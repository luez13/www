<?php
// Incluir el archivo model.php en config
include '../config/model.php';

// Incluir el archivo header.php en views
include '../views/header.php';

// Incluir el archivo curso.php en models
include '../models/curso.php';

// Crear una instancia de la clase DB
$db = new DB();

// Crear una instancia de la clase Curso
$curso = new Curso($db);

// Obtener el id del usuario de la sesión
$user_id = $_SESSION['user_id'];

echo '<div class="main-content">';
// Mostrar un formulario para crear un nuevo curso
echo '<h3>Crear un nuevo curso</h3>';
echo '<form action="../controllers/curso_controlador.php" method="post">';
echo '<input type="hidden" name="action" value="crear">';
echo '<p>Nombre del curso: <input type="text" name="nombre_curso" required></p>';
echo '<p>Descripción: <textarea name="descripcion" required></textarea></p>';
echo '<p>Duración (en días): <input type="number" name="duracion" min="1" required></p>';
echo '<p>Fecha de inicio: <input type="date" name="periodo" min="1" required></p>';
echo '<p>Modalidad: <select name="modalidad" required>';
echo '<option value="Presencial">Presencial</option>';
echo '<option value="Virtual">Virtual</option>';
echo '<option value="Mixto">Mixto</option>';
echo '</select></p>';
echo '<p>Tipo de evaluación: 
<input type="radio" id="calificacion" name="tipo_evaluacion" value= true required>
<label for="calificacion">Calificacion</label>
<input type="radio" id="participacion" name="tipo_evaluacion" value= false required>
<label for="participacion">Participacion</label>
</p>';
echo '<p>Tipo de curso: <select name="tipo_curso" required>';
echo '<option value="seminarios">Seminarios</option>';
echo '<option value="diplomados">Diplomados</option>';
echo '<option value="congreso">Congreso</option>';
echo '<option value="charlas">Charlas</option>';
echo '<option value="talleres">Talleres</option>';
echo '</select></p>';
echo '<p>Límite de inscripciones: <input type="number" name="limite_inscripciones" min="1" required></p>';
echo '<p><input type="submit" value="Crear curso"></p>';
echo '</form>';

// Mostrar una tabla con los cursos creados por el usuario
echo '<h3>Cursos creados por ti</h3>';
echo '<table>';
echo '<tr>';
echo '<th>Nombre</th>';
echo '<th>Descripción</th>';
echo '<th>Duración</th>';
echo '<th>Periodo</th>';
echo '<th>Modalidad</th>';
echo '<th>Tipo de evaluación</th>';
echo '<th>Tipo de curso</th>';
echo '<th>Límite de inscripciones</th>';
echo '<th>Estado</th>';
echo '<th>Opciones</th>';
echo '</tr>';

$cursos = $curso->obtener_contenido($user_id);

foreach ($cursos as $curso) {
    echo '<tr>';
    echo '<td>' . $curso['nombre_curso'] . '</td>';
    echo '<td>' . $curso['descripcion'] . '</td>';
    echo '<td>' . $curso['duracion'] . ' dias</td>';
    echo '<td>' . $curso['periodo'] . ' días</td>';
    echo '<td>' . $curso['modalidad'] . '</td>';
    echo '<td>' . ($curso['tipo_evaluacion'] ? 'Evaluada con ponderacion (calificacion)' : 'Sin ponderacion (calificacion)') . '</td>';
    echo '<td>' . $curso['tipo_curso'] . '</td>';
    echo '<td>' . $curso['limite_inscripciones'] . '</td>';
    echo '<td>' . ($curso['estado'] ? 'Activo' : 'Finalizado') . '</td>';
    echo '<td>';

    // Botones con clases de Bootstrap
    echo '<form action="../views/curso_formulario.php" method="get" class="d-inline-block">';
    echo '<input type="hidden" name="id_curso" value="' . $curso['id_curso'] . '">';
    echo '<input type="submit" value="Editar" class="btn btn-secondary">';
    echo '</form>';

    echo '<form action="../public/detalles_curso.php" method="get" class="d-inline-block">';
    echo '<input type="hidden" name="id" value="' . $curso['id_curso'] . '">';
    echo '<input type="submit" value="Detalles del curso" class="btn btn-dark">';
    echo '</form>';

    $estado = $curso['estado'] ? 'Finalizar' : 'Iniciar';
    $action = $curso['estado'] ? 'finalizar' : 'iniciar';
    $autorizado = $curso['autorizacion'];
    echo '<form id="formCurso" action="../controllers/curso_controlador.php" method="post" class="d-inline-block">';
    echo '<input type="hidden" name="action" value="' . $action . '">';
    echo '<input type="hidden" name="id_curso" value="' . $curso['id_curso'] . '">';
    echo '<input id="botonCurso" type="submit" value="' . $estado . '" class="btn btn-success">';
    echo '</form>';

    echo '<form action="../controllers/curso_controlador.php" method="post" class="d-inline-block">';
    echo '<input type="hidden" name="action" value="eliminar">';
    echo '<input type="hidden" name="id_curso" value="' . $curso['id_curso'] . '">';
    echo '<input type="submit" value="Eliminar" class="btn btn-danger">';
    echo '</form>';

    echo '</td>';
    echo '</tr>';
}
echo '</table>';

echo '</div>';
include '../views/footer.php';
?>

<script>
    window.onload = function() {
        var boton = document.getElementById('botonCurso');
        var form = document.getElementById('formCurso');
        var autorizado = <?php echo $autorizado ? 'true' : 'false'; ?>;

        boton.onclick = function(e) {
            if (!autorizado) {
                e.preventDefault();
                alert('El curso no está autorizado');
            }
        }
    }
</script>