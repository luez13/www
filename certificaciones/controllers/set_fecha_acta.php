<?php
require_once 'init.php';
require_once '../config/model.php';

// LA DIRECTRIZ DEL AUDITOR TÉCNICO: EL CANDADO DEL BACKEND
// Solo el Rol 4 puede modificar la fecha maestra del diploma/acta final.
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 4) {
    http_response_code(403);
    die("Error crítico de seguridad: No tiene autorización (Rol 4) para fijar fechas forenses.");
}

$id_curso = isset($_POST['id_curso']) ? (int)$_POST['id_curso'] : 0;
$fecha = isset($_POST['fecha']) ? trim($_POST['fecha']) : '';

if ($id_curso <= 0 || empty($fecha)) {
    http_response_code(400);
    die("Datos incompletos.");
}

// Validar formato de fecha simple
if (!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $fecha)) {
    http_response_code(400);
    die("Formato de fecha inválido.");
}

$db = new DB();
$conn = $db->getConn();

try {
    $stmt = $conn->prepare("UPDATE cursos.cursos SET fecha_acta_cierre = :fecha WHERE id_curso = :id_curso");
    $stmt->execute([
        'fecha' => $fecha,
        'id_curso' => $id_curso
    ]);
    
    if ($stmt->rowCount() > 0) {
        echo "Fecha guardada exitosamente en el expediente forense.";
    } else {
        echo "La fecha ya estaba fijada o no hubo cambios.";
    }
} catch (PDOException $e) {
    http_response_code(500);
    die("Error de BD: " . $e->getMessage());
}
