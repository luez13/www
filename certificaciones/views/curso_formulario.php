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

// Obtener el id del curso de la URL
$id_curso = $_GET['id_curso'];

// Usar el método de la clase Curso para obtener los datos del curso que se quiere editar
$curso_editar = $curso->obtener_curso($id_curso);

// Mostrar un formulario para editar el curso con los datos actuales
echo '<div class="main-content">';
echo '<h3>Editar curso</h3>';
echo '<form action="../controllers/curso_controlador.php" method="post">';
echo '<input type="hidden" name="action" value="editar">';
echo '<input type="hidden" name="id_curso" value="' . $id_curso . '">';
echo '<p>Nombre: <input type="text" name="nombre_curso" value="' . $curso_editar['nombre_curso'] . '" required></p>';
echo '<p>Descripción: <textarea name="descripcion" required>' . $curso_editar['descripcion'] . '</textarea></p>';
echo '<p>Duración (en días): <input type="number" name="duracion" value="' . $curso_editar['duracion'] . '" min="1" required></p>';
echo '<p>Fecha de inicio: <input type="date" name="periodo" value="' . $curso_editar['periodo'] . '" min="1" required></p>';
echo '<p>Modalidad: <select name="modalidad" required>';
echo '<option value="Presencial"' . ($curso_editar['modalidad'] == 'Presencial' ? ' selected' : '') . '>Presencial</option>';
echo '<option value="Virtual"' . ($curso_editar['modalidad'] == 'Virtual' ? ' selected' : '') . '>Virtual</option>';
echo '<option value="Mixto"' . ($curso_editar['modalidad'] == 'Mixto' ? ' selected' : '') . '>Mixto</option>';
echo '</select></p>';
echo '<p>Tipo de evaluación: ';
echo '<input type="radio" id="calificacion" name="tipo_evaluacion" value="true" ' . ($curso_editar['tipo_evaluacion'] == true ? ' checked' : '') . ' required>';
echo '<label for="calificacion">Calificacion</label>';
echo '<input type="radio" id="participacion" name="tipo_evaluacion" value="false" ' . ($curso_editar['tipo_evaluacion'] == false ? ' checked' : '') . ' required>';
echo '<label for="participacion">Participacion</label>';
echo '</p>';
echo '<p>Tipo de curso: <select name="tipo_curso" required>';
echo '<option value="seminarios"' . ($curso_editar['tipo_curso'] == 'seminarios' ? ' selected' : '') . '>Seminarios</option>';
echo '<option value="diplomados"' . ($curso_editar['tipo_curso'] == 'diplomados' ? ' selected' : '') . '>Diplomados</option>';
echo '<option value="congreso"' . ($curso_editar['tipo_curso'] == 'congreso' ? ' selected' : '') . '>Congreso</option>';
echo '<option value="charlas"' . ($curso_editar['tipo_curso'] == 'charlas' ? ' selected' : '') . '>Charlas</option>';
echo '<option value="talleres"' . ($curso_editar['tipo_curso'] == 'talleres' ? ' selected' : '') . '>Talleres</option>';
echo '</select></p>';
echo '<p>Límite de inscripciones: <input type="number" name="limite_inscripciones" value="' . $curso_editar['limite_inscripciones'] . '" min="1" required></p>';
echo '<p><input type="submit" value="Editar curso"></p>';
echo '</form>';
echo '</div>';

// Incluir el archivo footer.php en views
include '../views/footer.php';
?>