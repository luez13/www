<?php
// Incluir el archivo model.php en config
include '../config/model.php';

// Incluir el archivo header.php en views
include '../views/header.php';

// Crear una instancia de la clase DB
$db = new DB();

// Obtener el id del usuario de la sesión
session_start();
$user_id = $_SESSION['user_id'];

// Consultar la base de datos para obtener los datos del usuario
try {
    $stmt = $db->prepare('SELECT * FROM cursos.usuarios WHERE id = :id');
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch();
    // Mostrar los datos del usuario en formato HTML
    echo '<h3>Datos del usuario</h3>';
    echo '<p>Nombre: ' . $user['nombre'] . '</p>';
    echo '<p>Apellido: ' . $user['apellido'] . '</p>';
    echo '<p>Correo: ' . $user['correo'] . '</p>';
    echo '<p>Cédula: ' . $user['cedula'] . '</p>';
    echo '<p>Rol: ' . $user['id_rol'] . '</p>';
    // Mostrar un botón para editar los datos del usuario
    echo '<form action="../controllers/autenticacion.php" method="post">';
    echo '<input type="hidden" name="action" value="editar">';
    echo '<input type="submit" value="Editar datos">';
    echo '</form>';
} catch (PDOException $e) {
    // Mostrar un mensaje de error al usuario
    echo '<p>Ha ocurrido un error al obtener los datos del usuario: ' . $e->getMessage() . '</p>';
}

// Incluir el archivo footer.php en views
include '../views/footer.php';
?>