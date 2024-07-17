<?php
// Incluir autenticacion.php
require_once '../controllers/autenticacion.php';

// Iniciar la sesión
session_start();

// Llamar a la función verificar_sesion
verificar_sesion();

// Establecer una variable de sesión para indicar si el usuario está logueado o no
if (isset($_SESSION['nombre'])) {
    $_SESSION['logueado'] = true;
} else {
    $_SESSION['logueado'] = false;
}
?>
<!DOCTYPE html>
<meta charset="UTF-8">
<html>
<head>
    <!-- Enlazar el archivo estilo.css -->
    <link rel="stylesheet" href="../public/assets/estilo.css">
    <!-- Agregar un título a la página -->
    <title>Sistema de gestión de cursos y certificaciones</title>
    <!-- Agregar el CDN de Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.debug.js"></script>
    </head>
<body>
    <!-- Mostrar el nombre del sistema y el nombre del usuario -->
    <h1>Sistema de gestión de cursos y certificaciones</h1>
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
// Mostrar el navbar solo si el usuario está logueado y no está en index.php
if ($_SESSION['logueado'] && basename($_SERVER['PHP_SELF']) != 'index.php') {
    echo '<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">';
    echo '<a class="navbar-brand" href="../public/perfil.php">Gestión de cursos</a>';
    echo '<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">';
    echo '<span class="navbar-toggler-icon"></span>';
    echo '</button>';
    echo '<div class="collapse navbar-collapse" id="navbarNavDropdown">';
    echo '<ul class="navbar-nav mr-auto mt-2 mt-lg-0">';
    echo '<li class="nav-item"><a class="nav-link" href="../public/perfil.php">Perfil</a></li>';
    echo '<li class="nav-item"><a class="nav-link" href="../public/cursos.php">Cursos</a></li>';
    /*echo '<li class="nav-item"><a class="nav-link" href="../public/gestion_cursos.php">Gestión de cursos</a></li>';*/
    echo '</ul>';
    echo '<form class="form-inline my-2 my-lg-0">';
    /*echo '<input class="form-control mr-sm-2" type="search" placeholder="Buscar" aria-label="Buscar">';
    echo '<button class="btn btn-outline-success my-2 my-sm-0" type="submit">Buscar</button>';*/
    echo '</form>';
    // Mostrar el mensaje de bienvenida y el botón de cerrar sesión en la barra de navegación
    echo '<p class="navbar-text">Bienvenido, ' . $_SESSION['nombre'] . '</p>';
    echo '<form class="form-inline" action="../controllers/autenticacion.php" method="post">';
    echo '<input type="hidden" name="action" value="logout">';
    echo '<input class="btn btn-outline-danger my-2 my-sm-0" type="submit" value="Cerrar sesión">';
    echo '</form>';
    echo '</div>';
    echo '</nav>';
}

?>
</html>