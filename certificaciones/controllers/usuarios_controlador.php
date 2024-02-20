<?php
// Incluir el archivo header.php en views
include '../views/header.php';

// Incluir el archivo model.php en config
include '../config/model.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'editar_perfil') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo = $_POST['correo'];
    $cedula = $_POST['cedula'];
    $id_rol = $_POST['id_rol']; // Obtener el id del rol seleccionado

    // Actualizar los datos del usuario
    $db = new DB();
    $stmt = $db->prepare("UPDATE cursos.usuarios SET nombre = :nombre, apellido = :apellido, correo = :correo, cedula = :cedula, id_rol = :id_rol WHERE id = :id");
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':apellido', $apellido);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':cedula', $cedula);
    $stmt->bindParam(':id_rol', $id_rol); // Actualizar el rol del usuario
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    header('Location: ../public/usuarios.php'); // Redirige de nuevo a la página de usuarios
}
?>