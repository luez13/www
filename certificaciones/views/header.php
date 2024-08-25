<?php
// Incluir autenticacion.php
require_once '../controllers/autenticacion.php';

// Verificar si la sesión no está iniciada antes de llamar a session_start()
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si la página actual es register.php
$current_page = basename($_SERVER['PHP_SELF']);
if ($current_page !== 'register.php') {
    // Llamar a la función verificar_sesion
    verificar_sesion();
}

// Establecer una variable de sesión para indicar si el usuario está logueado o no
if (isset($_SESSION['nombre'])) {
    $_SESSION['logueado'] = true;
} else {
    $_SESSION['logueado'] = false;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Enlazar el archivo estilo.css -->
    <link rel="stylesheet" href="../public/assets/css/estilo.css">
    <!-- Agregar el CDN de Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Enlazar otros archivos CSS necesarios -->
    <link href="../public/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../public/assets/css/sb-admin-2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.debug.js"></script>
    <title>Sistema de gestión de cursos y certificaciones</title>
    <?php if ($current_page === 'perfil.php' || $current_page === 'index.php'): ?>
        <img style="width:100%; height:auto; max-height:150px;" src="../public/assets/img/vector membrete 1-01.png">
    <?php endif; ?>
</head>
<body class="bg-gradient-light">
    <?php
    echo '<style>
    .navbar-text {
        margin-right: 4px;
    }
    .form-inline {
        margin-left: 8px;
    }
    .form-inline.my-2.my-lg-0 {
        margin-right: 10px;
    }
    .main-content {
        padding-top: 70px; /* Ajusta este valor según el alto de tu navbar */
    }
    </style>';
    ?>