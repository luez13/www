<?php
// Incluir el archivo header.php en views
include '../views/header.php';

// Incluir el archivo model.php en config
include '../config/model.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'editar_perfil') {
        // Lógica existente para editar perfil
        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $correo = $_POST['correo'];
        $cedula = $_POST['cedula'];
        $id_rol = $_POST['id_rol'];

        $db = new DB();
        $stmt = $db->prepare("UPDATE cursos.usuarios SET nombre = :nombre, apellido = :apellido, correo = :correo, cedula = :cedula, id_rol = :id_rol WHERE id = :id");
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido', $apellido);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':cedula', $cedula);
        $stmt->bindParam(':id_rol', $id_rol);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if (!empty($_POST['nueva_contrasena'])) {
            $nueva_contrasena = $_POST['nueva_contrasena'];
            $hash_nueva_contrasena = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE cursos.usuarios SET password = :hash WHERE id = :id");
            $stmt->bindParam(':hash', $hash_nueva_contrasena);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        }

        header('Location: ../public/usuarios.php');
        exit;
    } elseif ($_POST['action'] === 'eliminar_usuario') {
        // Lógica para eliminar el usuario
        $id = $_POST['id'];

        $db = new DB();
        $stmt = $db->prepare("DELETE FROM cursos.usuarios WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        header('Location: ../public/usuarios.php'); // Redirige tras la eliminación
        exit;
    }
}
?>