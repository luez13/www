<?php
// controllers/admin_usuarios_ajax.php
include 'init.php';
include '../config/model.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['id_rol'], [3, 4, 1])) {
    http_response_code(403);
    die("No autorizado");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new DB();
    $id = (int) $_POST['id_usuario'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $cedula = $_POST['cedula'];
    $correo = $_POST['correo'];
    $id_rol = (int) $_POST['id_rol'];
    $titulo = $_POST['titulo'] ?? '';
    $cargo = $_POST['cargo'] ?? '';
    $nueva_pass = $_POST['nueva_password'] ?? '';

    try {
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
            ':id_rol' => $id_rol,
            ':titulo' => $titulo,
            ':cargo' => $cargo,
            ':id' => $id
        ];

        if (!empty($nueva_pass)) {
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
                $upload_dir = "../public/firmas/";

                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                if (move_uploaded_file($tmp_name, $upload_dir . $unique_name)) {
                    $sql .= ", firma_digital = :firma";
                    // Guardamos la ruta relativa hacia public/firmas/
                    $params[':firma'] = $unique_name;
                }
            }
        }

        $sql .= " WHERE id = :id";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        echo "Usuario actualizado con éxito.";
    } catch (PDOException $e) {
        http_response_code(500);
        echo "Error: " . $e->getMessage();
    }
}
?>