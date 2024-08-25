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
        // Construir la consulta SQL
        $sql = 'UPDATE cursos.usuarios SET nombre = :nombre, apellido = :apellido, correo = :correo, cedula = :cedula';

        // Agregar la contraseña a la consulta solo si se ha ingresado una nueva
        if (!empty($nuevaContrasena)) {
            $sql .= ', password = :password';
        }

        $sql .= ' WHERE id = :id';

        // Preparar la consulta
        $stmt = $db->prepare($sql);

        // Vincular los parámetros
        $params = [
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':correo' => $correo,
            ':cedula' => $cedula,
            ':id' => $user_id
        ];

        if (!empty($nuevaContrasena)) {
            $params[':password'] = password_hash($nuevaContrasena, PASSWORD_DEFAULT);
        }

        // Ejecutar la consulta
        $stmt->execute($params);

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