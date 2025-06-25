<?php
// controllers/usuarios_controlador.php

// NO incluir header.php. Un controlador AJAX no debe generar HTML.

// Incluir el archivo model.php para la base de datos
include '../config/model.php';

// Verificamos que la solicitud sea POST y que haya una acción definida
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
    // Si no es una solicitud válida, detenemos la ejecución.
    http_response_code(403); // Forbidden
    die('Acceso no autorizado.');
}

try {
    // Creamos la instancia de la base de datos UNA SOLA VEZ
    $db = new DB();
    $action = $_POST['action'];

    if ($action === 'editar_perfil') {
        // --- Lógica para editar perfil ---
        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $correo = strtolower($_POST['correo']); // Estandarizamos a minúsculas
        $cedula = $_POST['cedula'];
        $id_rol = $_POST['id_rol'];
        $titulo = $_POST['titulo'] ?? '';
        $cargo = $_POST['cargo'] ?? '';

        $stmt = $db->prepare("UPDATE cursos.usuarios SET nombre = :nombre, apellido = :apellido, correo = :correo, cedula = :cedula, id_rol = :id_rol, titulo = :titulo, cargo = :cargo WHERE id = :id");
        $stmt->execute([
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':correo' => $correo,
            ':cedula' => $cedula,
            ':id_rol' => $id_rol,
            ':titulo' => $titulo,
            ':cargo' => $cargo,
            ':id' => $id
        ]);

        // Si se proporcionó una nueva contraseña, la actualizamos también
        if (!empty($_POST['nueva_contrasena'])) {
            $nueva_contrasena = $_POST['nueva_contrasena'];
            $hash_nueva_contrasena = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
            $stmt_pass = $db->prepare("UPDATE cursos.usuarios SET password = :hash WHERE id = :id");
            $stmt_pass->execute([':hash' => $hash_nueva_contrasena, ':id' => $id]);
        }
        
        // NO redirigir. En su lugar, enviamos un mensaje de texto plano.
        echo "El usuario se ha editado correctamente";

    } elseif ($action === 'eliminar_usuario') {
        // --- Lógica para eliminar el usuario ---
        $id = $_POST['id'];

        $stmt = $db->prepare("DELETE FROM cursos.usuarios WHERE id = :id");
        $stmt->execute([':id' => $id]);

        // NO redirigir. Enviamos un mensaje de éxito.
        echo "El usuario se ha eliminado correctamente";
    
    } else {
        // Si la acción no es válida
        http_response_code(400); // Bad Request
        echo "Acción no reconocida.";
    }

} catch (PDOException $e) {
    // Si ocurre un error de base de datos, lo capturamos
    http_response_code(500); // Internal Server Error
    // Devolvemos un mensaje de error claro para depuración
    echo "Error en la base de datos: " . $e->getMessage();
}

// Detenemos la ejecución del script para asegurar que no se envíe nada más.
exit;
?>