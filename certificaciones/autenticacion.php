<?php
// Iniciar la sesión
session_start();

// Conectar a la base de datos
$db = new PDO('pgsql:host=localhost;dbname=certificaciones_DB', 'postgres', '0000');

// Verificar la acción
if ($_POST['action'] == 'registro') {
    // Obtener los datos del formulario
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo = $_POST['correo'];
    $password = $_POST['password'];
    $cedula = $_POST['cedula'];
    $id_rol = $_POST['id_rol'];

    // Insertar los datos del usuario en la base de datos
    $stmt = $db->prepare('INSERT INTO cursos.usuarios (nombre, apellido, correo, password, cedula, id_rol) VALUES (:nombre, :apellido, :correo, :password, :cedula, :id_rol)');
    $stmt->execute(['nombre' => $nombre, 'apellido' => $apellido, 'correo' => $correo, 'password' => $password, 'cedula' => $cedula, 'id_rol' => $id_rol]);

    // Redirigir a la página de inicio de sesión
    header('Location: index.html');
} elseif ($_POST['action'] == 'login') {
    // Obtener los datos del formulario
    $correo = $_POST['correo'];
    $password = $_POST['password'];

    // Consultar la base de datos para verificar las credenciales del usuario
    $stmt = $db->prepare('SELECT * FROM cursos.usuarios WHERE correo = :correo AND password = :password');
    $stmt->execute(['correo' => $correo, 'password' => $password]);
    $user = $stmt->fetch();

    // Verificar si las credenciales son correctas
    if ($user) {
        // Iniciar sesión y guardar el rol del usuario en la sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_rol'] = $user['id_rol'];

        // Redirigir a la página de perfil
        header('Location: perfil.php');
    } else {
        // Mostrar un mensaje de error
        echo 'Las credenciales son incorrectas.';
    }
}
?>