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

// Mostrar un formulario para crear un nuevo curso
echo '<h3>Crear un nuevo curso</h3>';
echo '<form action="../controllers/curso_controlador.php" method="post">';
echo '<input type="hidden" name="action" value="crear">';
echo '<p>Nombre del curso: <input type="text" name="nombre_curso" required></p>';
echo '<p>Descripción: <textarea name="descripcion" required></textarea></p>';
echo '<p>Duración (en horas): <input type="number" name="duracion" min="1" required></p>';
echo '<p>Periodo (en días): <input type="date" name="periodo" min="1" required></p>';
echo '<p>Modalidad: <select name="modalidad" required>';
echo '<option value="Presencial">Presencial</option>';
echo '<option value="Virtual">Virtual</option>';
echo '<option value="Mixto">Mixto</option>';
echo '</select></p>';
echo '<p>Tipo de evaluación: 
<input type="radio" id="con_nota" name="tipo_evaluacion" value="true" required>
<label for="con_nota">Con nota</label>
<input type="radio" id="sin_nota" name="tipo_evaluacion" value="false">
<label for="sin_nota">Sin nota</label>
</p>';
echo '<p>Tipo de curso: <select name="tipo_curso" required>';
echo '<option value="PNF">PNF</option>';
echo '<option value="Formacion">Formacion</option>';
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
// Usar el método de la clase Curso para obtener los cursos creados por el usuario
$cursos = $curso->obtener_contenido($user_id);
// Recorrer los cursos y mostrar sus datos en la tabla
foreach ($cursos as $curso) {
    echo '<tr>';
    echo '<td>' . $curso['nombre_curso'] . '</td>';
    echo '<td>' . $curso['descripcion'] . '</td>';
    echo '<td>' . $curso['duracion'] . ' horas</td>';
    echo '<td>' . $curso['periodo'] . ' días</td>';
    echo '<td>' . $curso['modalidad'] . '</td>';
    echo '<td>' . $curso['tipo_evaluacion'] . '</td>';
    echo '<td>' . $curso['tipo_curso'] . '</td>';
    echo '<td>' . $curso['limite_inscripciones'] . '</td>';
    echo '<td>' . $curso['estado'] . '</td>';
    echo '<td>';
    // Mostrar un botón para editar el curso
    echo '<form action="../views/curso_formulario.php" method="get">';
    echo '<input type="hidden" name="id_curso" value="' . $curso['id_curso'] . '">';
    echo '<input type="submit" value="Editar">';
    echo '</form>';

    // Mostrar un botón para eliminar el curso
    echo '<form action="../controllers/curso_controlador.php" method="post">';
    echo '<input type="hidden" name="action" value="eliminar">';
    echo '<input type="hidden" name="id_curso" value="' . $curso['id_curso'] . '">';
    echo '<input type="submit" value="Eliminar">';
    echo '</form>';
    // Mostrar un botón para finalizar el curso
    echo '<form action="../controllers/curso_controlador.php" method="post">';
    echo '<input type="hidden" name="action" value="finalizar">';
    echo '<input type="hidden" name="id_curso" value="' . $curso['id_curso'] . '">';
    echo '<input type="submit" value="Finalizar">';
    echo '</form>';
    echo '</td>';
    echo '</tr>';
}

echo '</table>';

// Incluir el archivo footer.php en views
include '../views/footer.php';
?>