<?php

// Mostrar un formulario para iniciar sesión
echo '<h3>Iniciar sesión</h3>';
echo '<form action="../controllers/autenticacion.php" method="post">';
echo '<input type="hidden" name="action" value="login">';
echo '<p>Correo: <input type="email" name="correo" required></p>';
echo '<p>Contraseña: <input type="password" name="password" required></p>';
echo '<p><input type="submit" value="Iniciar sesión"></p>';
echo '</form>';

// Mostrar un formulario para registrarse
echo '<h3>Registrarse</h3>';
echo '<form action="../controllers/autenticacion.php" method="post">';
echo '<input type="hidden" name="action" value="registro">';
echo '<p>Nombre: <input type="text" name="nombre" required></p>';
echo '<p>Apellido: <input type="text" name="apellido" required></p>';
echo '<p>Correo: <input type="email" name="correo" required></p>';
echo '<p>Contraseña: <input type="password" name="password" required></p>';
echo '<p>Cédula: <input type="number" name="cedula" required></p>';
echo '<p><input type="submit" value="Registrarse"></p>';
echo '</form>';

// Mostrar un enlace para recuperar la contraseña
echo '<p>¿Olvidaste tu contraseña? <a href="../views/recuperar.php">Haz clic aquí</a></p>';

// Incluir el archivo footer.php en views
include '../views/footer.php';
?>