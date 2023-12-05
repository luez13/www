<?php
// Incluir el archivo model.php en config
include '../config/model.php';

// Incluir el archivo header.php en views
include '../views/header.php';

// Incluir el archivo curso.php en models
include '../models/curso.php';

// Obtener el id del usuario de la sesión
$id_usuario = $_SESSION['user_id'];

// Crear una instancia de la clase DB
$db = new DB();

// Crear una instancia de la clase Curso
$curso = new Curso($db);

// Obtener el id del curso de la URL
$id_curso = $_GET['id'];

// Usar el método de la clase Curso para obtener el contenido del curso
$curso_contenido = $curso->obtener_contenido($id_curso);

// Asignar el tipo de evaluación según el valor booleano
if ($curso_contenido['tipo_evaluacion'] == 0) {
    $tipo_evaluacion = 'Presencial';
} else {
    $tipo_evaluacion = 'Evaluada';
}

// Mostrar el contenido del curso en formato HTML
echo '<h3>Contenido del curso</h3>';
echo '<p>Nombre: ' . $curso_contenido['nombre_curso'] . '</p>';
echo '<p>Descripción: ' . $curso_contenido['descripcion'] . '</p>';
echo '<p>Duración: ' . $curso_contenido['duracion'] . '</p>';
echo '<p>Periodo: ' . $curso_contenido['periodo'] . '</p>';
echo '<p>Modalidad: ' . $curso_contenido['modalidad'] . '</p>';
// Mostrar el tipo de evaluación del curso
echo '<p>Tipo de evaluación: ' . $tipo_evaluacion . '</p>';
echo '<p>Tipo de curso: ' . $curso_contenido['tipo_curso'] . '</p>';
echo '<p>Límite de inscripciones: ' . $curso_contenido['limite_inscripciones'] . '</p>';
echo '<p>Promotor: ' . $curso_contenido['promotor'] . '</p>';
echo '<form action="../controllers/curso_acciones.php" method="post">';
echo '<input type="hidden" name="action" value="inscribirse">';
echo '<input type="hidden" name="curso_id" value="' . $id_curso . '">';
echo '<input type="hidden" name="id_usuario" value="' . $id_usuario . '">'; // Asegúrate de tener disponible la variable $id_usuario
echo '<input type="submit" value="Inscribirse al curso">';
echo '</form>';
// Incluir el archivo footer.php en views
include '../views/footer.php';
?>