<?php
// Conectar a la base de datos
$db = new PDO('pgsql:host=localhost;dbname=certificaciones_DB', 'postgres', '0000');

// Obtener el id del usuario de la sesión
session_start();
$user_id = $_SESSION['user_id'];

// Consultar la base de datos para obtener los datos del usuario
$stmt = $db->prepare('SELECT * FROM cursos.usuarios WHERE id = :id');
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch();

// Devolver los datos del usuario como JSON
echo json_encode($user);
?>