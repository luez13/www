<?php
session_start();
include '../config/model.php';

$user_id = $_SESSION['user_id'];

$db = new DB();

if ($_POST['action'] == 'editar_perfil') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo = $_POST['correo'];
    $cedula = $_POST['cedula'];
    $nuevaContrasena = $_POST['nueva_contrasena'];

    try {
        $stmt = $db->prepare('UPDATE cursos.usuarios SET nombre = :nombre, apellido = :apellido, correo = :correo, cedula = :cedula, password = :password WHERE id = :id');
        $stmt->execute([
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':correo' => $correo,
            ':cedula' => $cedula,
            ':password' => password_hash($nuevaContrasena, PASSWORD_DEFAULT),
            ':id' => $user_id
        ]);

        // Actualizar los datos en la sesión
        $_SESSION['nombre'] = $nombre;
        $_SESSION['apellido'] = $apellido;
        $_SESSION['correo'] = $correo;
        $_SESSION['cedula'] = $cedula;

        echo 'Los datos se han editado correctamente';
    } catch (PDOException $e) {
        echo 'Error al editar los datos: ' . $e->getMessage();
    }
}
?>