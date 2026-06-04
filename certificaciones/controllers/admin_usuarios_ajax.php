<?php
// controllers/admin_usuarios_ajax.php
include 'init.php';
include '../config/model.php';
include '../config/mailer.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['id_rol'], [3, 4])) {
    http_response_code(403);
    die("No autorizado");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new DB();
    $action = isset($_POST['action']) ? $_POST['action'] : 'editar_admin';

    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $cedula = $_POST['cedula'];
    $correo = strtolower($_POST['correo']);
    $id_rol = (int) $_POST['id_rol'];
    $titulo = isset($_POST['titulo']) ? $_POST['titulo'] : '';
    $cargo = isset($_POST['cargo']) ? $_POST['cargo'] : '';

    // Si el usuario actual es Rol 3, no puede cambiar roles ni contraseñas
    if ($_SESSION['id_rol'] == 3) {
        $id_rol = null; // Para no actualizarlo
    }

    try {
        if ($action === 'crear_admin') {
            // Validar que el usuario no exista
            $stmt_check = $db->prepare('SELECT id FROM cursos.usuarios WHERE correo = :correo OR cedula = :cedula');
            $stmt_check->execute([':correo' => $correo, ':cedula' => $cedula]);
            if ($stmt_check->fetch()) {
                http_response_code(400);
                die("Error: El correo o la cédula ya están registrados.");
            }

            // Clave autogenerada: primer nombre y primer apellido + "20" en minúscula, sin acentos y sin espacios
            $first_name = trim(explode(' ', trim($nombre))[0]);
            $first_lastname = trim(explode(' ', trim($apellido))[0]);
            $raw_user_str = strtolower($first_name . $first_lastname);
            $replacements = [
                'á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ú'=>'u', 'ü'=>'u', 'ñ'=>'n',
                'Á'=>'a', 'É'=>'e', 'Í'=>'i', 'Ó'=>'o', 'Ú'=>'u', 'Ü'=>'u', 'Ñ'=>'n'
            ];
            $clean_user_str = strtr($raw_user_str, $replacements);
            $pwd_raw = str_replace(' ', '', $clean_user_str) . "20";
            $password = password_hash($pwd_raw, PASSWORD_DEFAULT);
            $token = md5($correo . time());

            $sql = "INSERT INTO cursos.usuarios 
                    (nombre, apellido, cedula, correo, password, id_rol, titulo, cargo, confirmado, token) 
                    VALUES (:nombre, :apellido, :cedula, :correo, :password, :id_rol, :titulo, :cargo, true, :token) RETURNING id";

            $params = [
                ':nombre' => $nombre,
                ':apellido' => $apellido,
                ':cedula' => $cedula,
                ':correo' => $correo,
                ':password' => $password,
                ':id_rol' => $id_rol,
                ':titulo' => $titulo,
                ':cargo' => $cargo,
                ':token' => $token
            ];

            // Subida de Firma Digital si aplica al crear
            if (isset($_FILES['firma_digital']) && $_FILES['firma_digital']['error'] == UPLOAD_ERR_OK) {
                // ... we need the inserted ID to name the signature properly, so we upload it AFTER insert.
            }

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $new_user_id = $stmt->fetchColumn();

            // Si subieron firma al crear, la procesamos y actualizamos el usuaio
            if (isset($_FILES['firma_digital']) && $_FILES['firma_digital']['error'] == UPLOAD_ERR_OK) {
                $tmp_name = $_FILES["firma_digital"]["tmp_name"];
                $name = basename($_FILES["firma_digital"]["name"]);
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                if (in_array($ext, ['jpg', 'jpeg', 'png', 'svg'])) {
                    $unique_name = "firma_" . $new_user_id . "_" . time() . "." . $ext;
                    $upload_dir = "../public/assets/firmas/";
                    if (!is_dir($upload_dir))
                        mkdir($upload_dir, 0777, true);

                    if (move_uploaded_file($tmp_name, $upload_dir . $unique_name)) {
                        $stmtUpdate = $db->prepare("UPDATE cursos.usuarios SET firma_digital = :firma WHERE id = :id");
                        $stmtUpdate->execute([':firma' => $unique_name, ':id' => $new_user_id]);
                    }
                }
            }

            // Enviar correo de bienvenida
            $host_url = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "la plataforma";
            $protocolo = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $app_path = explode('/controllers/', $_SERVER['PHP_SELF'])[0];
            $base_url = $protocolo . "://" . $host_url . $app_path;

            $asunto = "Bienvenido a nuestra plataforma";
            $mensajeHtml = "<h3>¡Hola, " . htmlspecialchars($nombre) . "!</h3>
                            <p>Tu cuenta ha sido creada exitosamente por la administración.</p>
                            <p>Tus credenciales de acceso son las siguientes:</p>
                            <div style='background-color: #f8f9fc; padding: 15px; border-left: 4px solid #4e73df; margin: 20px 0;'>
                                <p style='margin: 0;'><b>Correo:</b> {$correo}</p>
                                <p style='margin: 5px 0 0 0;'><b>Contraseña:</b> {$pwd_raw}</p>
                            </div>
                            <div style='text-align: center; margin: 30px 0;'>
                                <a href='{$base_url}/public/index.php' style='background-color: #4e73df; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Iniciar Sesión</a>
                            </div>
                            <p>Te recomendamos cambiar tu contraseña una vez que inicies sesión por primera vez desde tu perfil.</p>";
            
            enviarCorreo($correo, $asunto, $mensajeHtml);

            echo "Usuario creado con éxito. \nContraseña: " . $pwd_raw;

        } else {
            // Lógica original de Edición (UPDATE)
            $id = (int) $_POST['id_usuario'];
            $nueva_pass = isset($_POST['nueva_password']) ? $_POST['nueva_password'] : '';

            $sql = "UPDATE cursos.usuarios 
                    SET nombre = :nombre, 
                        apellido = :apellido, 
                        cedula = :cedula, 
                        correo = :correo, 
                        id_rol = :id_rol, 
                        titulo = :titulo, 
                        cargo = :cargo";

            $params = [
                ':nombre' => $nombre,
                ':apellido' => $apellido,
                ':cedula' => $cedula,
                ':correo' => $correo,
                ':titulo' => $titulo,
                ':cargo' => $cargo,
                ':id' => $id
            ];

            // Solo actualizar id_rol si no es Rol 3
            if ($_SESSION['id_rol'] != 3) {
                $sql = str_replace("id_rol = :id_rol, ", "id_rol = :id_rol, ", $sql); // (Already in sql, just binding it)
                $params[':id_rol'] = $id_rol;
            } else {
                // Remove id_rol = :id_rol, from $sql
                $sql = str_replace("id_rol = :id_rol, \n", "", $sql);
                $sql = str_replace("id_rol = :id_rol, ", "", $sql);
            }

            if (!empty($nueva_pass) && $_SESSION['id_rol'] != 3) {
                $sql .= ", password = :password";
                $params[':password'] = password_hash($nueva_pass, PASSWORD_DEFAULT);
            }

            // Subida de Firma Digital si aplica
            if (isset($_FILES['firma_digital']) && $_FILES['firma_digital']['error'] == UPLOAD_ERR_OK) {
                $tmp_name = $_FILES["firma_digital"]["tmp_name"];
                $name = basename($_FILES["firma_digital"]["name"]);
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                if (in_array($ext, ['jpg', 'jpeg', 'png', 'svg'])) {
                    $unique_name = "firma_" . $id . "_" . time() . "." . $ext;
                    $upload_dir = "../public/assets/firmas/";

                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    if (move_uploaded_file($tmp_name, $upload_dir . $unique_name)) {
                        $sql .= ", firma_digital = :firma";
                        $params[':firma'] = $unique_name;
                    }
                }
            }

            $sql .= " WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            echo "Usuario actualizado con éxito.";
        }

    } catch (PDOException $e) {
        http_response_code(500);
        echo "Error de BD: " . $e->getMessage();
    }
}
?>