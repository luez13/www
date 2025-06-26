<?php
include '../config/model.php';
include '../models/Sugerencia.php'; // Incluye el modelo

$db = new DB();
$sugerenciaModel = new Sugerencia($db);

if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Iniciar sesión si no está iniciada
}

$user_data = [];

if (isset($_SESSION['user_id'])) {
    // Suponiendo que los datos del usuario están almacenados en la sesión
    $user_data = array(
        'nombre' => isset($_SESSION['nombre']) ? $_SESSION['nombre'] : '',
        'apellido' => isset($_SESSION['apellido']) ? $_SESSION['apellido'] : '',
        'correo' => isset($_SESSION['correo']) ? $_SESSION['correo'] : '',
        'cedula' => isset($_SESSION['cedula']) ? $_SESSION['cedula'] : ''
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo = $_POST['correo'];
    $cedula = $_POST['cedula'];
    $sugerencia = $_POST['sugerencia'];
    $id_usuario = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    $success = $sugerenciaModel->agregarSugerencia($nombre, $apellido, $correo, $cedula, $sugerencia, $id_usuario);

    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'error' => $success ? null : 'Error al agregar sugerencia']);
    exit();
} else {
    // Para solicitudes GET, devolver los datos del usuario
    header('Content-Type: application/json');
    echo json_encode(['user_data' => $user_data]);
    exit();
}
?>