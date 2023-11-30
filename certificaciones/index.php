<!-- index.html -->
<!DOCTYPE html>
<meta charset="UTF-8">
<html>
<body>

<h2>Iniciar sesión</h2>

<form action="autenticacion.php" method="post">
  <input type="hidden" name="action" value="login">
  <label for="correo1">Correo:</label><br>
  <input type="email" id="correo1" name="correo"><br>
  <label for="password1">Contraseña:</label><br>
  <input type="password" id="password1" name="password"><br>
  <input type="submit" value="Iniciar sesión">
</form>

<h2>Registrarse</h2>

<form action="autenticacion.php" method="post">
  <input type="hidden" name="action" value="registro">
  <label for="nombre">Nombre:</label><br>
  <input type="text" id="nombre" name="nombre"><br>
  <label for="apellido">Apellido:</label><br>
  <input type="text" id="apellido" name="apellido"><br>
  <label for="correo2">Correo:</label><br>
  <input type="email" id="correo2" name="correo"><br>
  <label for="password2">Contraseña:</label><br>
  <input type="password" id="password2" name="password"><br>
  <label for="cedula">Cédula:</label><br>
  <input type="text" id="cedula" name="cedula"><br>
  <input type="submit" value="Registrarse">
</form>

</body>
</html>