<?php
// Conectar a la base de datos
$db = new PDO('pgsql:host=localhost;dbname=certificaciones_DB', 'postgres', '0000');

// Obtener los datos del formulario
$id_curso = $_POST['id_curso'];
// Los demás datos son iguales a los de crear_curso.php

// Verificar la acción
if ($_POST['action'] == 'crear') {
    // Obtener los datos del formulario
$promotor = $_SESSION['user_id']; // El ID del usuario actual
$modalidad = $_POST['modalidad'];
$nombre_curso = $_POST['nombre_curso'];
$descripcion = $_POST['descripcion'];
$duracion = $_POST['duracion'] . ' ' . ($_POST['unidad_duracion'] == 'dias' ? 'day' : ($_POST['unidad_duracion'] == 'semanas' ? 'week' : 'month')); // Concatenar la duración y la unidad de duración
$periodo = $_POST['periodo'];
$tipo_evaluacion = $_POST['tipo_evaluacion'];
$tipo_curso = $_POST['tipo_curso'];

// Insertar los datos del curso en la base de datos
$stmt = $db->prepare('INSERT INTO cursos.cursos (promotor, modalidad, nombre_curso, descripcion, duracion, periodo, tipo_evaluacion, tipo_curso) VALUES (:promotor, :modalidad, :nombre_curso, :descripcion, :duracion, :periodo, :tipo_evaluacion, :tipo_curso)');
$stmt->execute(['promotor' => $promotor, 'modalidad' => $modalidad, 'nombre_curso' => $nombre_curso, 'descripcion' => $descripcion, 'duracion' => $duracion, 'periodo' => $periodo, 'tipo_evaluacion' => $tipo_evaluacion, 'tipo_curso' => $tipo_curso]);
} elseif ($_POST['action'] == 'editar') {
// Actualizar los datos del curso en la base de datos
$stmt = $db->prepare('UPDATE cursos.cursos SET promotor = :promotor, modalidad = :modalidad, nombre_curso = :nombre_curso, descripcion = :descripcion, duracion = :duracion, periodo = :periodo, tipo_evaluacion = :tipo_evaluacion, nota = :nota, tipo_curso = :tipo_curso, autorizacion = :autorizacion WHERE id_curso = :id_curso');
$stmt->execute(['id_curso' => $id_curso, 'promotor' => $promotor, 'modalidad' => $modalidad, 'nombre_curso' => $nombre_curso, 'descripcion' => $descripcion, 'duracion' => $duracion, 'periodo' => $periodo, 'tipo_evaluacion' => $tipo_evaluacion, 'nota' => $nota, 'tipo_curso' => $tipo_curso, 'autorizacion' => $autorizacion]);
} elseif ($_POST['action'] == 'eliminar') {
    // Eliminar el curso de la base de datos
    $stmt = $db->prepare('DELETE FROM cursos.cursos WHERE id_curso = :id_curso');
    $stmt->execute(['id_curso' => $id_curso]);
}

// Redirigir a la página de cursos
header('Location: cursos.html');
?>