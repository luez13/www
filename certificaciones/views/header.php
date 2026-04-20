<?php
require_once __DIR__ . '/../controllers/autenticacion.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Generar Token CSRF global si no existe para proteger los formularios
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
}

$current_page = basename($_SERVER['PHP_SELF']);
$public_pages = ['index.php', 'register.php', 'recuperar_password.php', 'reset_password.php', 'generar_certificado.php', 'ver_certificados.php'];

if (!in_array($current_page, $public_pages)) {
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
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <!-- Custom styles -->
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/estilo.css">
    <!-- DataTables y Extensiones CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.7.0/css/select.bootstrap4.min.css">
    <style>
        body {
            background: #f8f9fa;
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
            max-height: 200px;
            /* Ajusta esta altura según sea necesario */
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
            z-index: -1;
            /* Asegura que esté detrás del contenido */
        }
    </style>
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <!-- tsParticles Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/tsparticles@2/tsparticles.bundle.min.js"></script>
    <?php if (in_array($current_page, ['index.php', 'register.php', 'recuperar_password.php', 'reset_password.php', 'ver_certificados.php'])): ?>
        <script src="assets/js/tsparticles-config.js" defer></script>
        <?php
    endif; ?>
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
    <!-- Global AJAX Spinner -->
    <div id="global-spinner" class="d-none justify-content-center align-items-center"
        style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.5); z-index: 9999; backdrop-filter: blur(4px);">
        <div class="spinner-border text-light" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>

    <!-- tsParticles Background Container -->
    <?php if (in_array($current_page, ['index.php', 'register.php', 'recuperar_password.php', 'reset_password.php', 'ver_certificados.php'])): ?>
        <div id="particle-background"></div>
    <?php endif; ?>

    <!-- Global Alerts for Auth Messages -->
    <?php if (isset($_SESSION['auth_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-4 shadow-lg text-center"
            style="z-index: 10000; min-width: 300px; max-width: 90%; border-radius: 10px;" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($_SESSION['auth_error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['auth_error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['auth_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-4 shadow-lg text-center"
            style="z-index: 10000; min-width: 300px; max-width: 90%; border-radius: 10px;" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_SESSION['auth_success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['auth_success']); ?>
    <?php endif; ?>

    <div class="container-fluid p-0">
        <!-- Verificar si el archivo actual es perfil.php -->
        <?php if ($current_page === 'perfil.php'): ?>
            <div class="banner-container">
                <?php if ($_SESSION['logueado']): ?>
                    <a href="perfil.php" class="banner-link">
                        <img src="assets/img/vector membrete 1-01.png" alt="Banner" class="banner-image">
                    </a>
                    <?php
                else: ?>
                    <a href="index.php" class="banner-link">
                        <img src="assets/img/vector membrete 1-01.png" alt="Banner" class="banner-image">
                    </a>
                    <?php
                endif; ?>
            </div>
            <?php
        endif; ?>
    </div>
</body>

</html>