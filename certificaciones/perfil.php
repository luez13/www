<!DOCTYPE html>
<meta charset="UTF-8">
<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<title>Título de la página</title>
</head>
<body>
<?php
// Iniciar la sesión
session_start();

// Conectar a la base de datos
$db = new PDO('pgsql:host=localhost;dbname=certificaciones_DB', 'postgres', '0000');

// Obtener el id del usuario de la sesión
$user_id = $_SESSION['user_id'];

// Consultar la base de datos para obtener los datos del usuario
try {
    $stmt = $db->prepare('SELECT * FROM cursos.usuarios WHERE id = :id');
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    // Mostrar un mensaje de error al usuario
    echo '<p>Ha ocurrido un error al consultar los datos del usuario: ' . $e->getMessage() . '</p>';
}

// Consultar la base de datos para obtener el nombre del rol
try {
    $stmt = $db->prepare('SELECT nombre_rol FROM cursos.roles WHERE id_rol = :id_rol');
    $stmt->execute(['id_rol' => $user['id_rol']]);
    $rol = $stmt->fetch();
} catch (PDOException $e) {
    // Mostrar un mensaje de error al usuario
    echo '<p>Ha ocurrido un error al consultar el nombre del rol: ' . $e->getMessage() . '</p>';
}

// Mostrar los datos del usuario
echo '<h3>Datos del usuario</h3>';
echo '<form method="POST" action="curso_acciones.php">';
echo '<input type="hidden" name="action" value="actualizar_datos">';
echo 'Nombre: <input type="text" name="nombre" value="' . $user['nombre'] . '"><br>';
echo 'Apellido: <input type="text" name="apellido" value="' . $user['apellido'] . '"><br>';
echo 'Correo: <input type="text" name="correo" value="' . $user['correo'] . '"><br>';
echo 'Cédula: <input type="text" name="cedula" value="' . $user['cedula'] . '"><br>';
echo 'Rol: <input type="text" name="rol" value="' . $rol['nombre_rol'] . '" readonly><br>';
echo '<button type="submit">Guardar cambios</button>';
echo '</form>';

// Consultar la base de datos para obtener los cursos en los que el usuario está inscrito
try {
    $stmt = $db->prepare("SELECT * FROM cursos.cursos WHERE id_curso IN (SELECT id_curso FROM cursos.certificaciones WHERE id_usuario = :id_usuario)");
    $stmt->execute(['id_usuario' => $user_id]);
    $cursos_inscritos = $stmt->fetchAll();
} catch (PDOException $e) {
    // Mostrar un mensaje de error al usuario
    echo '<p>Ha ocurrido un error al consultar los cursos inscritos: ' . $e->getMessage() . '</p>';
}

// Mostrar los cursos inscritos por el usuario
echo '<h3>Cursos inscritos</h3>';
foreach ($cursos_inscritos as $curso) {
    echo '<h4>' . $curso['nombre_curso'] . '</h4>';
    echo '<p>' . $curso['descripcion'] . '</p>';
    // Use a form with method POST and a hidden field with value 'cancelar_inscripcion' to send the action to curso_acciones.php
    echo '<form method="POST" action="curso_acciones.php">';
    echo '<input type="hidden" name="action" value="cancelar_inscripcion">';
    echo '<input type="hidden" name="id_curso" value="' . $curso['id_curso'] . '">';
    echo '<button type="submit">Cancelar suscripción</button>';
    echo '</form>';
}

// Consultar la base de datos para obtener los cursos disponibles
try {
    $stmt = $db->prepare("SELECT * FROM cursos.cursos WHERE autorizacion IS NOT NULL AND estado = true AND id_curso NOT IN (SELECT id_curso FROM cursos.certificaciones WHERE id_usuario = :id_usuario)");
    $stmt->execute(['id_usuario' => $user_id]);
    $cursos_disponibles = $stmt->fetchAll();
} catch (PDOException $e) {
    // Mostrar un mensaje de error al usuario
    echo '<p>Ha ocurrido un error al consultar los cursos disponibles: ' . $e->getMessage() . '</p>';
}

// Mostrar los cursos disponibles
echo '<h3>Cursos disponibles</h3>';
foreach ($cursos_disponibles as $curso) {
    echo '<h4>' . $curso['nombre_curso'] . '</h4>';
    echo '<p>' . $curso['descripcion'] . '</p>';
    // Use a form with method POST and a hidden field with value 'inscribirse' to send the action to curso_acciones.php
    echo '<form method="POST" action="curso_acciones.php">';
    echo '<input type="hidden" name="action" value="inscribirse">';
    echo '<input type="hidden" name="id_curso" value="' . $curso['id_curso'] . '">';
    echo '<button type="submit">Inscribirse</button>';
    echo '</form>';
}

// Mostrar botones y características según el rol del usuario
$user_rol = $user['id_rol'];
if ($user_rol == 1) { // Usuario estándar
    // No mostrar nada adicional
} elseif ($user_rol == 2) { // Promotor
    echo '<button onclick="window.location.href=\'curso_formulario.html?action=crear\'">Crear Curso</button>';
    echo '<button onclick="window.location.href=\'gestion_cursos.php\'">Mis Cursos</button>'; // Cambiado de 'Finalizar Curso' a 'Mis Cursos'
} elseif ($user_rol == 3) { // Autorizador
    echo '<button onclick="window.location.href=\'curso_formulario.html?action=autorizar\'">Autorizar Curso</button>';
} elseif ($user_rol == 4) { // Administrador
    echo '<button onclick="window.location.href=\'curso_formulario.html?action=crear\'">Crear Curso</button>';
    echo '<button onclick="window.location.href=\'gestion_cursos.php\'">Mis Cursos</button>'; // Cambiado de 'Finalizar Curso' a 'Mis Cursos'
    echo '<button onclick="window.location.href=\'curso_formulario.html?action=autorizar\'">Autorizar Curso</button>';
}
?>
</body>
</html>