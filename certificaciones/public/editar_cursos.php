<?php
// public/editar_cursos.php

include '../controllers/init.php';
include '../config/model.php';
include '../models/curso.php';

$user_id = $_SESSION['user_id'];
$pagina_actual = 'editar_cursos.php'; 

require_once '../controllers/autenticacion.php';
if (!esPerfil3($user_id) && !esPerfil4($user_id)) {
    die('No tienes permiso para ver esta página.');
}

$db = new DB();

// Compatible con PHP 5.6 y PHP 8.x
function h($string) {
    // Usamos isset y operador ternario en lugar de ??
    $str = isset($string) ? $string : '';
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function fmt_date($date_str) {
    if (empty($date_str)) return '';
    return date('Y-m-d\TH:i', strtotime($date_str));
}

// Datos auxiliares
$stmt_cargos = $db->prepare("SELECT id_cargo, nombre_cargo, nombre, apellido FROM cursos.cargos WHERE activo = true ORDER BY nombre_cargo");
$stmt_cargos->execute();
$cargos_firmantes = $stmt_cargos->fetchAll(PDO::FETCH_ASSOC);

$stmt_posiciones = $db->prepare("SELECT id_posicion, codigo_posicion, descripcion_posicion FROM cursos.posiciones_firma ORDER BY id_posicion");
$stmt_posiciones->execute();
$posiciones_firma = $stmt_posiciones->fetchAll(PDO::FETCH_ASSOC);

// --- BÚSQUEDA Y PAGINACIÓN ---
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$sql_base = "FROM cursos.cursos";
$params = [];

if (!empty($busqueda)) {
    $sql_base .= " WHERE nombre_curso ILIKE :busqueda OR CAST(id_curso AS TEXT) LIKE :busqueda";
    $params[':busqueda'] = '%' . $busqueda . '%';
}

$stmt_total = $db->prepare("SELECT COUNT(*) $sql_base");
$stmt_total->execute($params);
$total_cursos = $stmt_total->fetchColumn();
$total_pages = ceil($total_cursos / $limit);

$sql_final = "SELECT * $sql_base ORDER BY nombre_curso ASC LIMIT :limit OFFSET :offset";
$stmt_cursos = $db->prepare($sql_final);
foreach ($params as $key => $val) $stmt_cursos->bindValue($key, $val);
$stmt_cursos->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt_cursos->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt_cursos->execute();
$cursos = $stmt_cursos->fetchAll(PDO::FETCH_ASSOC);

function diasToArray($dias_str) {
    if (!is_string($dias_str) || empty($dias_str)) return [];
    $dias_limpio = trim($dias_str, '{}');
    $array_dias = explode(',', $dias_limpio);
    return array_filter(array_map(function($dia) { return strtolower(trim($dia)); }, $array_dias));
}

function renderPagination($total_pages, $current_page, $pagina_actual, $busqueda) {
    if ($total_pages <= 1) return '';
    $q = addslashes($busqueda); 
    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    if ($current_page > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="#" onclick="loadPage(\'' . $pagina_actual . '\', { page: ' . ($current_page - 1) . ', busqueda: \'' . $q . '\' }); return false;">&laquo;</a></li>';
    }
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);
    for ($i = $start; $i <= $end; $i++) {
        $active = $i == $current_page ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '"><a class="page-link" href="#" onclick="loadPage(\'' . $pagina_actual . '\', { page: ' . $i . ', busqueda: \'' . $q . '\' }); return false;">' . $i . '</a></li>';
    }
    if ($current_page < $total_pages) {
        $html .= '<li class="page-item"><a class="page-link" href="#" onclick="loadPage(\'' . $pagina_actual . '\', { page: ' . ($current_page + 1) . ', busqueda: \'' . $q . '\' }); return false;">&raquo;</a></li>';
    }
    $html .= '</ul></nav>';
    return $html;
}
?>
<style>
    select option:checked, select option[selected] { font-weight: bold; color: #198754; }
    .module-border { border-left: 4px solid #4e73df; background-color: #f8f9fc; }
    .btn-circle-sm { width: 30px; height: 30px; padding: 6px 0px; border-radius: 15px; font-size: 12px; text-align: center; }
    .result-row:hover { background-color: #f1f1f1; cursor: pointer; }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Editar Cursos</h1>
        <div class="input-group" style="width: 300px;">
            <input type="text" id="busquedaCursoGlobal" class="form-control bg-light border-0 small" placeholder="Buscar curso o ID..." value="<?= h($busqueda) ?>" onkeyup="filtrarCursosDinamico(event)">
            <div class="input-group-append">
                <button class="btn btn-primary" type="button" onclick="ejecutarBusquedaCurso()"><i class="fas fa-search fa-sm"></i></button>
            </div>
            <?php if(!empty($busqueda)): ?>
                <div class="input-group-append">
                    <button class="btn btn-danger" type="button" onclick="limpiarBusquedaCurso()"><i class="fas fa-times fa-sm"></i></button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="accordion" id="accordionCursos">
    <?php if (empty($cursos)): ?>
        <div class="alert alert-info">No se encontraron cursos.</div>
    <?php else: ?>
        <?php foreach ($cursos as $curso): ?>
            <?php
            $curso_model = new Curso($db); 
            $modulos_existentes = $curso_model->obtenerModulosPorCurso($curso['id_curso']);
            $modulos_existentes = is_array($modulos_existentes) ? $modulos_existentes : [];

            $nombre_promotor_actual = "No asignado";
            if ($curso['promotor']) {
                $stmt_p = $db->prepare("SELECT nombre, apellido FROM cursos.usuarios WHERE id = :id");
                $stmt_p->execute(['id' => $curso['promotor']]);
                $p_data = $stmt_p->fetch(PDO::FETCH_ASSOC);
                if ($p_data) $nombre_promotor_actual = $p_data['nombre'] . ' ' . $p_data['apellido'];
            }
            ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading<?= $curso['id_curso'] ?>">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $curso['id_curso'] ?>" aria-expanded="false" aria-controls="collapse<?= $curso['id_curso'] ?>">
                        <strong><?= h($curso['nombre_curso']) ?></strong>
                        <?php if($curso['autorizacion']): ?>
                            <span class="badge bg-success ms-2"><i class="fas fa-check-circle"></i> Autorizado</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark ms-2"><i class="fas fa-clock"></i> Pendiente</span>
                        <?php endif; ?>
                        <span class="badge bg-secondary ms-2">ID: <?= $curso['id_curso'] ?></span>
                    </button>
                </h2>
                <div id="collapse<?= $curso['id_curso'] ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $curso['id_curso'] ?>" data-bs-parent="#accordionCursos">
                    <div class="accordion-body">
                        <form id="editarCursoForm<?= $curso['id_curso'] ?>" action="../controllers/curso_controlador.php" method="post" class="editarCursoForm">
                            <input type="hidden" name="action" value="editar">
                            <input type="hidden" name="id_curso" value="<?= $curso['id_curso'] ?>">
                            <input type="hidden" name="modulos_a_eliminar" id="modulos_a_eliminar_<?= $curso['id_curso'] ?>" value="">

                            <div class="card shadow mb-4">
                                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Información General</h6></div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Nombre del curso</label>
                                        <input type="text" class="form-control" name="nombre_curso" value="<?= h($curso['nombre_curso']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Descripción</label>
                                        <textarea class="form-control" name="descripcion" required><?= h($curso['descripcion']) ?></textarea>
                                    </div>
                                     <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Tipo de curso</label>
                                            <select class="form-select" name="tipo_curso" required>
                                                <?php $tipos = ['masterclass', 'seminario', 'diplomado', 'congreso', 'charla', 'taller', 'curso', 'masterclass_rectoria', 'seminario_rectoria', 'diplomado_rectoria', 'congreso_rectoria', 'charla_rectoria', 'taller_rectoria', 'curso_rectoria']; ?>
                                                <?php foreach ($tipos as $tipo): ?>
                                                    <option value="<?= $tipo ?>" <?= ($curso['tipo_curso'] == $tipo) ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $tipo)) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nivel del curso</label>
                                            <input type="text" class="form-control" name="nivel_curso" value="<?= h($curso['nivel_curso']) ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card shadow mb-4">
                                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Fechas y Horarios</h6></div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Fecha de Inicio</label>
                                            <input type="date" class="form-control" name="inicio_mes" value="<?= h($curso['inicio_mes']) ?>" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Fecha de Finalización</label>
                                            <input type="datetime-local" class="form-control" name="fecha_finalizacion" value="<?= fmt_date($curso['fecha_finalizacion']) ?>" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Duración (semanas)</label>
                                            <input type="number" class="form-control" name="tiempo_asignado" value="<?= h($curso['tiempo_asignado']) ?>" min="1" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Horario Inicio</label>
                                            <input type="time" class="form-control" name="horario_inicio" value="<?= h($curso['horario_inicio']) ?>" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Horario Fin</label>
                                            <input type="time" class="form-control" name="horario_fin" value="<?= h($curso['horario_fin']) ?>" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label d-block">Días de clase</label>
                                            <div class="p-2 border rounded bg-light">
                                                <?php 
                                                $dias_semana = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
                                                $dias_guardados = diasToArray($curso['dias_clase']);
                                                foreach ($dias_semana as $dia):
                                                    $checked = in_array($dia, $dias_guardados) ? 'checked' : '';
                                                ?>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox" name="dias_clase[]" value="<?= $dia ?>" id="dia_<?= $curso['id_curso'] ?>_<?= $dia ?>" <?= $checked ?>>
                                                        <label class="form-check-label" for="dia_<?= $curso['id_curso'] ?>_<?= $dia ?>"><?= ucfirst($dia) ?></label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card shadow mb-4">
                                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Detalles Académicos</h6></div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Costo</label>
                                            <input type="number" class="form-control" name="costo" value="<?= h($curso['costo']) ?>" step="0.01" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Horas cronológicas</label>
                                            <input type="number" class="form-control" name="horas_cronologicas" value="<?= h($curso['horas_cronologicas']) ?>" step="0.1" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Límite Inscripción</label>
                                            <input type="number" class="form-control" name="limite_inscripciones" value="<?= h($curso['limite_inscripciones']) ?>" required>
                                        </div>
                                    </div>
                                    <div class="mb-3"><label class="form-label">Conocimientos previos</label><textarea class="form-control" name="conocimientos_previos" required><?= h($curso['conocimientos_previos']) ?></textarea></div>
                                    <div class="mb-3"><label class="form-label">Requerimientos</label><textarea class="form-control" name="requerimientos_implementos" required><?= h($curso['requerimientos_implemento']) ?></textarea></div>
                                    <div class="mb-3"><label class="form-label">Desempeño al concluir</label><textarea class="form-control" name="desempeño_al_concluir" required><?= h($curso['desempeno_al_concluir']) ?></textarea></div>
                                </div>
                            </div>

                            <div class="card shadow mb-4">
                                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Gestión y Autorización</h6></div>
                                <div class="card-body">
                                     <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label font-weight-bold">Facilitador (Promotor)</label>
                                            <div class="input-group">
                                                <input type="hidden" id="promotor_id_<?= $curso['id_curso'] ?>" name="promotor" value="<?= h($curso['promotor']) ?>" required>
                                                <input type="text" class="form-control bg-white" id="promotor_nombre_<?= $curso['id_curso'] ?>" value="<?= h($nombre_promotor_actual) ?>" readonly>
                                                <button class="btn btn-outline-primary" type="button" onclick="abrirModalFacilitador(<?= $curso['id_curso'] ?>)">
                                                    <i class="fas fa-search"></i> Cambiar
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card border-left-warning h-100 py-2">
                                                <div class="card-body">
                                                    <div class="row no-gutters align-items-center">
                                                        <div class="col mr-2">
                                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Estado de Autorización</div>
                                                            <?php if ($curso['autorizacion']): 
                                                                $stmt_a = $db->prepare("SELECT nombre FROM cursos.usuarios WHERE id = :id");
                                                                $stmt_a->execute(['id' => $curso['autorizacion']]);
                                                                $autorizador = $stmt_a->fetchColumn();
                                                            ?>
                                                                <div class="h5 mb-0 font-weight-bold text-gray-800 text-success"><i class="fas fa-check"></i> Autorizado</div>
                                                                <div class="small text-muted mt-1">Por: <?= h($autorizador) ?></div>
                                                            <?php else: ?>
                                                                <div class="form-check mt-2">
                                                                    <input class="form-check-input" type="checkbox" name="autorizacion" value="<?= $_SESSION['user_id'] ?>" id="auth_check_<?= $curso['id_curso'] ?>">
                                                                    <label class="form-check-label font-weight-bold text-primary" for="auth_check_<?= $curso['id_curso'] ?>">AUTORIZAR CURSO AHORA</label>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" role="switch" name="firma_digital" value="1" <?= ($curso['firma_digital'] ? 'checked' : '') ?>>
                                                        <label class="form-check-label">Habilitar Firma Digital</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                     </div>
                                </div>
                            </div>

                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Módulos del Curso</h6>
                                    <button type="button" class="btn btn-success btn-sm" onclick="agregarNuevoModulo(<?= $curso['id_curso'] ?>)"><i class="fas fa-plus"></i> Agregar Módulo</button>
                                </div>
                                <div class="card-body">
                                    <div id="contenedor_modulos_<?= $curso['id_curso'] ?>">
                                        <?php foreach ($modulos_existentes as $index => $modulo): ?>
                                            <div class="p-3 border rounded mb-3 module-border" id="modulo_existente_<?= $modulo['id_modulo'] ?>">
                                                <input type="hidden" name="modulos[<?= $modulo['id_modulo'] ?>][id_modulo]" value="<?= $modulo['id_modulo'] ?>">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <h5>Módulo <span class="numero-modulo"><?= h($modulo['numero']) ?></span> (Existente)</h5>
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="eliminarModuloExistente(<?= $modulo['id_modulo'] ?>, <?= $curso['id_curso'] ?>)"><i class="fas fa-trash"></i></button>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-8 mb-3">
                                                        <label class="form-label">Nombre del módulo</label>
                                                        <input type="text" class="form-control" name="modulos[<?= $modulo['id_modulo'] ?>][nombre_modulo]" value="<?= h($modulo['nombre_modulo']) ?>" required>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="form-label">Número</label>
                                                        <input type="number" class="form-control" name="modulos[<?= $modulo['id_modulo'] ?>][numero]" value="<?= h($modulo['numero']) ?>" required>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label d-flex justify-content-between">Contenido <button type="button" class="btn btn-secondary btn-circle-sm" onclick="agregarTextareaContenido(this, 'modulos[<?= $modulo['id_modulo'] ?>][contenido][]')"><i class="fas fa-plus"></i></button></label>
                                                    <div class="contenidos-container">
                                                        <?php
                                                        $raw_content = trim($modulo['contenido'] ?? '', '[]');
                                                        $contenidos = explode('][', $raw_content);
                                                        if(empty($raw_content)) $contenidos = [''];
                                                        foreach ($contenidos as $cont): 
                                                        ?>
                                                        <div class="d-flex mb-2">
                                                            <textarea class="form-control me-2" name="modulos[<?= $modulo['id_modulo'] ?>][contenido][]" rows="2" required><?= h($cont) ?></textarea>
                                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarTextareaContenido(this)"><i class="fas fa-minus"></i></button>
                                                        </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3"><label class="form-label">Actividad</label><input type="text" class="form-control" name="modulos[<?= $modulo['id_modulo'] ?>][actividad]" value="<?= h($modulo['actividad']) ?>" required></div>
                                                    <div class="col-md-6 mb-3"><label class="form-label">Instrumento</label><input type="text" class="form-control" name="modulos[<?= $modulo['id_modulo'] ?>][instrumento]" value="<?= h($modulo['instrumento']) ?>" required></div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="card shadow mb-4">
                                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Firmas del Certificado</h6></div>
                                <div class="card-body">
                                    <?php
                                     $stmt_config = $db->prepare("SELECT id_posicion, id_cargo_firmante, usar_promotor_curso FROM cursos.cursos_config_firmas WHERE id_curso = :id_curso");
                                     $stmt_config->execute([':id_curso' => $curso['id_curso']]);
                                     $configs_curso_raw = $stmt_config->fetchAll(PDO::FETCH_ASSOC);
                                     $configs_curso = [];
                                     foreach ($configs_curso_raw as $config) $configs_curso[$config['id_posicion']] = $config;
                                     
                                     foreach ($posiciones_firma as $posicion):
                                        $id_pos = $posicion['id_posicion'];
                                        $conf = isset($configs_curso[$id_pos]) ? $configs_curso[$id_pos] : null;
                                     ?>
                                        <div class="row align-items-center mb-2 border-bottom pb-2">
                                            <div class="col-md-4"><strong><?= h($posicion['descripcion_posicion']) ?></strong></div>
                                            <div class="col-md-4">
                                                <select class="form-select form-select-sm" name="config_firmas[<?= $id_pos ?>][id_cargo_firmante]">
                                                    <option value="">-- Seleccionar Cargo --</option>
                                                    <?php foreach ($cargos_firmantes as $cargo): 
                                                        $sel = ($conf && $conf['id_cargo_firmante'] == $cargo['id_cargo']) ? 'selected' : '';
                                                    ?>
                                                    <option value="<?= $cargo['id_cargo'] ?>" <?= $sel ?>><?= h($cargo['nombre_cargo']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="config_firmas[<?= $id_pos ?>][usar_promotor_curso]" value="1" <?= ($conf && $conf['usar_promotor_curso']) ? 'checked' : '' ?>>
                                                    <label class="form-check-label small">Usar Promotor</label>
                                                </div>
                                            </div>
                                        </div>
                                     <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <?php $es_diplomado = in_array($curso['tipo_curso'], ['diplomado', 'diplomado_rectoria']); ?>
                            <?php if ($es_diplomado): ?>
                            <div class="card shadow mb-4 border-left-success">
                                <div class="card-header py-3 bg-success text-white">
                                    <h6 class="m-0 font-weight-bold">Gestión de Módulos, Notas y Acta de Cierre (Diplomado)</h6>
                                </div>
                                <div class="card-body">
                                    <p class="text-secondary small">Herramientas exclusivas para la gestión académica de Diplomados.</p>
                                    <div class="d-grid gap-2 d-md-block">
                                        <a href="#" class="btn btn-primary btn-icon-split" 
                                        onclick="loadPage('../views/gestionar_materias.php', { id_curso: <?= $curso['id_curso'] ?> }); return false;">
                                            <span class="icon text-white-50"><i class="fas fa-fw fa-book"></i></span>
                                            <span class="text">Materias/Bimestres</span>
                                        </a>
                                        <a href="#" class="btn btn-info btn-icon-split" onclick="loadPage('../views/gestionar_notas.php', { id_curso: <?= $curso['id_curso'] ?> }); return false;">
                                            <span class="icon text-white-50"><i class="fas fa-fw fa-calculator"></i></span>
                                            <span class="text">Notas/Calificaciones</span>
                                        </a>
                                        <a href="#" class="btn btn-success btn-icon-split" onclick="loadPage('../views/generar_acta_cierre.php', { id_curso: <?= $curso['id_curso'] ?> }); return false;">
                                            <span class="icon text-white-50"><i class="fas fa-fw fa-file-signature"></i></span>
                                            <span class="text">Acta de Cierre Final</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#detallesCursoModal<?= $curso['id_curso'] ?>"><i class="fas fa-eye me-1"></i> Vista Previa</button>
                                    <button type="button" class="btn btn-success" onclick="loadPage('../controllers/buscar.php', { id_curso: <?= $curso['id_curso'] ?> })"><i class="fas fa-users me-1"></i> Inscripciones / Usuarios</button>
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg shadow"><i class="fas fa-save me-2"></i>Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    </div>
    <?php if ($total_pages > 1) echo renderPagination($total_pages, $page, $pagina_actual, $busqueda); ?>
</div>

<?php foreach ($cursos as $curso): ?>
    <div class="modal fade" id="detallesCursoModal<?= $curso['id_curso']; ?>" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl">
          <div class="modal-content">
              <div class="modal-header bg-info text-white">
                  <h5 class="modal-title">Detalles del Curso</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body p-0">
                  <iframe src="../public/detalles_curso.php?id=<?= $curso['id_curso']; ?>" style="width: 100%; height: 80vh;" frameborder="0"></iframe>
              </div>
          </div>
      </div>
    </div>
<?php endforeach; ?>

<div class="modal fade" id="modalBuscarFacilitador" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-search"></i> Buscar Facilitador</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="input-group mb-3">
            <input type="text" class="form-control" id="inputBusquedaFacilitador" placeholder="Buscar..." onkeyup="buscarFacilitador(event)">
            <button class="btn btn-primary" type="button" onclick="ejecutarBusqueda()">Buscar</button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="table-light"><tr><th>Nombre</th><th>Cédula</th><th>Rol</th><th>Título</th><th>Acción</th></tr></thead>
                <tbody id="tablaResultadosFacilitador"><tr><td colspan="5" class="text-center text-muted">...</td></tr></tbody>
            </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// --- LÓGICA DE BÚSQUEDA CURSOS ---
var searchTimeout;
function filtrarCursosDinamico(e) {
    if (e.key === 'Enter') { ejecutarBusquedaCurso(); return; }
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function() { ejecutarBusquedaCurso(); }, 600);
}
function ejecutarBusquedaCurso() { loadPage('editar_cursos.php', { busqueda: document.getElementById('busquedaCursoGlobal').value, page: 1 }); }
function limpiarBusquedaCurso() { loadPage('editar_cursos.php', { busqueda: '', page: 1 }); }

// --- LÓGICA MÓDULOS JS (igual que antes) ---
var newModuleCounter = 0;
function agregarNuevoModulo(idCurso) {
    newModuleCounter++;
    var tempId = 'new_' + newModuleCounter;
    var container = document.getElementById('contenedor_modulos_' + idCurso);
    var nextNum = container.querySelectorAll('.module-border').length + 1;
    var html = `<div class="p-3 border rounded mb-3 module-border bg-light" id="modulo_nuevo_${tempId}">
        <div class="d-flex justify-content-between mb-2">
            <h5 class="text-success">Nuevo Módulo <span class="numero-modulo">${nextNum}</span></h5>
            <button type="button" class="btn btn-danger btn-sm" onclick="eliminarModuloNuevo('${tempId}')"><i class="fas fa-times"></i></button>
        </div>
        <div class="row">
            <div class="col-md-8 mb-3"><label class="form-label">Nombre</label><input type="text" class="form-control" name="modulos[${tempId}][nombre_modulo]" required></div>
            <div class="col-md-4 mb-3"><label class="form-label">Número</label><input type="number" class="form-control" name="modulos[${tempId}][numero]" value="${nextNum}" required></div>
        </div>
        <div class="mb-3"><label class="form-label d-flex justify-content-between">Contenido <button type="button" class="btn btn-secondary btn-circle-sm" onclick="agregarTextareaContenido(this, 'modulos[${tempId}][contenido][]')"><i class="fas fa-plus"></i></button></label>
        <div class="contenidos-container"><div class="d-flex mb-2"><textarea class="form-control me-2" name="modulos[${tempId}][contenido][]" rows="2" required></textarea><button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarTextareaContenido(this)"><i class="fas fa-minus"></i></button></div></div></div>
        <div class="row"><div class="col-md-6 mb-3"><label class="form-label">Actividad</label><input type="text" class="form-control" name="modulos[${tempId}][actividad]" required></div><div class="col-md-6 mb-3"><label class="form-label">Instrumento</label><input type="text" class="form-control" name="modulos[${tempId}][instrumento]" required></div></div>
    </div>`;
    container.insertAdjacentHTML('beforeend', html);
}
function eliminarModuloNuevo(tempId) { document.getElementById('modulo_nuevo_' + tempId)?.remove(); }
function eliminarModuloExistente(idModulo, idCurso) {
    if(confirm('¿Eliminar este módulo?')) {
        document.getElementById('modulo_existente_' + idModulo).style.display = 'none';
        var input = document.getElementById('modulos_a_eliminar_' + idCurso);
        var vals = input.value ? input.value.split(',') : [];
        vals.push(idModulo);
        input.value = vals.join(',');
    }
}
function agregarTextareaContenido(btn, nameAttr) {
    var div = document.createElement('div');
    div.className = 'd-flex mb-2';
    div.innerHTML = `<textarea class="form-control me-2" name="${nameAttr}" rows="2" required></textarea><button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarTextareaContenido(this)"><i class="fas fa-minus"></i></button>`;
    btn.closest('.contenidos-container').appendChild(div);
}
function eliminarTextareaContenido(btn) {
    if(btn.closest('.contenidos-container').querySelectorAll('textarea').length > 1) btn.closest('.d-flex').remove();
    else alert('Mínimo un contenido.');
}

// --- BUSCADOR FACILITADOR ---
var cursoIdActual = null;
function abrirModalFacilitador(idCurso) {
    cursoIdActual = idCurso;
    document.getElementById('inputBusquedaFacilitador').value = '';
    $('#modalBuscarFacilitador').modal('show');
}
function buscarFacilitador(e) { if (e.key === 'Enter') ejecutarBusqueda(); }
function ejecutarBusqueda() {
    var q = document.getElementById('inputBusquedaFacilitador').value;
    if (q.length < 3) return;
    $.ajax({
        url: '../controllers/buscar_usuarios_ajax.php', data: { q: q }, dataType: 'json',
        success: function(data) {
            var html = '';
            if (data.length === 0) html = '<tr><td colspan="5" class="text-center">No encontrado</td></tr>';
            else data.forEach(u => {
                html += `<tr><td>${u.nombre} ${u.apellido}</td><td>${u.cedula}</td><td>${u.nombre_rol||''}</td><td>${u.titulo||''}</td><td><button class="btn btn-sm btn-success" onclick="seleccionarFacilitador(${u.id}, '${u.nombre} ${u.apellido}')">Elegir</button></td></tr>`;
            });
            $('#tablaResultadosFacilitador').html(html);
        }
    });
}
function seleccionarFacilitador(id, nombre) {
    if (cursoIdActual) {
        document.getElementById('promotor_id_' + cursoIdActual).value = id;
        document.getElementById('promotor_nombre_' + cursoIdActual).value = nombre;
        $('#modalBuscarFacilitador').modal('hide');
        cursoIdActual = null;
    }
}
</script>