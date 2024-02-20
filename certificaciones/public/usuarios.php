<?php
// Incluir el archivo header.php en views
include '../views/header.php';

// Incluir el archivo model.php en config
include '../config/model.php';

$db = new DB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'editar_perfil') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo = $_POST['correo'];
    $cedula = $_POST['cedula'];
    $id_rol = $_POST['id_rol']; // Obtener el id del rol seleccionado

    // Actualizar los datos del usuario
    $stmt = $db->prepare("UPDATE cursos.usuarios SET nombre = :nombre, apellido = :apellido, correo = :correo, cedula = :cedula, id_rol = :id_rol WHERE id = :id");
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':apellido', $apellido);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':cedula', $cedula);
    $stmt->bindParam(':id_rol', $id_rol); // Actualizar el rol del usuario
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    header('Location: ../public/usuarios.php'); // Redirige de nuevo a la página de usuarios
} else {
    // Obtener todos los usuarios y sus roles
    $stmt = $db->prepare("SELECT usuarios.*, roles.nombre_rol FROM cursos.usuarios INNER JOIN cursos.roles ON usuarios.id_rol = roles.id_rol");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener todos los roles
    $stmt = $db->prepare("SELECT * FROM cursos.roles");
    $stmt->execute();
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($usuarios as $usuario) {
        echo '<div class="main-content">';
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
        echo '<label for="id_rol">Rol:</label>';
        echo '<select id="id_rol" name="id_rol">'; // Campo de selección para el rol
        foreach ($roles as $rol) {
            echo '<option value="' . $rol['id_rol'] . '"' . ($usuario['id_rol'] == $rol['id_rol'] ? ' selected' : '') . '>' . $rol['nombre_rol'] . '</option>';
        }
        echo '</select>';
        echo '<input type="submit" value="Guardar cambios">';
        echo '</form>';
        echo '</div>';
    }
}

// Incluir el archivo footer.php en views
include '../views/footer.php';
?>