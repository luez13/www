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

    $stmt = $db->prepare("SELECT password FROM cursos.usuarios WHERE id = :id");
    $stmt->bindParam(':id', $usuario['id']);
    $stmt->execute();
    $contrasenaDesdeBD = $stmt->fetchColumn(); // Obtenemos la contraseña desde la base de datos

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
            // Campo para ingresar una nueva contraseña (visible para el administrador)
        if ($_SESSION['user_rol'] == 4) {
            echo '<label for="nueva_contrasena">Nueva Contraseña:</label>';
            echo '<input type="text" id="nueva_contrasena" name="nueva_contrasena">';
            echo '<!-- Botón para abrir la ventana modal -->
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalContrasena">
                        Ver Contraseña
                    </button>

                    <!-- Ventana modal -->
                    <div class="modal fade" id="modalContrasena" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalLabel">Contraseña del Usuario</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <!-- Aquí mostrarás la contraseña desde la base de datos -->
                                    <p>Contraseña: <?php echo $contrasenaDesdeBD; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>'; // Agregamos el botón aquí
        }
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

// Botón para abrir la ventana modal
$botonVerContrasena = '
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalContrasena">
        Ver Contraseña
    </button>
';

// Ventana modal
$ventanaModal = '
    <div class="modal fade" id="modalContrasena" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Contraseña del Usuario</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>La contraseña actual es: ' . $contrasenaDesdeBD . '</p>
                </div>
            </div>
        </div>
    </div>
';

// Incluir el archivo footer.php en views
include '../views/footer.php';
?>