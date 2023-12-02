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
$id_curso = $_GET['id'];

// Usar el método de la clase Curso para obtener los datos del curso que se quiere editar
$curso_editar = $curso->obtener_curso($id_curso);

// Mostrar un formulario para editar el curso con los datos actuales
echo '<h3>Editar curso</h3>';
echo '<form action="controllers/curso_controlador.php" method="post">';
echo '<input type="hidden" name="action" value="editar">';
echo '<input type="hidden" name="id_curso" value="' . $id_curso . '">';
echo '<p>Nombre: <input type="text" name="nombre" value="' . $curso_editar['nombre'] . '" required></p>';
echo '<p>Descripción: <textarea name="descripcion" required>' . $curso_editar['descripcion'] . '</textarea></p>';
echo '<p>Duración (en horas): <input type="number" name="duracion" value="' . $curso_editar['duracion'] . '" min="1" required></p>';
echo '<p>Periodo (en días): <input type="number" name="periodo" value="' . $curso_editar['periodo'] . '" min="1" required></p>';
echo '<p>Modalidad: <select name="modalidad" required>';
echo '<option value="Presencial"' . ($curso_editar['modalidad'] == 'Presencial' ? ' selected' : '') . '>Presencial</option>';
echo '<option value="Virtual"' . ($curso_editar['modalidad'] == 'Virtual' ? ' selected' : '') . '>Virtual</option>';
echo '<option value="Mixto"' . ($curso_editar['modalidad'] == 'Mixto' ? ' selected' : '') . '>Mixto</option>';
echo '</select></p>';
echo '<p>Tipo de evaluación: <select name="tipo_evaluacion" required>';
echo '<option value="Sin nota"' . ($curso_editar['tipo_evaluacion'] == 'Sin nota' ? ' selected' : '') . '>Sin nota</option>';
echo '<option value="Con nota"' . ($curso_editar['tipo_evaluacion'] == 'Con nota' ? ' selected' : '') . '>Con nota</option>';
echo '</select></p>';
echo '<p>Tipo de curso: <select name="tipo_curso" required>';
echo '<option value="Obligatorio"' . ($curso_editar['tipo_curso'] == 'Obligatorio' ? ' selected' : '') . '>Obligatorio</option>';
echo '<option value="Electivo"' . ($curso_editar['tipo_curso'] == 'Electivo' ? ' selected' : '') . '>Electivo</option>';
echo '</select></p>';
echo '<p>Límite de inscripciones: <input type="number" name="limite_inscripciones" value="' . $curso_editar['limite_inscripciones'] . '" min="1" required></p>';
echo '<p><input type="submit" value="Editar curso"></p>';
echo '</form>';

// Incluir el archivo footer.php en views
include '../views/footer.php';
?>