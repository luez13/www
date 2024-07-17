<?php
// Incluir el archivo model.php en config
include '../config/model.php';

// Incluir el archivo header.php en views
include '../views/header.php';

$user_id = $_SESSION['user_id'];

// Verificar si la acción es "editar"
if ($_POST['action'] == 'editar') {
    // Obtener los datos del usuario del formulario
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo = $_POST['correo'];
    $cedula = $_POST['cedula'];
    $nuevaContrasena = $_POST['nueva_contrasena']; // Nuevo campo para la nueva contraseña

    echo '<div class="main-content">';
    // Mostrar los datos del usuario en campos de entrada
    echo '<h3>Editar datos del usuario</h3>';
    echo '<form action="../controllers/autenticacion.php" method="post" onsubmit="return confirmarEdicion()">';
    echo '<input type="hidden" name="action" value="editar_perfil">';
    echo '<input type="hidden" name="nombre" value="' . htmlspecialchars($nombre) . '">';
    echo '<input type="hidden" name="apellido" value="' . htmlspecialchars($apellido) . '">';
    echo '<input type="hidden" name="correo" value="' . htmlspecialchars($correo) . '">';
    echo '<input type="hidden" name="cedula" value="' . htmlspecialchars($cedula) . '">';
    echo '<label for="nombre">Nombre:</label>';
    echo '<input type="text" id="nombre" name="nombre" value="' . $nombre . '">';
    echo '<label for="apellido">Apellido:</label>';
    echo '<input type="text" id="apellido" name="apellido" value="' . $apellido . '">';
    echo '<label for="correo">Correo:</label>';
    echo '<input type="text" id="correo" name="correo" value="' . $correo . '">';
    echo '<label for="cedula">Cédula:</label>';
    echo '<input type="text" id="cedula" name="cedula" value="' . $cedula . '">';
    // Nuevo campo para la nueva contraseña
    echo '<label for="nueva_contrasena">Nueva Contraseña:</label>';
    echo '<input type="password" id="nueva_contrasena" name="nueva_contrasena">';
    echo '<input type="submit" value="Guardar cambios">';
    echo '</form>';
    echo '</div>';
}

echo '<script>
function confirmarEdicion() {
    alert("Los datos se han editado");
    return true;
}
</script>';

// Incluir el archivo footer.php en views
include '../views/footer.php';
?>