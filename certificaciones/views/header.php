<?php
require_once '../controllers/autenticacion.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
if ($current_page !== 'register.php' && $current_page !== 'generar_certificado.php' && $current_page !== 'ver_certificados.php') {
    verificar_sesion();
}
$_SESSION['logueado'] = isset($_SESSION['nombre']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de gestión de cursos y certificaciones</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="../public/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <!-- Custom styles -->
    <link href="../public/assets/css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/assets/css/estilo.css">
    <style>
        body {
            background: #6c757d;
        }
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem 0 rgba(0, 0, 0, 0.1);
        }
        .card-body {
            padding: 2rem;
        }
        .form-input {
            border-radius: 2rem;
            padding: 1.5rem 1rem;
        }
        .btn-login {
            border-radius: 2rem;
            padding: 0.75rem 1rem;
            font-weight: bold;
            background-color: #4e73df;
            border-color: #4e73df;
        }
        .btn-login:hover {
            background-color: #2e59d9;
            border-color: #2653d4;
        }
        .banner-container {
            position: relative;
            width: 100%;
            overflow: hidden;
            background-color: #f8f9fa;
        }
        .banner-image {
            width: 100%;
            height: auto;
            display: block;
            max-height: 200px; /* Ajusta esta altura según sea necesario */
            margin: 0 auto;
        }
        .banner-link {
            display: block;
        }
        #particle-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1; /* Asegura que esté detrás del contenido */
        }
    </style>
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/p5.js/1.4.0/p5.js"></script>
    <?php if ($current_page === 'index.php'): ?>
        <script src="../public/assets/js/particle-network.js" defer></script>
    <?php endif; ?>
    <script>
        function confirmarInscripcion() {
            console.log("Confirmar inscripción llamado");
            return confirm("¿Estás seguro de que quieres inscribirte en este curso?");
        }
        function confirmarCancelacion() {
            console.log("Confirmar cancelación llamado");
            return confirm("¿Estás seguro de que quieres cancelar tu inscripción en este curso?");
        }
    </script>
</head>
<body>
    <div class="container-fluid p-0">
        <!-- Verificar si el archivo actual es perfil.php -->
        <?php if ($current_page === 'perfil.php'): ?>
        <div class="banner-container">
            <?php if ($_SESSION['logueado']): ?>
                <a href="../public/perfil.php" class="banner-link">
                    <img src="../public/assets/img/vector membrete 1-01.png" alt="Banner" class="banner-image">
                </a>
            <?php else: ?>
                <a href="../public/index.php" class="banner-link">
                    <img src="../public/assets/img/vector membrete 1-01.png" alt="Banner" class="banner-image">
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>