<?php
// Conectar a la base de datos
$db = new PDO('pgsql:host=localhost;dbname=certificaciones_DB', 'postgres', '0000');

// Obtener el id del usuario de la sesión
session_start();
$user_id = $_SESSION['user_id'];

// Consultar la base de datos para obtener solo los cursos relevantes para el usuario
$stmt = $db->prepare('SELECT * FROM cursos.cursos JOIN cursos.certificaciones ON cursos.cursos.id_curso = cursos.certificaciones.id_curso WHERE cursos.certificaciones.id_usuario = :id_usuario');
$stmt->execute(['id_usuario' => $user_id]);
$cursos = $stmt->fetchAll();

// Mostrar los datos de los cursos
foreach ($cursos as $curso) {
    echo '<h3>' . $curso['nombre_curso'] . '</h3>';
    echo '<p>' . $curso['descripcion'] . '</p>';
}
?>
