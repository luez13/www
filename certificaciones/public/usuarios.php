<?php
// Incluir el archivo header.php en views
include '../views/header.php';

// Incluir el archivo model.php en config
include '../config/model.php';

// Obtener todos los usuarios
$db = new DB();
$stmt = $db->prepare("SELECT * FROM cursos.usuarios");
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($usuarios as $usuario) {
    // Mostrar los datos del usuario en campos de entrada
    echo '<h3>Editar datos del usuario ' . $usuario['nombre'] . '</h3>';
    echo '<form action="../controllers/usuarios_controlador.php" method="post">';
    echo '<input type="hidden" name="action" value="editar_perfil">';
    echo '<input type="hidden" name="id" value="' . $usuario['id'] . '">';
    echo '<label for="nombre">Nombre:</label>';
    echo '<input type="text" id="nombre" name="nombre" value="' . $usuario['nombre'] . '">';
    echo '<label for="apellido">Apellido:</label>';
    echo '<input type="text" id="apellido" name="apellido" value="' . $usuario['apellido'] . '">';
    echo '<label for="correo">Correo:</label>';
    echo '<input type="text" id="correo" name="correo" value="' . $usuario['correo'] . '">';
    echo '<label for="cedula">Cédula:</label>';
    echo '<input type="text" id="cedula" name="cedula" value="' . $usuario['cedula'] . '">';
    echo '<input type="submit" value="Guardar cambios">';
    echo '</form>';
}
?>