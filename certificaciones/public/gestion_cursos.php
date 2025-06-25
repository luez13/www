<?php
include '../config/model.php';
include '../views/header.php';
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
                    <li><strong>Posición 1 (Inferior Derecha):</strong> Se asignará al cargo "Coordinador de Formación Permanente".</li>
                    <li><strong>Posición 2 (Inferior Derecha):</strong> Se asignará al Promotor del curso (usted).</li>
                </ul>
                <p class="text-muted small">Esta configuración podrá ser modificada más adelante por un administrador.</p>
                
                <?php if ($id_posicion_1 && $id_cargo_coordinador): ?>
                    <input type="hidden" name="config_firmas[<?= $id_posicion_1 ?>][id_cargo_firmante]" value="<?= $id_cargo_coordinador ?>">
                <?php endif; ?>
                <?php if ($id_posicion_2): ?>
                    <input type="hidden" name="config_firmas[<?= $id_posicion_2 ?>][usar_promotor_curso]" value="1">
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
echo '<h3>Cursos creados por ti</h3>';
echo '<div class="table-responsive">';
echo '<table class="table table-striped">';
echo '<thead class="thead-dark">';
echo '<tr>';
echo '<th>Nombre</th>';
echo '<th>Descripción</th>';
echo '<th>Semanas</th>';
echo '<th>Fecha de inicio</th>';
echo '<th>Tipo de curso</th>';
echo '<th>Límite de inscripciones</th>';
echo '<th>Días de clase</th>';
echo '<th>Horario de inicio</th>';
echo '<th>Horario de fin</th>';
echo '<th>Nivel del curso</th>';
echo '<th>Costo</th>';
echo '<th>Conocimientos previos</th>';
echo '<th>Requerimientos e implementos</th>';
echo '<th>Desempeño al concluir</th>';
echo '<th>Estado</th>';
echo '<th>Opciones</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

foreach ($cursos as $curso) {
    echo '<tr>';
    echo '<td>' . $curso['nombre_curso'] . '</td>';
    echo '<td>' . $curso['descripcion'] . '</td>';
    echo '<td>' . $curso['tiempo_asignado'] . '</td>';
    echo '<td>' . $curso['inicio_mes'] . '</td>';
    echo '<td>' . $curso['tipo_curso'] . '</td>';
    echo '<td>' . $curso['limite_inscripciones'] . '</td>';
    echo '<td>' . $curso['dias_clase'] . '</td>';
    echo '<td>' . $curso['horario_inicio'] . '</td>';
    echo '<td>' . $curso['horario_fin'] . '</td>';
    echo '<td>' . $curso['nivel_curso'] . '</td>';
    echo '<td>' . $curso['costo'] . '</td>';
    echo '<td>' . $curso['conocimientos_previos'] . '</td>';
    echo '<td>' . $curso['requerimientos_implemento'] . '</td>';
    echo '<td>' . $curso['desempeno_al_concluir'] . '</td>';
    echo '<td>' . ($curso['estado'] ? 'Activo' : 'Finalizado') . '</td>';
    echo '<td>';
    echo '<div class="btn-group-vertical" role="group">';
    echo '<button class="btn btn-secondary mb-1" onclick="loadPage(\'../views/curso_formulario.php\', {id_curso: ' . $curso['id_curso'] . '})">Editar</button>';
    
    echo '<button class="btn btn-dark mb-1" onclick="loadPage(\'../public/detalles_curso.php\', {id: ' . $curso['id_curso'] . '})">Detalles del curso</button>';
    
    $estado = $curso['estado'] ? 'Finalizar' : 'Iniciar';
    $action = $curso['estado'] ? 'finalizar' : 'iniciar';
    echo '<button class="btn btn-success mb-1" onclick="cambiarEstadoCurso(' . $curso['id_curso'] . ', \'' . $action . '\')">' . $estado . '</button>';
    
    // Mostrar el botón de eliminar solo si no hay inscritos o aprobados
    echo '<button class="btn btn-danger" onclick="eliminarCurso(' . $curso['id_curso'] . ')">Eliminar</button>';
    
    // Botón para mostrar/ocultar módulos
    echo '<button class="btn btn-info mb-1" data-bs-toggle="collapse" data-bs-target="#modulos-' . $curso['id_curso'] . '">Módulos</button>';
    
    // Botón para generar constancia
    echo '<button class="btn btn-warning mb-1" onclick="generarConstancia(' . $curso['id_curso'] . ')">Generar constancia</button>';
    
    echo '</div>';    
    echo '</td>';
    echo '</tr>';

    // Mostrar los módulos del curso en un contenedor colapsable
    if (!empty($curso['modulos'])) {
        echo '<tr>';
        echo '<td colspan="16">';
        echo '<div id="modulos-' . $curso['id_curso'] . '" class="collapse">';
        echo '<h4>Módulos del curso</h4>';
        echo '<ul class="list-group">';
        foreach ($curso['modulos'] as $modulo) {
            echo '<li class="list-group-item">';
            echo '<strong>Modulo' . $modulo['numero'] .  '</strong> <br>';
            echo '<strong>Nombre:</strong> ' . $modulo['nombre_modulo'] . '<br>';
            echo '<strong>Contenido:</strong> ' . $modulo['contenido'] . '<br>';
            echo '<strong>Actividad:</strong> ' . $modulo['actividad'] . '<br>';
            echo '<strong>Instrumento:</strong> ' . $modulo['instrumento'] . '<br>';
            echo '</li>';
        }
        echo '</ul>';
        echo '</div>';
        echo '</td>';
        echo '</tr>';
    }
}
echo '</tbody>';
echo '</table>';
echo '</div>';
}

echo '</div>';
include '../views/footer.php';
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

    document.getElementById('numero_modulos').onblur = addModuleFields;
</script>