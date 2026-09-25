<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/index.php');
    exit;
}

// Destruimos EXCLUSIVAMENTE la extensión seleccionada
if (isset($_SESSION['id_extension'])) {
    unset($_SESSION['id_extension']);
}

// Redirigimos al Gateway para que vuelva a elegir
header('Location: ../public/seleccionar_extension.php');
exit;
