<?php
// init.php
// Asegúrate de que la ruta a autenticacion.php sea correcta desde la ubicación de init.php
// Usar __DIR__ es más robusto para la inclusión de archivos.
require_once __DIR__ . '/../controllers/autenticacion.php'; // Ajusta esta ruta si es necesario

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Establecer el estado de 'logueado' basado en la existencia de user_id y nombre en la sesión
// Esta lógica es la que tenías. Podrías centralizarla más en autenticacion.php si lo deseas.
$_SESSION['logueado'] = (isset($_SESSION['user_id']) && !empty($_SESSION['user_id']));

// Opcional: puedes definir una función aquí para verificar sesión en vistas AJAX
// que no haga un header('Location: ...') sino que devuelva un error o un fragmento HTML.

function verificar_sesion_ajax($roles_permitidos = []) {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401); // Unauthorized
        echo "<div class='alert alert-danger m-3'>Acceso denegado. Sesión no iniciada.</div>";
        exit;
    }
    if (!empty($roles_permitidos) && !in_array($_SESSION['id_rol'], $roles_permitidos)) {
        http_response_code(403); // Forbidden
        echo "<div class='alert alert-danger m-3'>Acceso denegado. No tienes los permisos necesarios.</div>";
        exit;
    }
    return true;
}

?>