<?php
// Incluir el archivo header.php
include '../views/header.php';

// Mostrar un formulario para iniciar sesión
echo '<div class="container">';
echo '<h3 class="text-center">Iniciar sesión</h3>';
echo '<form action="../controllers/autenticacion.php" method="post" class="form-horizontal">';
echo '<input type="hidden" name="action" value="login">';
echo '<div class="form-group">';
echo '<label for="correo" class="col-sm-2 control-label">Correo:</label>';
echo '<div class="col-sm-10">';
echo '<input type="email" name="correo" id="correo" class="form-control" required>';
echo '</div>';
echo '</div>';
echo '<div class="form-group">';
echo '<label for="password" class="col-sm-2 control-label">Contraseña:</label>';
echo '<div class="col-sm-10">';
echo '<input type="password" name="password" id="password" class="form-control" required>';
echo '</div>';
echo '</div>';
echo '<div class="form-group">';
echo '<div class="col-sm-offset-2 col-sm-10">';
echo '<input type="submit" value="Iniciar sesión" class="btn btn-primary">';
echo '</div>';
echo '</div>';
echo '</form>';
echo '</div>';

// Mostrar un formulario para registrarse
echo '<div class="container">';
echo '<h3 class="text-center">Registrarse</h3>';
echo '<form action="../controllers/autenticacion.php" method="post" class="form-horizontal">';
echo '<input type="hidden" name="action" value="registro">';
echo '<div class="form-group">';
echo '<label for="nombre" class="col-sm-2 control-label">Nombre:</label>';
echo '<div class="col-sm-10">';
echo '<input type="text" name="nombre" id="nombre" class="form-control" required>';
echo '</div>';
echo '</div>';
echo '<div class="form-group">';
echo '<label for="apellido" class="col-sm-2 control-label">Apellido:</label>';
echo '<div class="col-sm-10">';
echo '<input type="text" name="apellido" id="apellido" class="form-control" required>';
echo '</div>';
echo '</div>';
echo '<div class="form-group">';
echo '<label for="correo" class="col-sm-2 control-label">Correo:</label>';
echo '<div class="col-sm-10">';
echo '<input type="email" name="correo" id="correo" class="form-control" required>';
echo '</div>';
echo '</div>';
echo '<div class="form-group">';
echo '<label for="password" class="col-sm-2 control-label">Contraseña:</label>';
echo '<div class="col-sm-10">';
echo '<input type="password" name="password" id="password" class="form-control" required>';
echo '</div>';
echo '</div>';
echo '<div class="form-group">';
echo '<label for="cedula" class="col-sm-2 control-label">Cédula:</label>';
echo '<div class="col-sm-10">';
echo '<input type="number" name="cedula" id="cedula" class="form-control" required>';
echo '</div>';
echo '</div>';
echo '<div class="form-group">';
echo '<div class="col-sm-offset-2 col-sm-10">';
echo '<input type="submit" value="Registrarse" class="btn btn-success">';
echo '</div>';
echo '</div>';
echo '</form>';
echo '</div>';

// Mostrar un enlace para recuperar la contraseña
// echo '<div class="container">';
// echo '<p class="text-center">¿Olvidaste tu contraseña? <a href="../views/recuperar.php" class="alert-link">Haz clic aquí</a></p>';
// echo '</div>';

// Incluir el archivo footer.php en views
include '../views/footer.php';
?>