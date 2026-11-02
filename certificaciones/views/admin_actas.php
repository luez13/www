<?php
// views/admin_actas.php

require_once __DIR__ . '/../controllers/init.php';
require_once __DIR__ . '/../config/model.php';
require_once __DIR__ . '/../models/curso.php';

// Verificar permisos (Coordinador = 3, Admin = 4, Super Admin = 7)
if (!tieneAcceso([3, 4])) {
    die('<div class="alert alert-danger m-3">Acceso denegado. No tienes permisos para ver esta sección.</div>');
}

$db = new DB();
$conn = $db->getConn();
$cursoModel = new Curso($db);

// 1. Obtener todos los cursos autorizados del sistema
$stmtCursos = $conn->prepare("
    SELECT id_curso, nombre_curso, tipo_curso 
    FROM cursos.cursos 
    WHERE autorizacion IS NOT NULL 
    ORDER BY id_curso DESC
");
$stmtCursos->execute();
$todos_cursos = $stmtCursos->fetchAll(PDO::FETCH_ASSOC);

$id_curso_sel = isset($_GET['id_curso']) ? (int)$_GET['id_curso'] : 0;

$curso_info = null;
$estudiantes_aprobados = [];
$docente_responsable = "No asignado";
$total_inscritos = 0;
$aprobados_count = 0;
$total_materias_curso = 0;
$mapa_recup_curso = [];
$recuperativos_count = 0;
$tiene_columna_recup_bd = $cursoModel->tieneColumnaNotaRecuperativa();

if ($id_curso_sel > 0) {
    // Info del curso
    $stmtC = $conn->prepare("
        SELECT c.*, u.nombre as prom_nom, u.apellido as prom_ape, u.titulo as prom_tit
        FROM cursos.cursos c
        LEFT JOIN cursos.usuarios u ON c.promotor = u.id
        WHERE c.id_curso = :id
    ");
    $stmtC->execute(['id' => $id_curso_sel]);
    $curso_info = $stmtC->fetch(PDO::FETCH_ASSOC);

    if ($curso_info) {
        if (!empty($curso_info['prom_nom'])) {
            $titulo = !empty($curso_info['prom_tit']) ? trim($curso_info['prom_tit']) . ' ' : '';
            $docente_responsable = trim($titulo . $curso_info['prom_nom'] . ' ' . $curso_info['prom_ape']);
        }

        // Obtener todos los estudiantes registrados
        $stmtEst = $conn->prepare("
            SELECT u.cedula, u.nombre, u.apellido, cert.nota, cert.nota as nota_original_bd, cert.completado, u.id as id_usuario
            FROM cursos.certificaciones cert
            JOIN cursos.usuarios u ON cert.id_usuario = u.id
            WHERE cert.curso_id = :id
            ORDER BY u.apellido ASC, u.nombre ASC
        ");
        $stmtEst->execute(['id' => $id_curso_sel]);
        $estudiantes_aprobados = $stmtEst->fetchAll(PDO::FETCH_ASSOC); // Reutilizamos el nombre para no romper otras variables

        // CÁLCULO ON-THE-FLY Y ARRASTRE DE CRÉDITOS
        $stmtTotal = $conn->prepare("SELECT COUNT(*) FROM cursos.materias_bimestre WHERE id_curso = :id");
        $stmtTotal->execute(['id' => $id_curso_sel]);
        $total_materias_curso = (int)$stmtTotal->fetchColumn();

        // Cargar mapa de recuperativos para cursos normales (Opción A + Opción B)
        $mapa_recup_curso = $cursoModel->obtenerMapaRecuperativosCurso($id_curso_sel);
        $nota_min = isset($curso_info['nota_minima_aprobatoria']) ? (int)$curso_info['nota_minima_aprobatoria'] : 12;

        $stmtHist = $conn->prepare("
            SELECT 
                MAX(um_hist.nota_recuperativa) AS max_recup_mat,
                MAX(COALESCE(NULLIF(um_hist.nota_recuperativa, 0), um_hist.nota_regular)) AS nota_historica
            FROM cursos.materias_bimestre m_pensum
            LEFT JOIN cursos.materias_bimestre mb_hist ON UPPER(TRIM(m_pensum.nombre_materia)) = UPPER(TRIM(mb_hist.nombre_materia))
            LEFT JOIN cursos.usuario_materias um_hist ON mb_hist.id_materia_bimestre = um_hist.id_materia_bimestre 
                                                      AND um_hist.id_usuario = :id_usuario
                                                      AND um_hist.estado LIKE 'Aprobado%'
            WHERE m_pensum.id_curso = :id_curso
            GROUP BY m_pensum.nombre_materia
        ");

        foreach ($estudiantes_aprobados as &$e) {
            $uid = (int)$e['id_usuario'];
            $e['nota_regular_curso'] = ($e['nota_original_bd'] !== null && $e['nota_original_bd'] !== '') ? round((float)$e['nota_original_bd']) : null;
            $e['nota_recuperativa_curso'] = null;

            $stmtHist->execute(['id_curso' => $id_curso_sel, 'id_usuario' => $uid]);
            $materias_historial = $stmtHist->fetchAll(PDO::FETCH_ASSOC);
            
            $materias_aprobadas = 0;
            $suma_notas = 0;
            $tiene_recup_mat = false;
            foreach ($materias_historial as $mh) {
                if ($mh['max_recup_mat'] !== null) {
                    $tiene_recup_mat = true;
                }
                if ($mh['nota_historica'] !== null) {
                    $materias_aprobadas++;
                    $suma_notas += (float)$mh['nota_historica'];
                }
            }

            if ($total_materias_curso == 0 && isset($mapa_recup_curso[$uid]) && $mapa_recup_curso[$uid]['nota_recuperativa'] !== null) {
                $e['nota_regular_curso'] = $mapa_recup_curso[$uid]['nota_regular'] !== null ? round((float)$mapa_recup_curso[$uid]['nota_regular']) : 0;
                $e['nota_recuperativa_curso'] = round((float)$mapa_recup_curso[$uid]['nota_recuperativa']);
                $e['nota'] = max((float)$e['nota_regular_curso'], (float)$e['nota_recuperativa_curso']);
                $e['completado'] = ($e['nota'] >= $nota_min);
                $recuperativos_count++;
            } elseif (isset($e['nota_original_bd']) && $e['nota_original_bd'] !== null && $e['nota_original_bd'] !== '') {
                // Si ya tiene nota cargada manualmente (ej. 18), se respeta y se asume completado
                $e['nota'] = $e['nota_original_bd'];
                $e['completado'] = true;
                if ($tiene_recup_mat) {
                    $recuperativos_count++;
                }
            } elseif ($total_materias_curso > 0 && $materias_aprobadas == $total_materias_curso) {
                // Completó todas las materias requeridas en la historia
                $e['completado'] = true;
                $e['nota'] = round($suma_notas / $total_materias_curso);
                if ($tiene_recup_mat) {
                    $recuperativos_count++;
                }
            } elseif ($total_materias_curso > 0) {
                // En progreso
                $e['completado'] = false;
                $e['nota'] = $materias_aprobadas > 0 ? round($suma_notas / $total_materias_curso) : null;
                if ($tiene_recup_mat) {
                    $recuperativos_count++;
                }
            } else {
                $e['completado'] = (bool)$e['completado'];
            }
        }
        unset($e); // Romper referencia
        
        $total_inscritos = count($estudiantes_aprobados);
        $aprobados_count = 0;
        $reprobados_count = 0;
        $solo_participantes_count = 0;
        $no_completaron_count = 0;
        $tiene_notas = false;
        
        foreach ($estudiantes_aprobados as $e) {
            if (isset($e['completado']) && $e['completado'] == false) {
                $reprobados_count++; // No completó -> Reprueba
                $no_completaron_count++;
            } else {
                if ($e['nota'] !== null && $e['nota'] !== '') {
                    $tiene_notas = true;
                    $nota_val = round((float)$e['nota']);
                    if ($nota_val >= $nota_min) {
                        $aprobados_count++;
                    } else {
                        $solo_participantes_count++; // Completó pero nota baja -> Participación
                    }
                } else {
                    $solo_participantes_count++; // Completó sin nota
                }
            }
        }
    }
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-file-signature text-primary me-2"></i> Emisión de Actas de Cierre</h1>
            <p class="text-muted small mt-1">Generación de actas oficiales inmutables por curso.</p>
        </div>
        <a href="javascript:history.back()" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 me-1"></i> Regresar
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter"></i> Seleccionar Curso</h6>
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-9 mb-3 mb-md-0">
                    <label for="selectCursoActa" class="form-label font-weight-bold">Programa / Curso / Diplomado:</label>
                    <select class="form-select form-control select2-busqueda" id="selectCursoActa">
                        <option value="">-- Seleccione un curso --</option>
                        <?php foreach ($todos_cursos as $c): ?>
                            <option value="<?= $c['id_curso'] ?>" <?= $id_curso_sel == $c['id_curso'] ? 'selected' : '' ?>>
                                [<?= ucfirst($c['tipo_curso']) ?>] <?= htmlspecialchars($c['nombre_curso']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-secondary w-100" onclick="limpiarActaFiltro()"><i class="fas fa-eraser"></i> Limpiar</button>
                </div>
            </div>
        </div>
    </div>

    <?php if ($id_curso_sel > 0 && $curso_info): ?>
        <div class="row">
            <!-- Columna izquierda: Configuración del Acta -->
            <div class="col-lg-5">
                <div class="card shadow mb-4 border-left-success">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-cog"></i> Ajustes del Acta</h6>
                        <?php if ($total_materias_curso == 0): ?>
                            <span class="badge <?= $tiene_columna_recup_bd ? 'bg-success' : 'bg-info' ?> text-white" title="<?= $tiene_columna_recup_bd ? 'Columna nota_recuperativa activa en BD (Opción B)' : 'Modo híbrido de respaldo activo (Opción A)' ?>">
                                <i class="fas fa-database me-1"></i> <?= $tiene_columna_recup_bd ? 'BD Nativa (Opción B)' : 'Modo Híbrido (Opción A)' ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <form action="../controllers/generar_acta_cierre_fpdf.php" method="GET" target="_blank" id="formDescargarActa">
                            <input type="hidden" name="id_curso" value="<?= $id_curso_sel ?>">

                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Título / Tipo de Programa:</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars(ucfirst($curso_info['tipo_curso'])) ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Docente Responsable (Facilitador):</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($docente_responsable) ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="tipo_acta" class="form-label font-weight-bold text-primary"><i class="fas fa-file-contract me-1"></i> Tipo de Acta a Emitir:</label>
                                <select class="form-select form-control border-primary fw-bold" id="tipo_acta" name="tipo_acta">
                                    <option value="Regular">Acta de Cierre Final (General + Anexos)</option>
                                    <option value="Recuperativo">Acta de Cierre de Recuperativo (Exclusiva)</option>
                                </select>
                                <small class="text-muted">Seleccione "Acta de Cierre de Recuperativo" para emitir el documento exclusivo de evaluación recuperativa.</small>
                            </div>

                            <?php if ($total_materias_curso == 0): ?>
                                <div class="mb-3">
                                    <button type="button" class="btn btn-warning w-100 fw-bold text-dark shadow-sm" onclick="abrirModalRecuperativoCurso()">
                                        <i class="fas fa-ambulance me-1"></i> Cargar / Gestionar Notas de Recuperativo
                                        <?php if ($recuperativos_count > 0): ?>
                                            <span class="badge bg-dark text-white ms-1"><?= $recuperativos_count ?></span>
                                        <?php endif; ?>
                                    </button>
                                </div>
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_cierre" class="form-label font-weight-bold">Fecha Maestra (Diplomas/Acta):</label>
                                    <div class="input-group">
                                        <?php 
                                        $fecha_maestra = !empty($curso_info['fecha_acta_cierre']) ? date('Y-m-d', strtotime($curso_info['fecha_acta_cierre'])) : date('Y-m-d');
                                        $disabled = (!tieneAcceso([4])) ? 'disabled' : '';
                                        ?>
                                        <input type="date" class="form-control" id="fecha_cierre" name="fecha_cierre" value="<?= $fecha_maestra ?>" <?= $disabled ?> required>
                                        <?php if (tieneAcceso([4])): ?>
                                            <button class="btn btn-outline-secondary" type="button" onclick="fijarFechaMaestra(<?= $id_curso_sel ?>)"><i class="fas fa-save"></i> Fijar</button>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted">Fecha impresa en los certificados finales.</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="hora_cierre" class="form-label font-weight-bold">Hora de Cierre:</label>
                                    <input type="text" class="form-control" id="hora_cierre" name="hora_cierre" value="<?= date('h:i a') ?>" placeholder="Ej: 10:30 am" <?= $disabled ?> required>
                                </div>
                            </div>
                            <div class="alert alert-info py-2 small mb-4">
                                <i class="fas fa-info-circle"></i> <strong>Nota de Negocio:</strong> El acta de cierre oficial se genera en el backend (FPDF) e incluye a los participantes con su estatus de acreditación respectivo.
                            </div>

                            <div class="card bg-light border-0 mb-4">
                                <div class="card-body p-3 text-center">
                                    <div class="row">
                                        <?php if ($tiene_notas): ?>
                                            <div class="col-3 border-end">
                                                <small class="text-muted d-block uppercase font-weight-bold">Inscritos</small>
                                                <span class="h4 font-weight-bold text-gray-800"><?= $total_inscritos ?></span>
                                            </div>
                                            <div class="col-3 border-end">
                                                <small class="text-muted d-block uppercase font-weight-bold">Aprobados</small>
                                                <span class="h4 font-weight-bold text-success"><?= $aprobados_count ?></span>
                                            </div>
                                            <div class="col-3 border-end">
                                                <small class="text-muted d-block uppercase font-weight-bold">Participación</small>
                                                <span class="h4 font-weight-bold text-warning"><?= $solo_participantes_count ?></span>
                                            </div>
                                            <div class="col-3">
                                                <small class="text-muted d-block uppercase font-weight-bold">Reprobados</small>
                                                <span class="h4 font-weight-bold text-danger"><?= $reprobados_count ?></span>
                                            </div>
                                        <?php else: ?>
                                            <div class="col-4 border-end">
                                                <small class="text-muted d-block uppercase font-weight-bold">Inscritos</small>
                                                <span class="h4 font-weight-bold text-gray-800"><?= $total_inscritos ?></span>
                                            </div>
                                            <div class="col-4 border-end">
                                                <small class="text-muted d-block uppercase font-weight-bold">Acreditados</small>
                                                <span class="h4 font-weight-bold text-info"><?= $solo_participantes_count ?></span>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted d-block uppercase font-weight-bold">No Completaron</small>
                                                <span class="h4 font-weight-bold text-danger"><?= $no_completaron_count ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success btn-lg w-100 shadow">
                                <i class="fas fa-file-pdf fa-lg me-2"></i> Descargar Acta en PDF
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Columna derecha: Vista previa de alumnos aprobados -->
            <div class="col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-light d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-users"></i> Participantes en Acta (<?= $total_inscritos ?>)</h6>
                        <div>
                            <?php if ($recuperativos_count > 0): ?>
                                <span class="badge bg-warning text-dark py-2 me-1 font-weight-bold"><i class="fas fa-ambulance me-1"></i> <?= $recuperativos_count ?> en Recuperativo</span>
                            <?php endif; ?>
                            <span class="badge bg-primary py-2 font-weight-bold">Todos los Participantes</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                            <table class="table table-hover table-striped mb-0 align-middle text-center">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Cédula</th>
                                        <th class="text-start">Nombre / Apellido</th>
                                        <th>Nota</th>
                                        <th>Estatus</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($estudiantes_aprobados)): ?>
                                        <tr>
                                            <td colspan="4" class="text-muted py-4">No hay participantes registrados para este curso.</td>
                                        </tr>
                                    <?php else: foreach ($estudiantes_aprobados as $e): ?>
                                        <?php
                                        $nota_min = isset($curso_info['nota_minima_aprobatoria']) ? (int)$curso_info['nota_minima_aprobatoria'] : 12;
                                        $nota_str = "N/A";
                                        $estatus_badge = "";
                                        $recup_badge = "";

                                        if (isset($e['nota_recuperativa_curso']) && $e['nota_recuperativa_curso'] !== null) {
                                            $recup_badge = ' <span class="badge bg-warning text-dark ms-1" title="Nota Regular: ' . (int)$e['nota_regular_curso'] . ' | Nota Recuperativa: ' . (int)$e['nota_recuperativa_curso'] . '">Recup. (Reg: ' . (int)$e['nota_regular_curso'] . ')</span>';
                                        }
                                        
                                        if (isset($e['completado']) && $e['completado'] == false) {
                                            if ($e['nota'] !== null && $e['nota'] !== '') {
                                                $nota_str = round((float)$e['nota']);
                                            }
                                            $estatus_badge = '<span class="badge bg-danger text-white px-2 py-1"><i class="fas fa-times-circle me-1"></i> Reprobado</span>';
                                        } else {
                                            if ($e['nota'] !== null && $e['nota'] !== '') {
                                                $nota_val = round((float)$e['nota']);
                                                $nota_str = $nota_val;
                                                if ($nota_val >= $nota_min) {
                                                    $estatus_badge = '<span class="badge bg-success text-white px-2 py-1"><i class="fas fa-check-circle me-1"></i> Aprobado</span>';
                                                } else {
                                                    $estatus_badge = '<span class="badge bg-info text-white px-2 py-1"><i class="fas fa-user-check me-1"></i> Participante</span>';
                                                }
                                            } else {
                                                $estatus_badge = '<span class="badge bg-info text-white px-2 py-1"><i class="fas fa-user-check me-1"></i> Participante</span>';
                                            }
                                        }
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($e['cedula']) ?></td>
                                            <td class="text-start"><?= htmlspecialchars(mb_convert_case($e['apellido'] . ' ' . $e['nombre'], MB_CASE_TITLE, "UTF-8")) ?></td>
                                            <td class="font-weight-bold"><?= $nota_str ?><?= $recup_badge ?></td>
                                            <td><?= $estatus_badge ?></td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($total_materias_curso == 0): ?>
        <!-- Modal de Evaluación Recuperativa para Cursos Normales -->
        <div class="modal fade" id="modalRecuperativoCurso" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header text-white" style="background-color: #fd7e14;">
                        <h5 class="modal-title fw-bold">
                            <i class="fas fa-ambulance me-2"></i> Evaluación Recuperativa - <?= htmlspecialchars($curso_info['nombre_curso']) ?>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <div class="small text-muted">
                                Nota mínima aprobatoria del programa: <strong class="text-dark"><?= (int)$nota_min ?> pts</strong>.
                                Ingrese la calificación recuperativa para los participantes que presentaron recuperación (deje en blanco si no aplica).
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="chkMostrarTodosRecup" onchange="filtrarTablaRecuperativoCurso()">
                                <label class="form-check-label small fw-bold" for="chkMostrarTodosRecup">Mostrar todos los inscritos</label>
                            </div>
                        </div>

                        <form id="formRecuperativoCurso">
                            <input type="hidden" name="action" value="guardar_recuperativo_curso">
                            <input type="hidden" name="id_curso" value="<?= $id_curso_sel ?>">
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-bordered table-hover table-sm align-middle text-center mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th width="16%">Cédula</th>
                                            <th class="text-start">Participante</th>
                                            <th width="18%">Nota Regular</th>
                                            <th width="20%">Nota Recuperativa</th>
                                            <th width="18%">Estatus Final</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($estudiantes_aprobados as $e): 
                                            $uid = (int)$e['id_usuario'];
                                            $n_reg_val = $e['nota_regular_curso'] !== null ? (int)$e['nota_regular_curso'] : '';
                                            $n_rec_val = $e['nota_recuperativa_curso'] !== null ? (int)$e['nota_recuperativa_curso'] : '';
                                            $es_candidato_recup = ($n_rec_val !== '' || $n_reg_val === '' || (int)$n_reg_val < $nota_min || (isset($e['completado']) && !$e['completado']));
                                        ?>
                                        <tr class="fila-recup-curso" data-candidato="<?= $es_candidato_recup ? '1' : '0' ?>">
                                            <td><?= htmlspecialchars($e['cedula']) ?></td>
                                            <td class="text-start fw-bold"><?= htmlspecialchars(mb_convert_case($e['apellido'] . ' ' . $e['nombre'], MB_CASE_TITLE, "UTF-8")) ?></td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm text-center input-nota-reg" 
                                                       name="notas_regular[<?= $uid ?>]" 
                                                       value="<?= $n_reg_val ?>" 
                                                       min="0" max="100" step="1" placeholder="Reg."
                                                       oninput="actualizarPreviewRecupFila(this)">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm text-center border-warning fw-bold input-nota-recup" 
                                                       name="notas_recup[<?= $uid ?>]" 
                                                       value="<?= $n_rec_val ?>" 
                                                       min="1" max="100" step="1" placeholder="S/R"
                                                       oninput="actualizarPreviewRecupFila(this)">
                                            </td>
                                            <td class="celda-estatus-recup">
                                                <?php 
                                                $nota_efec = $n_rec_val !== '' ? max((int)$n_reg_val, (int)$n_rec_val) : ($n_reg_val !== '' ? (int)$n_reg_val : null);
                                                if ($nota_efec === null): ?>
                                                    <span class="badge bg-secondary">Sin Nota</span>
                                                <?php elseif ($nota_efec >= $nota_min): ?>
                                                    <span class="badge bg-success">Aprobado (<?= $nota_efec ?>)</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Reprobado (<?= $nota_efec ?>)</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn text-white fw-bold" style="background-color: #fd7e14;" onclick="guardarRecuperativoCurso(<?= $id_curso_sel ?>)">
                            <i class="fas fa-save me-1"></i> Guardar Notas de Recuperativo
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="card shadow border-left-info py-2">
            <div class="card-body text-center py-5">
                <i class="fas fa-file-invoice fa-3x text-info mb-3"></i>
                <h5>Esperando selección de curso</h5>
                <p class="text-muted mb-0">Seleccione un curso en la barra superior para parametrizar y generar su Acta de Cierre oficial.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    var NOTA_MINIMA_CURSO_ACTA = <?= isset($nota_min) ? (int)$nota_min : 12 ?>;

    $(document).ready(function() {
        // Inicializar Select2 para búsqueda de cursos
        $('#selectCursoActa').select2({
            theme: 'bootstrap-5',
            placeholder: "Seleccione un curso...",
            allowClear: true,
            width: '100%'
        });
        
        // Enlazar evento select2:select y select2:unselect
        $('#selectCursoActa').on('select2:select', function (e) {
            var data = e.params.data;
            seleccionarCursoParaActa(data.id);
        });
        $('#selectCursoActa').on('select2:unselect', function (e) {
            seleccionarCursoParaActa("");
        });
    });

    function seleccionarCursoParaActa(idCurso) {
        if (idCurso) {
            loadPage('../views/admin_actas.php', { id_curso: idCurso });
        } else {
            limpiarActaFiltro();
        }
    }

    function limpiarActaFiltro() {
        $('#selectCursoActa').val(null).trigger('change');
        loadPage('../views/admin_actas.php');
    }

    function abrirModalRecuperativoCurso() {
        // Si no hay candidatos reprobados, activar automáticamente "Mostrar todos"
        var candidatos = $('.fila-recup-curso[data-candidato="1"]').length;
        $('#chkMostrarTodosRecup').prop('checked', candidatos === 0);
        filtrarTablaRecuperativoCurso();
        $('#modalRecuperativoCurso').modal('show');
    }

    function filtrarTablaRecuperativoCurso() {
        var mostrarTodos = $('#chkMostrarTodosRecup').is(':checked');
        $('.fila-recup-curso').each(function() {
            if (mostrarTodos || $(this).attr('data-candidato') === '1') {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    function actualizarPreviewRecupFila(inputEl) {
        var $tr = $(inputEl).closest('tr');
        var regVal = $tr.find('.input-nota-reg').val();
        var recVal = $tr.find('.input-nota-recup').val();
        var $celda = $tr.find('.celda-estatus-recup');

        var nReg = regVal !== '' ? parseInt(regVal, 10) : null;
        var nRec = recVal !== '' ? parseInt(recVal, 10) : null;
        var efec = null;

        if (nRec !== null && !isNaN(nRec)) {
            efec = (nReg !== null && !isNaN(nReg)) ? Math.max(nReg, nRec) : nRec;
        } else if (nReg !== null && !isNaN(nReg)) {
            efec = nReg;
        }

        if (efec === null) {
            $celda.html('<span class="badge bg-secondary">Sin Nota</span>');
        } else if (efec >= NOTA_MINIMA_CURSO_ACTA) {
            $celda.html('<span class="badge bg-success">Aprobado (' + efec + ')</span>');
        } else {
            $celda.html('<span class="badge bg-danger">Reprobado (' + efec + ')</span>');
        }
    }

    function guardarRecuperativoCurso(idCurso) {
        var datos = $('#formRecuperativoCurso').serialize();
        $.ajax({
            url: '../controllers/gestion_notas.php',
            type: 'POST',
            data: datos,
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#modalRecuperativoCurso').modal('hide');
                    $('.modal-backdrop').remove();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Guardado', res.message, 'success');
                    } else {
                        alert(res.message);
                    }
                    loadPage('../views/admin_actas.php', { id_curso: idCurso });
                } else {
                    alert('Error: ' + res.message);
                }
            },
            error: function(xhr) {
                alert('Error de conexión: ' + xhr.responseText);
            }
        });
    }

    function fijarFechaMaestra(idCurso) {
        var fecha = $('#fecha_cierre').val();
        if(!fecha) {
            alert('Debe seleccionar una fecha.');
            return;
        }
        if(!confirm('¿Está seguro de fijar ' + fecha + ' como la fecha legal de cierre para los diplomas de este curso?')) return;
        
        $.ajax({
            url: '../controllers/set_fecha_acta.php',
            type: 'POST',
            data: { id_curso: idCurso, fecha: fecha },
            success: function(res) {
                alert(res);
            },
            error: function(xhr) {
                alert('Error al fijar la fecha: ' + xhr.responseText);
            }
        });
    }
</script>
