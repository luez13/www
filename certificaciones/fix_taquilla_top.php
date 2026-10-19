<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/model.php';
require_once 'autenticacion.php';

header('Content-Type: application/json; charset=utf-8');

// Validar acceso: Solo Roles 4 (ADMIN) y 5 (ANALISTA)
if (!isset($_SESSION['user_id']) || !tieneAcceso([3, 4, 5])) {
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado.']);
    exit;
}

$id_admin = $_SESSION['user_id'];

if (!isset($_POST['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Acción no especificada.']);
    exit;
}

$action = $_POST['action'];
$db = new DB();
$pdo = $db->getConn();
