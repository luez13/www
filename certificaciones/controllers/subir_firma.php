<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['firma_digital'])) {
    $target_dir = "../public/assets/firmas/";
    $target_file = $target_dir . basename($_FILES["firma_digital"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["firma_digital"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        $uploadOk = 0;
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        if (!isset($_POST['overwrite']) || $_POST['overwrite'] != 'true') {
            echo json_encode(['success' => false, 'message' => 'El archivo ya existe. ¿Desea sobreescribirlo?', 'file_exists' => true]);
            exit;
        }
    }

    // Check file size
    if ($_FILES["firma_digital"]["size"] > 500000) {
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        echo json_encode(['success' => false, 'message' => 'Error al subir la imagen.']);
    } else {
        if (move_uploaded_file($_FILES["firma_digital"]["tmp_name"], $target_file)) {
            // Save the file path to the database
            include '../config/model.php';
            $db = new DB();
            $stmt = $db->prepare("UPDATE cursos.usuarios SET firma_digital = :firma_digital WHERE id = :id");
            $stmt->bindParam(':firma_digital', $target_file);
            $stmt->bindParam(':id', $_SESSION['user_id']);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Firma digital subida exitosamente.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar la ruta en la base de datos.']);
            }
        } else {
            error_log("Error al mover la imagen: " . $_FILES["firma_digital"]["error"]);
            echo json_encode(['success' => false, 'message' => 'Error al mover la imagen.']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Solicitud no válida.']);
}
?>