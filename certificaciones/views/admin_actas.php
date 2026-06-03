<?php
// views/admin_actas.php

require_once __DIR__ . '/../controllers/init.php';
require_once __DIR__ . '/../config/model.php';

// Verificar permisos (Coordinador = 3, Admin = 4)
if (!in_array($_SESSION['id_rol'], [3, 4])) {
    die('<div class="alert alert-danger m-3">Acceso denegado. No tienes permisos para ver esta sección.</div>');
}

$db = new DB();
$conn = $db->getConn();

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
            SELECT u.cedula, u.nombre, u.apellido, cert.nota, cert.completado
            FROM cursos.certificaciones cert
            JOIN cursos.usuarios u ON cert.id_usuario = u.id
            WHERE cert.curso_id = :id
            ORDER BY u.apellido ASC, u.nombre ASC
        ");
        $stmtEst->execute(['id' => $id_curso_sel]);
        $estudiantes_aprobados = $stmtEst->fetchAll(PDO::FETCH_ASSOC); // Reutilizamos el nombre para no romper otras variables
        
        $total_inscritos = count($estudiantes_aprobados);
        $aprobados_count = 0;
        $reprobados_count = 0;
        $solo_participantes_count = 0;
        $no_completaron_count = 0;
        $tiene_notas = false;
        
        foreach ($estudiantes_aprobados as $e) {
            if ($e['nota'] !== null && $e['nota'] !== '') {
                $tiene_notas = true;
                $nota_val = round((float)$e['nota']);
                if ($nota_val >= 12) {
                    $aprobados_count++;
                } else {
                    $reprobados_count++;
                }
            } else {
                if (isset($e['completado']) && $e['completado'] == true) {
                    $solo_participantes_count++;
                } else {
                    $no_completaron_count++;
                }
            }
        }
    }
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-file-signature text-primary me-2"></i> Emisión de Actas de Cierre</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter"></i> Seleccionar Curso</h6>
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-9 mb-3 mb-md-0">
                    <label for="selectCursoActa" class="form-label font-weight-bold">Programa / Curso / Diplomado:</label>
                    <select class="form-select form-control" id="selectCursoActa" onchange="seleccionarCursoParaActa(this.value)">
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
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-cog"></i> Ajustes del Acta</h6>
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

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_cierre" class="form-label font-weight-bold">Fecha de Cierre:</label>
                                    <input type="date" class="form-control" id="fecha_cierre" name="fecha_cierre" value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="hora_cierre" class="form-label font-weight-bold">Hora de Cierre:</label>
                                    <input type="text" class="form-control" id="hora_cierre" name="hora_cierre" value="<?= date('h:i a') ?>" placeholder="Ej: 10:30 am" required>
                                </div>
                            </div>
                            <div class="alert alert-info py-2 small mb-4">
                                <i class="fas fa-info-circle"></i> <strong>Nota de Negocio:</strong> El acta de cierre oficial se genera en el backend (FPDF) e incluye a todos los participantes del curso con su estatus de acreditación respectivo.
                            </div>

                            <div class="card bg-light border-0 mb-4">
                                <div class="card-body p-3 text-center">
                                    <div class="row">
                                        <?php if ($tiene_notas): ?>
                                            <div class="col-4 border-end">
                                                <small class="text-muted d-block uppercase font-weight-bold">Inscritos</small>
                                                <span class="h4 font-weight-bold text-gray-800"><?= $total_inscritos ?></span>
                                            </div>
                                            <div class="col-4 border-end">
                                                <small class="text-muted d-block uppercase font-weight-bold">Aprobados</small>
                                                <span class="h4 font-weight-bold text-success"><?= $aprobados_count ?></span>
                                            </div>
                                            <div class="col-4">
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
                        <span class="badge bg-primary py-2 font-weight-bold">Todos los Participantes</span>
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
                                        $nota_str = "N/A";
                                        $estatus_badge = "";
                                        
                                        if ($e['nota'] !== null && $e['nota'] !== '') {
                                            $nota_val = round((float)$e['nota']);
                                            $nota_str = $nota_val;
                                            if ($nota_val >= 12) {
                                                $estatus_badge = '<span class="badge bg-success text-white px-2 py-1"><i class="fas fa-check-circle me-1"></i> Aprobado</span>';
                                            } else {
                                                $estatus_badge = '<span class="badge bg-danger text-white px-2 py-1"><i class="fas fa-times-circle me-1"></i> Reprobado</span>';
                                            }
                                        } else {
                                            if (isset($e['completado']) && $e['completado'] == true) {
                                                $estatus_badge = '<span class="badge bg-info text-white px-2 py-1"><i class="fas fa-user-check me-1"></i> Participante</span>';
                                            } else {
                                                $estatus_badge = '<span class="badge bg-secondary text-white px-2 py-1"><i class="fas fa-user-clock me-1"></i> No completó</span>';
                                            }
                                        }
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($e['cedula']) ?></td>
                                            <td class="text-start"><?= htmlspecialchars(mb_convert_case($e['apellido'] . ' ' . $e['nombre'], MB_CASE_TITLE, "UTF-8")) ?></td>
                                            <td class="font-weight-bold"><?= $nota_str ?></td>
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
    function seleccionarCursoParaActa(idCurso) {
        if (idCurso) {
            loadPage('../views/admin_actas.php', { id_curso: idCurso });
        } else {
            limpiarActaFiltro();
        }
    }

    function limpiarActaFiltro() {
        loadPage('../views/admin_actas.php');
    }
</script>
