<?php
// Incluir el archivo header.php en views
include '../views/header.php';

// Incluir el archivo model.php en config
include '../config/model.php';

$user_id = $_SESSION['user_id'];
$pagina_actual = 'editar_cursos.php'; // Definir la página actual 

// Verificar si el usuario es administrador
require_once '../controllers/autenticacion.php';
if (!esPerfil3($user_id) && !esPerfil4($user_id)) {
    die('No tienes permiso para ver esta página.');
}

$db = new DB();

$db = new DB();

// 1. Obtener todos los usuarios para el selector de "Promotor del Curso"
$stmt_usuarios = $db->prepare("SELECT id, nombre FROM cursos.usuarios ORDER BY nombre");
$stmt_usuarios->execute();
$todos_los_usuarios = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC);

// 2. Obtener todos los CARGOS activos que pueden firmar
$stmt_cargos = $db->prepare("SELECT id_cargo, nombre_cargo, nombre, apellido FROM cursos.cargos WHERE activo = true ORDER BY nombre_cargo");
$stmt_cargos->execute();
$cargos_firmantes = $stmt_cargos->fetchAll(PDO::FETCH_ASSOC);

// 3. Obtener todas las POSICIONES de firma disponibles
$stmt_posiciones = $db->prepare("SELECT id_posicion, codigo_posicion, descripcion_posicion FROM cursos.posiciones_firma ORDER BY id_posicion");
$stmt_posiciones->execute();
$posiciones_firma = $stmt_posiciones->fetchAll(PDO::FETCH_ASSOC);


// --- Lógica de Paginación ---
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Obtener los cursos para la página actual
$stmt_cursos = $db->prepare("SELECT * FROM cursos.cursos ORDER BY nombre_curso ASC LIMIT :limit OFFSET :offset");
$stmt_cursos->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt_cursos->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt_cursos->execute();
$cursos = $stmt_cursos->fetchAll(PDO::FETCH_ASSOC);

// Total de cursos para la paginación
$stmt_total = $db->prepare("SELECT COUNT(*) FROM cursos.cursos");
$stmt_total->execute();
$total_cursos = $stmt_total->fetchColumn();
$total_pages = ceil($total_cursos / $limit);

function diasToArray($dias_str) {
    // 1. Si la entrada no es un string o está vacía, devuelve un array vacío.
    if (!is_string($dias_str) || empty($dias_str)) {
        return [];
    }

    // 2. Quita las llaves {} del formato de array de PostgreSQL.
    $dias_limpio = trim($dias_str, '{}');

    // 3. Separa el string en un array usando la coma como delimitador.
    $array_dias = explode(',', $dias_limpio);

    // 4. LA MAGIA ESTÁ AQUÍ:
    //    - 'trim' quita los espacios en blanco de cada día (ej: " martes " -> "martes").
    //    - 'strtolower' convierte todo a minúsculas (ej: "Lunes" -> "lunes").
    //    Esto asegura que la comparación siempre funcione.
    $array_dias_limpio = array_map(function($dia) {
        return strtolower(trim($dia));
    }, $array_dias);

    // 5. Devuelve el array limpio, filtrando cualquier elemento que haya quedado vacío.
    return array_filter($array_dias_limpio);
}

// Función para renderizar la paginación (sin cambios, pero puedes pegarla aquí si no está en un include)
function renderPagination($total_pages, $current_page, $pagina_actual) {
    $html = '<nav><ul class="pagination">';
    
    // Botón para la primera página
    if ($current_page > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="#" onclick="loadPage(\'' . $pagina_actual . '\', { page: 1 }); return false;">Primera</a></li>';
        $html .= '<li class="page-item"><a class="page-link" href="#" onclick="loadPage(\'' . $pagina_actual . '\', { page: ' . ($current_page - 1) . ' }); return false;">&laquo; Anterior</a></li>';
    }
    
    // Determinar el rango de páginas a mostrar
    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);
    
    // Ajustar si estamos cerca del principio o final
    if ($current_page <= 3) {
        $end_page = min(5, $total_pages);
    }
    if ($current_page >= $total_pages - 2) {
        $start_page = max(1, $total_pages - 4);
    }
    
    // Páginas numéricas
    for ($i = $start_page; $i <= $end_page; $i++) {
        $active = $i == $current_page ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '"><a class="page-link" href="#" onclick="loadPage(\'' . $pagina_actual . '\', { page: ' . $i . ' }); return false;">' . $i . '</a></li>';
    }
    
    // Botón para la última página
    if ($current_page < $total_pages) {
        $html .= '<li class="page-item"><a class="page-link" href="#" onclick="loadPage(\'' . $pagina_actual . '\', { page: ' . ($current_page + 1) . ' }); return false;">Siguiente &raquo;</a></li>';
        $html .= '<li class="page-item"><a class="page-link" href="#" onclick="loadPage(\'' . $pagina_actual . '\', { page: ' . $total_pages . ' }); return false;">Última</a></li>';
    }
    
    $html .= '</ul></nav>';
    return $html;
}
?>
<style>
    /* Estilo para la opción seleccionada en los menús desplegables */
    select option:checked,
    select option[selected] {
        font-weight: bold;
        color: #198754; /* Un verde oscuro, el color 'success' de Bootstrap */
    }
