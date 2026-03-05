<?php
// controllers/obtener_datos_usuario_ajax.php
include 'init.php';
include '../config/model.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['id_rol'], [3, 4, 1])) {
    http_response_code(403);
    die(json_encode(['error' => 'No autorizado']));
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    die(json_encode(['error' => 'ID no proporcionado']));
}

$db = new DB();
$id = (int) $_GET['id'];

try {
    // We only need the ID, the name/surname/etc from the DB
    $stmt = $db->prepare("
        SELECT u.id, u.nombre, u.apellido, u.cedula, u.correo, u.id_rol, u.titulo, u.cargo, u.firma_digital 
        FROM cursos.usuarios u 
        WHERE id = :id
    ");
    $stmt->execute([':id' => $id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        header('Content-Type: application/json');
        echo json_encode($usuario);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de BD: ' . $e->getMessage()]);
}
?>