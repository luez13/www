<?php
// controllers/procesar_importacion_usuarios.php
include 'init.php';
include '../config/model.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['id_rol'], [3, 4, 1])) {
    http_response_code(403);
    die('No autorizado');
}

$db = new DB();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["usuarios"])) {
    $usuariosData = json_decode($_POST["usuarios"], true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        die("Invalid JSON data provided.");
    }

    $errors = [];
    $insertedCount = 0;

    $stmtInsert = $db->prepare('INSERT INTO cursos.usuarios (nombre, apellido, correo, password, cedula, token, confirmado, id_rol) VALUES (:nombre, :apellido, :correo, :password, :cedula, :token, true, 1)');

    foreach ($usuariosData as $usuario) {
        if ($usuario['valido'] == true) {
            $nombre = $usuario['nombre'];
            $apellido = $usuario['apellido'];
            $correo = $usuario['correo'];
            $password = password_hash($usuario['password'], PASSWORD_DEFAULT);
            $cedula = $usuario['cedula'];
            $token = bin2hex(random_bytes(16));

            // Final DB Check just in case race condition happened
            $stmtCheck = $db->prepare('SELECT COUNT(*) FROM cursos.usuarios WHERE cedula = :cedula OR correo = :correo');
            $stmtCheck->execute([':cedula' => $cedula, ':correo' => $correo]);

            if ($stmtCheck->fetchColumn() == 0) {
                try {
                    $stmtInsert->execute([
                        ':nombre' => $nombre,
                        ':apellido' => $apellido,
                        ':correo' => $correo,
                        ':password' => $password,
                        ':cedula' => $cedula,
                        ':token' => $token
                    ]);
                    $insertedCount++;
                } catch (PDOException $e) {
                    $errors[] = "Error insertando Cédula $cedula: " . $e->getMessage();
                }
            } else {
                $errors[] = "La Cédula $cedula o Correo $correo ya fueron insertados entre el análisis y la confirmación.";
            }
        }
    }

    if (empty($errors)) {
        echo "Importación exitosa. Se han registrado $insertedCount usuarios correctamente.";
    } else {
        echo "Importación finalizada con $insertedCount creados. Hubo algunos errores: \n" . implode("\n", $errors);
    }
}
?>