</style>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Editar Cursos</h1>
    <div class="accordion" id="accordionCursos">
    
    <?php if (empty($cursos)): ?>
        <div class="alert alert-info">No hay cursos para mostrar.</div>
    <?php else: ?>
        <?php foreach ($cursos as $curso): ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading<?= $curso['id_curso'] ?>">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $curso['id_curso'] ?>" aria-expanded="false" aria-controls="collapse<?= $curso['id_curso'] ?>">
                        <strong><?= htmlspecialchars($curso['nombre_curso']) ?></strong>&nbsp;<span class="badge bg-secondary ms-2">ID: <?= $curso['id_curso'] ?></span>
                    </button>
                </h2>
                <div id="collapse<?= $curso['id_curso'] ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $curso['id_curso'] ?>" data-bs-parent="#accordionCursos">
                    <div class="accordion-body">
                        <form id="editarCursoForm<?= $curso['id_curso'] ?>" action="../controllers/curso_controlador.php" method="post" class="editarCursoForm">
                            <input type="hidden" name="action" value="editar">
                            <input type="hidden" name="id_curso" value="<?= $curso['id_curso'] ?>">

                            <div class="card shadow mb-4">
                                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Información General</h6></div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="nombre_curso<?= $curso['id_curso'] ?>" class="form-label">Nombre del curso</label>
                                        <input type="text" class="form-control" id="nombre_curso<?= $curso['id_curso'] ?>" name="nombre_curso" value="<?= htmlspecialchars($curso['nombre_curso']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="descripcion<?= $curso['id_curso'] ?>" class="form-label">Descripción</label>
                                        <textarea class="form-control" id="descripcion<?= $curso['id_curso'] ?>" name="descripcion" required><?= htmlspecialchars($curso['descripcion']) ?></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="tipo_curso<?= $curso['id_curso'] ?>" class="form-label">Tipo de curso</label>
                                            <select class="form-select" id="tipo_curso<?= $curso['id_curso'] ?>" name="tipo_curso" required>
                                                <?php $tipos = ['masterclass', 'seminario', 'diplomado', 'congreso', 'charla', 'taller', 'curso', 'masterclass_rectoria', 'seminario_rectoria', 'diplomado_rectoria', 'congreso_rectoria', 'charla_rectoria', 'taller_rectoria', 'curso_rectoria']; ?>
                                                <?php foreach ($tipos as $tipo): ?>
                                                    <option value="<?= $tipo ?>" <?= ($curso['tipo_curso'] == $tipo) ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $tipo)) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="nivel_curso<?= $curso['id_curso'] ?>" class="form-label">Nivel del curso</label>
                                            <input type="text" class="form-control" id="nivel_curso<?= $curso['id_curso'] ?>" name="nivel_curso" value="<?= htmlspecialchars($curso['nivel_curso']) ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card shadow mb-4">
                                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Fechas y Horarios</h6></div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="inicio_mes<?= $curso['id_curso'] ?>" class="form-label">Fecha de Inicio</label>
                                            <input type="date" class="form-control" id="inicio_mes<?= $curso['id_curso'] ?>" name="inicio_mes" value="<?= htmlspecialchars($curso['inicio_mes']) ?>" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="fecha_finalizacion<?= $curso['id_curso'] ?>" class="form-label">Fecha de Finalización</label>
                                            <input type="datetime-local" class="form-control" id="fecha_finalizacion<?= $curso['id_curso'] ?>" name="fecha_finalizacion" value="<?= htmlspecialchars($curso['fecha_finalizacion']) ?>" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="tiempo_asignado<?= $curso['id_curso'] ?>" class="form-label">Duración (en semanas)</label>
                                            <input type="number" class="form-control" id="tiempo_asignado<?= $curso['id_curso'] ?>" name="tiempo_asignado" value="<?= htmlspecialchars($curso['tiempo_asignado']) ?>" min="1" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                         <div class="col-md-4 mb-3">
                                            <label for="horario_inicio<?= $curso['id_curso'] ?>" class="form-label">Horario de inicio</label>
                                            <input type="time" class="form-control" id="horario_inicio<?= $curso['id_curso'] ?>" name="horario_inicio" value="<?= htmlspecialchars($curso['horario_inicio']) ?>" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="horario_fin<?= $curso['id_curso'] ?>" class="form-label">Horario de fin</label>
                                            <input type="time" class="form-control" id="horario_fin<?= $curso['id_curso'] ?>" name="horario_fin" value="<?= htmlspecialchars($curso['horario_fin']) ?>" required>
                                        </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label d-block">Días de clase</label>
                                                <div class="p-2 border rounded bg-light">
                                                    <?php
                                                    // Lista de todos los días de la semana para generar los checkboxes
                                                    $dias_semana = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
                                                    // Convierte el string de la base de datos (ej: "{lunes,martes}") en un array de PHP
                                                    $dias_guardados = diasToArray($curso['dias_clase']);

                                                    foreach ($dias_semana as $dia):
                                                        // Revisa si el día actual está en el array de días guardados para marcar el checkbox
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
                                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Detalles Académicos y Administrativos</h6></div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="costo<?= $curso['id_curso'] ?>" class="form-label">Costo</label>
                                            <input type="number" class="form-control" id="costo<?= $curso['id_curso'] ?>" name="costo" value="<?= htmlspecialchars($curso['costo']) ?>" step="0.01" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="horas_cronologicas<?= $curso['id_curso'] ?>" class="form-label">Horas cronológicas</label>
                                            <input type="number" class="form-control" id="horas_cronologicas<?= $curso['id_curso'] ?>" name="horas_cronologicas" value="<?= htmlspecialchars($curso['horas_cronologicas']) ?>" step="0.1" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="limite_inscripciones<?= $curso['id_curso'] ?>" class="form-label">Límite de inscripción</label>
                                            <input type="number" class="form-control" id="limite_inscripciones<?= $curso['id_curso'] ?>" name="limite_inscripciones" value="<?= htmlspecialchars($curso['limite_inscripciones']) ?>" required>
                                        </div>
                                    </div>
                                     <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="conocimientos_previos<?= $curso['id_curso'] ?>" class="form-label">Conocimientos previos</label>
                                            <textarea class="form-control" id="conocimientos_previos<?= $curso['id_curso'] ?>" name="conocimientos_previos" required><?= htmlspecialchars($curso['conocimientos_previos']) ?></textarea>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="requerimientos_implemento<?= $curso['id_curso'] ?>" class="form-label">Requerimientos de implemento</label>
                                            <textarea class="form-control" id="requerimientos_implemento<?= $curso['id_curso'] ?>" name="requerimientos_implementos" required><?= htmlspecialchars($curso['requerimientos_implemento']) ?></textarea>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="desempeno_al_concluir<?= $curso['id_curso'] ?>" class="form-label">Desempeño al concluir</label>
                                        <textarea class="form-control" id="desempeno_al_concluir<?= $curso['id_curso'] ?>" name="desempeño_al_concluir" required><?= htmlspecialchars($curso['desempeno_al_concluir']) ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card shadow mb-4">
                                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Gestión y Autorización</h6></div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="promotor<?= $curso['id_curso'] ?>" class="form-label">Promotor del Curso</label>
                                            <select class="form-select" id="promotor<?= $curso['id_curso'] ?>" name="promotor" required>
                                                <?php
                                                $promotor_actual_id = $curso['promotor'];
                                                $promotor_actual_encontrado = false;

                                                if ($promotor_actual_id) {
                                                    foreach ($todos_los_usuarios as $p) {
                                                        if ($p['id'] == $promotor_actual_id) {
                                                            $promotor_actual_encontrado = true;
                                                            // Imprimimos la opción seleccionada con un prefijo de texto simple.
                                                            // El CSS que añadimos se encargará de ponerlo verde y en negrita.
                                                            echo '<option value="' . htmlspecialchars($p['id']) . '" selected>';
                                                            echo '[Actual] ' . htmlspecialchars($p['nombre']);
                                                            echo '</option>';
                                                            break;
                                                        }
                                                    }
                                                }

                                                if (!$promotor_actual_encontrado) {
                                                    echo '<option value="" selected disabled>-- Asignar un Promotor --</option>';
                                                }

                                                foreach ($todos_los_usuarios as $p) {
                                                    if ($p['id'] != $promotor_actual_id) {
                                                        echo '<option value="' . htmlspecialchars($p['id']) . '">' . htmlspecialchars($p['nombre']) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Estado de Autorización</label>
                                            <?php if ($curso['autorizacion']):
                                                $stmt_auth = $db->prepare("SELECT nombre FROM cursos.usuarios WHERE id = :id");
                                                $stmt_auth->execute([':id' => $curso['autorizacion']]);
                                                $nombre_autorizador = $stmt_auth->fetchColumn();
                                            ?>
                                                <div class="alert alert-success p-2">
                                                    Autorizado por: <strong><?= htmlspecialchars($nombre_autorizador) ?></strong>
                                                </div>
                                            <?php else: ?>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="autorizacion<?= $curso['id_curso'] ?>" name="autorizacion" value="<?= $user_id ?>">
                                                    <label class="form-check-label" for="autorizacion<?= $curso['id_curso'] ?>">Autorizar este curso ahora</label>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="mt-3 pt-3 border-top">
                                        <div class="form-check form-switch form-check-lg">
                                            <input class="form-check-input" type="checkbox" role="switch" id="firma_digital<?= $curso['id_curso'] ?>" name="firma_digital" value="1" <?= ($curso['firma_digital'] ? 'checked' : '') ?>>
                                            <label class="form-check-label" for="firma_digital<?= $curso['id_curso'] ?>">Habilitar Firma Digital en el Certificado</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card shadow mb-4">
                                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Módulos del Curso</h6></div>
                                <div class="card-body">
                                    <?php
                                    $stmt_modulos = $db->prepare("SELECT * FROM cursos.modulos WHERE id_curso = :curso_id ORDER BY numero ASC");
                                    $stmt_modulos->execute([':curso_id' => $curso['id_curso']]);
                                    $modulos = $stmt_modulos->fetchAll(PDO::FETCH_ASSOC);
                                    ?>
                                    <?php if (empty($modulos)): ?>
                                        <p>Este curso aún no tiene módulos definidos.</p>
                                    <?php else: ?>
                                        <?php foreach ($modulos as $index_modulo => $modulo): ?>
                                            <div class="p-3 border rounded mb-3">
                                                <h5>Editar Módulo <?= $index_modulo + 1 ?>: <?= htmlspecialchars($modulo['nombre_modulo']) ?></h5>
                                                <input type="hidden" name="id_modulo[]" value="<?= $modulo['id_modulo'] ?>">

                                                <div class="row">
                                                    <div class="col-md-8 mb-3">
                                                        <label for="nombre_modulo_<?= $modulo['id_modulo'] ?>" class="form-label">Nombre del módulo</label>
                                                        <input type="text" class="form-control" id="nombre_modulo_<?= $modulo['id_modulo'] ?>" name="nombre_modulo[]" value="<?= htmlspecialchars($modulo['nombre_modulo']) ?>" required>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label for="numero_modulo_<?= $modulo['id_modulo'] ?>" class="form-label">Número del módulo</label>
                                                        <input type="number" class="form-control" id="numero_modulo_<?= $modulo['id_modulo'] ?>" name="numero_modulo[]" value="<?= htmlspecialchars($modulo['numero']) ?>" required>
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Contenido</label>
                                                    <?php
                                                    $contenidos = explode('][', trim($modulo['contenido'], '[]'));
                                                    foreach ($contenidos as $key_contenido => $contenido):
                                                    ?>
                                                        <textarea class="form-control mb-2" name="contenido[]" required><?= htmlspecialchars($contenido) ?></textarea>
                                                        <input type="hidden" name="id_modulo_contenido[]" value="<?= $modulo['id_modulo'] ?>">
                                                    <?php endforeach; ?>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="actividad_modulo_<?= $modulo['id_modulo'] ?>" class="form-label">Actividad</label>
                                                        <input type="text" class="form-control" id="actividad_modulo_<?= $modulo['id_modulo'] ?>" name="actividad_modulo[]" value="<?= htmlspecialchars($modulo['actividad']) ?>" required>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="instrumento_modulo_<?= $modulo['id_modulo'] ?>" class="form-label">Instrumento</label>
                                                        <input type="text" class="form-control" id="instrumento_modulo_<?= $modulo['id_modulo'] ?>" name="instrumento_modulo[]" value="<?= htmlspecialchars($modulo['instrumento']) ?>" required>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Configuración de Certificado y Firmas</h6>
                                </div>
                                <div class="card-body">
                                    <?php
                                    // Esta consulta SÍ debe estar dentro del bucle, porque es específica para CADA curso.
                                    $stmt_config = $db->prepare("SELECT id_posicion, id_cargo_firmante, usar_promotor_curso FROM cursos.cursos_config_firmas WHERE id_curso = :id_curso");
                                    $stmt_config->execute([':id_curso' => $curso['id_curso']]);
                                    $configs_curso_raw = $stmt_config->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    $configs_curso = [];
                                    foreach ($configs_curso_raw as $config) {
                                        $configs_curso[$config['id_posicion']] = $config;
                                    }
                                    
                                    foreach ($posiciones_firma as $posicion):
                                        $id_posicion_actual = $posicion['id_posicion'];
                                        $config_actual = $configs_curso[$id_posicion_actual] ?? null;
                                    ?>
                                        <div class="row align-items-center mb-3 p-2 border rounded bg-light">
                                            <div class="col-md-3">
                                                <strong><?= htmlspecialchars($posicion['descripcion_posicion']) ?></strong>
                                                <small class="d-block text-muted">(<?= htmlspecialchars($posicion['codigo_posicion']) ?>)</small>
                                            </div>
                                            <div class="col-md-5">
                                                <label for="cargo_<?= $curso['id_curso'] ?>_<?= $id_posicion_actual ?>" class="form-label small">Firmante Asignado (Cargo)</label>
                                                <select class="form-select" name="config_firmas[<?= $id_posicion_actual ?>][id_cargo_firmante]" id="cargo_<?= $curso['id_curso'] ?>_<?= $id_posicion_actual ?>">
                                                    <option value="">-- Ninguno --</option>
                                                    <?php foreach ($cargos_firmantes as $cargo): ?>
                                                        <?php
                                                            $selected = (isset($config_actual['id_cargo_firmante']) && $config_actual['id_cargo_firmante'] == $cargo['id_cargo']) ? 'selected' : '';
                                                        ?>
                                                        <option value="<?= $cargo['id_cargo'] ?>" <?= $selected ?>>
                                                            <?= htmlspecialchars($cargo['nombre_cargo']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4 d-flex align-items-end">
                                                <div class="form-check form-switch mt-4">
                                                    <?php
                                                        $checked_promotor = (isset($config_actual['usar_promotor_curso']) && $config_actual['usar_promotor_curso']) ? 'checked' : '';
                                                    ?>
                                                    <input class="form-check-input" type="checkbox" name="config_firmas[<?= $id_posicion_actual ?>][usar_promotor_curso]" value="1" id="promotor_<?= $curso['id_curso'] ?>_<?= $id_posicion_actual ?>" <?= $checked_promotor ?>>
                                                    <label class="form-check-label" for="promotor_<?= $curso['id_curso'] ?>_<?= $id_posicion_actual ?>">
                                                        Usar Promotor del Curso
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="mt-4 pt-4 border-top">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="btn-group" role="group" aria-label="Acciones secundarias">
                                        <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#detallesCursoModal<?= $curso['id_curso'] ?>">
                                            <i class="fas fa-eye me-1"></i> Ver Detalles
                                        </button>
                                        <button type="button" class="btn btn-outline-success" onclick="loadPage('../controllers/buscar.php', { id_curso: <?= $curso['id_curso'] ?> })">
                                            <i class="fas fa-users me-1"></i> Agregar Usuarios
                                        </button>
                                    </div>

                                    <div>
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-save me-2"></i>Guardar Cambios
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div></div></div><?php endforeach; ?>
    <?php endif; ?>
    </div><?php if ($total_pages > 1) echo renderPagination($total_pages, $page, $pagina_actual); ?>
</div>

<?php foreach ($cursos as $curso): ?>
    <div class="modal fade" id="detallesCursoModal<?= $curso['id_curso']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Detalles del Curso</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                  <iframe src="detalles_curso.php?id=<?= $curso['id_curso']; ?>" style="width: 100%; height: 80vh;" frameborder="0"></iframe>
              </div>
          </div>
      </div>
    </div>
<?php endforeach; ?>

<?php
// Incluir el archivo footer.php en views
include '../views/footer.php';
?>