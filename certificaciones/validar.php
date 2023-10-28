<?php
require_once('db_connect.php');

function get_data($conn, $cedula = null) {
    if ($cedula) {
        $stmt = $conn->prepare("SELECT usuario.nombre, usuario.apellido, usuario.correo, documentos.nombre_doc FROM usuario INNER JOIN doc_ref ON usuario.id=doc_ref.user_id INNER JOIN documentos ON doc_ref.document_id=documentos.id_doc WHERE usuario.cedula = :cedula");
        $stmt->execute([':cedula' => $cedula]);
    } else {
        $stmt = $conn->prepare("SELECT usuario.cedula FROM usuario");
        $stmt->execute();
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

try {
    if (isset($_POST['cedula']) && !empty($_POST['cedula'])) {
        $data = get_data($conn, $_POST['cedula']);
        if (!empty($data)) {
            echo json_encode($data);
        } else {
            echo json_encode(["Error" => "No se encontraron datos para la cédula proporcionada"]);
        }
    } else {
        echo json_encode(get_data($conn));
    }
} catch(PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(["Error" => "Ha ocurrido un error al obtener los datos"]);
}
?>