<?php
// Iniciar la sesión
session_start();

// Conectar a la base de datos
$db = new PDO('pgsql:host=localhost;dbname=certificaciones_DB', 'postgres', '0000');

// Obtener los datos del formulario
$id_usuario = $_POST['id_usuario'];
$id_curso = $_POST['id_curso'];
$nota = $_POST['nota'];

// Asignar la nota al usuario en la base de datos
$stmt = $db->prepare("UPDATE cursos.certificaciones SET nota = :nota WHERE id_usuario = :id_usuario AND id_curso = :id_curso");
$stmt->execute(['nota' => $nota, 'id_usuario' => $id_usuario, 'id_curso' => $id_curso]);

// Redirigir al usuario a la página de gestión de cursos
header('Location: gestion_cursos.php');
?>