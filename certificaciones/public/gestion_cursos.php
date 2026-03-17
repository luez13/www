<?php
include '../config/model.php';
include '../controllers/init.php';
include '../models/curso.php';

$db = new DB();
$cursoModel = new Curso($db);
$user_id = $_SESSION['user_id'];

echo '<div class="container-fluid">';

// === LÓGICA PARA LA ACCIÓN 'CREAR' ===
if (isset($_GET['action']) && $_GET['action'] == 'crear') {

    // --- Obtenemos los datos necesarios para las firmas por defecto ---
    $stmt_coord = $db->prepare("SELECT id_cargo FROM cursos.cargos WHERE nombre_cargo = 'Coord. Formación Permanente' LIMIT 1");
    $stmt_coord->execute();
    $id_cargo_coordinador = $stmt_coord->fetchColumn();

    $stmt_pos1 = $db->prepare("SELECT id_posicion FROM cursos.posiciones_firma WHERE codigo_posicion = 'P1_INF_DER' LIMIT 1");
    $stmt_pos1->execute();
    $id_posicion_1 = $stmt_pos1->fetchColumn();

    $stmt_pos2 = $db->prepare("SELECT id_posicion FROM cursos.posiciones_firma WHERE codigo_posicion = 'P2_INF_DER' LIMIT 1");
    $stmt_pos2->execute();
    $id_posicion_2 = $stmt_pos2->fetchColumn();

    $stmt_vic = $db->prepare("SELECT valor_config FROM cursos.config_sistema WHERE clave_config = 'ID_CARGO_VICERRECTORADO_POR_DEFECTO' LIMIT 1");
    $stmt_vic->execute();
    $id_cargo_vicerrectora = $stmt_vic->fetchColumn();

    $stmt_pos3 = $db->prepare("SELECT id_posicion FROM cursos.posiciones_firma WHERE codigo_posicion = 'P1_INF_IZQ' LIMIT 1");
    $stmt_pos3->execute();
    $id_posicion_3 = $stmt_pos3->fetchColumn();

    $stmt_plantillas = $db->prepare("SELECT id, nombre, es_defecto FROM cursos.plantillas_certificados ORDER BY es_defecto DESC, nombre ASC");
    $stmt_plantillas->execute();
    $plantillas_certificados = $stmt_plantillas->fetchAll(PDO::FETCH_ASSOC);

?>
    <h1 class="h3 mb-4 text-gray-800">Postular una Nueva Propuesta de Curso</h1>
    
    <form id="crearCursoForm" method="post">
        <input type="hidden" name="action" value="crear">

        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Información General</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Nombre del curso:</label>
                    <input type="text" class="form-control" name="nombre_curso" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción:</label>
                    <textarea class="form-control" name="descripcion" required></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tipo de curso:</label>
                        <select class="form-select" name="tipo_curso" required>
                            <option value="masterclass">MasterClass</option>
                            <option value="taller">Taller</option>
                            <option value="curso">Curso</option>
                            <option value="seminario">Seminario</option>
                            <option value="diplomado">Diplomado</option>
                            <option value="congreso">Congreso</option>
                            <option value="charla">Charla</option>
                            <option value="masterclass_rectoria">MasterClass Rectoría</option>
                            <option value="taller_rectoria">Taller Rectoría</option>
                            <option value="curso_rectoria">Curso Rectoría</option>
                            <option value="seminario_rectoria">Seminario Rectoría</option>
                            <option value="diplomado_rectoria">Diplomado Rectoría</option>
                            <option value="congreso_rectoria">Congreso Rectoría</option>
                            <option value="charla_rectoria">Charla Rectoría</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nivel del curso:</label>
                        <select class="form-select" name="nivel_curso" required>
                            <option value="introductorio">Introductorio</option>
                            <option value="medio">Medio</option>
                            <option value="avanzado">Avanzado</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Plantilla del Certificado a emitir:</label>
                    <select class="form-select" name="id_plantilla">
                        <option value="">-- Usar Diseño por Defecto Global --</option>
                        <?php foreach ($plantillas_certificados as $plantilla): ?>
                            <option value="<?= $plantilla['id'] ?>"><?= htmlspecialchars($plantilla['nombre']) ?> <?= $plantilla['es_defecto'] ? ' (Por Defecto Actual)' : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Si se deja vacío, el curso usará la plantilla que esté configurada como global.</div>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Fechas y Horarios</h6></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Fecha de inicio:</label>
                        <input class="form-control" type="date" name="inicio_mes" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Duración (semanas):</label>
                        <input class="form-control" type="number" name="tiempo_asignado" min="1" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Horario de inicio:</label>
                        <input class="form-control" type="time" name="horario_inicio" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Horario de fin:</label>
                        <input class="form-control" type="time" name="horario_fin" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label d-block">Días de clase:</label>
                    <div class="p-2 border rounded bg-light">
                        <?php $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo']; ?>
                        <?php foreach ($dias as $dia): ?>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="dias_clase[]" value="<?= $dia ?>" id="dia_<?= $dia ?>">
                            <label class="form-check-label" for="dia_<?= $dia ?>"><?= ucfirst($dia) ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Detalles Académicos</h6></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Límite de inscripciones:</label>
                        <input class="form-control" type="number" name="limite_inscripciones" min="1" required>
                    </div>
                     <div class="col-md-6 mb-3">
                        <label class="form-label">Costo:</label>
                        <input class="form-control" type="number" name="costo" step="0.01" min="0">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Conocimientos previos:</label>
                    <textarea class="form-control" name="conocimientos_previos" required></textarea>
                </div>
                 <div class="mb-3">
                    <label class="form-label">Requerimientos e implementos:</label>
                    <textarea class="form-control" name="requerimientos_implementos" required></textarea>
                </div>
                 <div class="mb-3">
                    <label class="form-label">Desempeño al concluir:</label>
                    <textarea class="form-control" name="desempeño_al_concluir" required></textarea>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Módulos del Curso</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Número de módulos:</label>
                    <input class="form-control" type="number" id="numero_modulos" name="numero_modulos" min="1" required onblur="addModuleFields()">
                </div>
                <div id="moduleContainer">
                    </div>
            </div>
        </div>

        <div class="card shadow mb-4 border-left-info">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-info">Configuración de Firmas por Defecto</h6></div>
            <div class="card-body">
                <p>Al crear el curso, se asignarán las siguientes firmas por defecto en el certificado:</p>
                <ul>
                    <li><strong>Posición Coordinador (P1_INF_DER):</strong> Se asignará al "Coordinador de Formación Permanente".</li>
                    
                    <li><strong>Posición Vicerrectora (P1_INF_IZQ):</strong> Se asignará a la "Vicerrectora Académica".</li>

                    <li><strong>Posición Promotor (P2_INF_DER):</strong> Se asignará al Promotor del curso (usted).</li>
                </ul>
                <p class="text-muted small">Esta configuración podrá ser modificada más adelante por un administrador.</p>
                
                <?php if ($id_posicion_1 && $id_cargo_coordinador): ?>
                    <input type="hidden" name="config_firmas[<?php echo $id_posicion_1; ?>][id_cargo_firmante]" value="<?php echo $id_cargo_coordinador; ?>">
                <?php endif; ?>

                <?php if ($id_posicion_3 && $id_cargo_vicerrectora): ?>
                    <input type="hidden" name="config_firmas[<?php echo $id_posicion_3; ?>][id_cargo_firmante]" value="<?php echo $id_cargo_vicerrectora; ?>">
                <?php endif; ?>
                
                <?php if ($id_posicion_2): ?>
                    <input type="hidden" name="config_firmas[<?php echo $id_posicion_2; ?>][usar_promotor_curso]" value="1">
                <?php endif; ?>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-plus-circle me-2"></i>Crear Propuesta</button>
        </div>
    </form>
<?php
// === LÓGICA PARA LA ACCIÓN 'VER' ===
} elseif (isset($_GET['action']) && $_GET['action'] == 'ver') {
    $cursos = $cursoModel->obtener_contenido($user_id);

// Mostrar una tabla con los cursos creados por el usuario
echo '<div class="d-flex justify-content-between align-items-center mb-3">';
echo '<h3 class="m-0 text-gray-800">Cursos creados por ti</h3>';
echo '</div>';

echo '<div class="table-responsive shadow-sm rounded bg-white">';
echo '<table class="table table-hover table-bordered table-sm align-middle mb-0">';
echo '<thead class="table-dark text-center">';
echo '<tr>';
echo '<th style="width: 25%;" class="py-3">Información del Curso</th>';
echo '<th style="width: 25%;" class="py-3">Planificación</th>';
echo '<th style="width: 20%;" class="py-3">Inscripción</th>';
echo '<th style="width: 10%;" class="py-3">Estado</th>';
echo '<th style="width: 20%;" class="py-3">Opciones</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

foreach ($cursos as $curso) {
    echo '<tr>';
    
    // 1. Columna: Información del Curso (Nombre, Tipo y Nivel)
    echo '<td class="px-3">';
    echo '<div class="fw-bold text-primary mb-2" style="font-size: 1.1rem;">' . htmlspecialchars($curso['nombre_curso']) . '</div>';
    echo '<span class="badge bg-secondary me-1 text-uppercase">' . str_replace('_', ' ', $curso['tipo_curso']) . '</span>';
    echo '<span class="badge bg-info text-dark text-capitalize">' . $curso['nivel_curso'] . '</span>';
    echo '</td>';

    // 2. Columna: Planificación (Fechas, Horarios, Días)
    echo '<td class="px-3">';
    echo '<div class="small mb-1"><i class="fas fa-calendar-alt text-muted me-2"></i><strong>Inicio:</strong> ' . date('d/m/Y', strtotime($curso['inicio_mes'])) . ' <span class="text-muted">(' . $curso['tiempo_asignado'] . ' sem.)</span></div>';
    echo '<div class="small mb-1"><i class="fas fa-clock text-muted me-2"></i><strong>Horario:</strong> ' . date('H:i', strtotime($curso['horario_inicio'])) . ' - ' . date('H:i', strtotime($curso['horario_fin'])) . '</div>';
    echo '<div class="small"><i class="fas fa-calendar-week text-muted me-2"></i><strong>Días:</strong> <span class="text-capitalize">' . str_replace(',', ', ', $curso['dias_clase']) . '</span></div>';
    echo '</td>';

    // 3. Columna: Inscripción (Cupos y Costo)
    echo '<td class="px-3">';
    echo '<div class="small mb-1"><i class="fas fa-users text-muted me-2"></i><strong>Cupos:</strong> ' . $curso['limite_inscripciones'] . ' máx.</div>';
    $costo_formateado = ($curso['costo'] > 0) ? '$' . number_format($curso['costo'], 2) : 'Gratuito';
    echo '<div class="small"><i class="fas fa-tag text-muted me-2"></i><strong>Costo:</strong> ' . $costo_formateado . '</div>';
    echo '</td>';

    // 4. Columna: Estado
    echo '<td class="text-center">';
    $badge_class = $curso['estado'] ? 'bg-success' : 'bg-danger';
    $estado_txt = $curso['estado'] ? 'Activo' : 'Finalizado';
    echo '<span class="badge ' . $badge_class . ' px-3 py-2"><i class="fas fa-circle me-1" style="font-size: 0.6em;"></i>' . $estado_txt . '</span>';
    echo '</td>';

// 5. Columna: Opciones
    echo '<td class="px-3 py-2">';
    echo '<div class="d-flex flex-column gap-2 w-100">';
    
    // Botón Editar (Visible para todos)
    echo '<button class="btn btn-sm btn-primary" onclick="loadPage(\'../views/curso_formulario.php\', {id_curso: ' . $curso['id_curso'] . '})"><i class="fas fa-edit me-2"></i>Editar</button>';
    
    // 🔒 RESTRICCIÓN: Botón Iniciar / Finalizar (SOLO ROLES 3 y 4)
    if (isset($_SESSION['id_rol']) && ($_SESSION['id_rol'] == 3 || $_SESSION['id_rol'] == 4)) {
        $estado_texto = $curso['estado'] ? 'Finalizar' : 'Iniciar';
        $action = $curso['estado'] ? 'finalizar' : 'iniciar';
        $icono_estado = $curso['estado'] ? 'fas fa-stop-circle' : 'fas fa-play-circle';
        echo '<button class="btn btn-sm btn-success" onclick="cambiarEstadoCurso(' . $curso['id_curso'] . ', \'' . $action . '\')"><i class="' . $icono_estado . ' me-2"></i>' . $estado_texto . '</button>';
    }
    
    // Menú Desplegable "Opciones Académicas"
    echo '<div class="dropdown w-100">';
    echo '<button class="btn btn-sm btn-info dropdown-toggle w-100 text-white" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-cogs me-2"></i>Opciones</button>';
    echo '<ul class="dropdown-menu shadow-sm">';
    
    echo '<li><a class="dropdown-item" href="#" onclick="loadPage(\'../public/detalles_curso.php\', {id: ' . $curso['id_curso'] . '}); return false;"><i class="fas fa-info-circle me-2 text-secondary"></i>Detalles del curso</a></li>';
    echo '<li><a class="dropdown-item" href="#" data-bs-toggle="collapse" data-bs-target="#modulos-' . $curso['id_curso'] . '"><i class="fas fa-list me-2 text-secondary"></i>Ver Módulos</a></li>';
    echo '<li><a class="dropdown-item" href="#" onclick="generarConstancia(' . $curso['id_curso'] . '); return false;"><i class="fas fa-file-signature me-2 text-secondary"></i>Generar constancia</a></li>';
    echo '<li><a class="dropdown-item" href="#" onclick="duplicarCurso(' . $curso['id_curso'] . '); return false;"><i class="fas fa-copy me-2 text-secondary"></i>Duplicar Curso</a></li>';
    
    // Sección Exclusiva de Diplomados
    $es_diplomado = in_array(strtolower($curso['tipo_curso']), ['diplomado', 'diplomado_rectoria']);
    // Sección Exclusiva de Diplomados
    $es_diplomado = in_array(strtolower($curso['tipo_curso']), ['diplomado', 'diplomado_rectoria']);
    if ($es_diplomado) {
        // 🔒 RESTRICCIÓN: Gestión completa del diplomado (SOLO ROLES 3 y 4)
        if (isset($_SESSION['id_rol']) && ($_SESSION['id_rol'] == 3 || $_SESSION['id_rol'] == 4)) {
            echo '<li><hr class="dropdown-divider"></li>';
            echo '<li><h6 class="dropdown-header text-primary"><i class="fas fa-graduation-cap me-2"></i>Gestión de Diplomado</h6></li>';
            echo '<li><a class="dropdown-item" href="#" onclick="loadPage(\'../views/gestionar_materias.php\', {id_curso: ' . $curso['id_curso'] . '}); return false;"><i class="fas fa-book me-2 text-primary"></i>Materias</a></li>';
            echo '<li><a class="dropdown-item" href="#" onclick="loadPage(\'../views/gestionar_notas.php\', {id_curso: ' . $curso['id_curso'] . '}); return false;"><i class="fas fa-calculator me-2 text-primary"></i>Cargar Notas</a></li>';
            echo '<li><a class="dropdown-item" href="#" onclick="loadPage(\'../views/generar_acta_cierre.php\', {id_curso: ' . $curso['id_curso'] . '}); return false;"><i class="fas fa-file-contract me-2 text-primary"></i>Acta de Cierre</a></li>';
        }
    }
    
    // 🔒 RESTRICCIÓN: Botón Peligroso (Eliminar) - SOLO ROLES 3 y 4
    if (isset($_SESSION['id_rol']) && ($_SESSION['id_rol'] == 3 || $_SESSION['id_rol'] == 4)) {
        echo '<li><hr class="dropdown-divider"></li>';
        echo '<li><a class="dropdown-item text-danger" href="#" onclick="eliminarCurso(' . $curso['id_curso'] . '); return false;"><i class="fas fa-trash-alt me-2"></i>Eliminar Curso</a></li>';
    }
    
    echo '</ul>';
    echo '</div>'; // Fin dropdown
    echo '</div>'; // Fin flexbox
    echo '</td>';
    echo '</tr>';

    // Mostrar los módulos del curso en un contenedor colapsable
    if (!empty($curso['modulos'])) {
        echo '<tr>';
        echo '<td colspan="5" class="p-0 border-0">'; // ATENCIÓN: Cambiamos colspan a 5
        echo '<div id="modulos-' . $curso['id_curso'] . '" class="collapse bg-light p-4 border-bottom border-info border-4">';
        echo '<h5 class="text-info mb-3"><i class="fas fa-layer-group me-2"></i>Módulos del curso</h5>';
        echo '<div class="row">';
        foreach ($curso['modulos'] as $modulo) {
            echo '<div class="col-md-6 mb-3">';
            echo '<div class="card h-100 border-left-info shadow-sm">';
            echo '<div class="card-body py-2">';
            echo '<strong class="text-dark">Módulo ' . $modulo['numero'] . ':</strong> ' . $modulo['nombre_modulo'] . '<br>';
            echo '<small class="text-muted"><strong>Contenido:</strong> ' . $modulo['contenido'] . '</small><br>';
            echo '<small class="text-muted"><strong>Actividad:</strong> ' . $modulo['actividad'] . ' | <strong>Instrumento:</strong> ' . $modulo['instrumento'] . '</small>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>'; // Fin row
        echo '</div>'; // Fin collapse
        echo '</td>';
        echo '</tr>';
    }
}
echo '</tbody>';
echo '</table>';
echo '</div>'; // Fin table-responsive
}

echo '</div>';
?>

<script src="../models/module_processing.js"></script>
<script>

    document.getElementById('crearCursoForm').onsubmit = function(e) {
        e.preventDefault();
        combineContentsBeforeSubmit(); // Asegurarnos de combinar contenidos antes de enviar
        const formData = new FormData(this);
        $.ajax({
            url: '../controllers/curso_controlador.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                alert(response);
                window.location.href = '../public/perfil.php?seccion=ver_postulaciones';
            },
            error: function() {
                alert('Error al crear el curso.');
            }
        });
    };

function generarConstancia(idCurso) {
    window.open(`../controllers/generar_constancia.php?id_curso=${idCurso}`, '_blank');
}
    function cambiarEstadoCurso(id_curso, action) {
        $.ajax({
            url: '../controllers/curso_controlador.php',
            type: 'POST',
            data: { id_curso: id_curso, action: action },
            success: function(response) {
                alert(response);
                window.location.href = '../public/perfil.php?seccion=ver_postulaciones';
            },
            error: function() {
                alert('Error al cambiar el estado del curso.');
            }
        });
    }

    function duplicarCurso(id_curso) {
        if(confirm("¿Estás seguro de que deseas duplicar este curso? (Aparecerá como una copia vacía de cupos y fechas)")) {
            $.ajax({
                url: '../controllers/curso_controlador.php',
                type: 'POST',
                data: { id_curso: id_curso, action: 'duplicar' },
                success: function(response) {
                    alert(response);
                    window.location.href = '../public/perfil.php?seccion=ver_postulaciones';
                },
                error: function() {
                    alert('Error al duplicar el curso. Es posible que el servidor haya devuelto un error o el tiempo de espera se haya agotado.');
                }
            });
        }
    }

    document.getElementById('numero_modulos').onblur = addModuleFields;
</script>