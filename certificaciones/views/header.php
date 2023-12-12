<?php
// Incluir autenticacion.php
require_once '../controllers/autenticacion.php';

// Iniciar la sesión
session_start();

// Llamar a la función verificar_sesion
verificar_sesion();
?>
<!DOCTYPE html>
<meta charset="UTF-8">
<html>
<head>
    <!-- Enlazar el archivo estilo.css -->
    <link rel="stylesheet" href="../public/assets/estilo.css">
    <!-- Agregar un título a la página -->
    <title>Sistema de gestión de cursos y certificaciones</title>
</head>
<body>
    <!-- Mostrar el nombre del sistema y el nombre del usuario -->
    <h1>Sistema de gestión de cursos y certificaciones</h1>
    <h2>Bienvenido, <?php echo $_SESSION['nombre']; ?></h2>
    <!-- Mostrar un botón para cerrar sesión -->
    <form action="../controllers/autenticacion.php" method="post">
        <input type="hidden" name="action" value="logout">
        <input type="submit" value="Cerrar sesión">
    </form>