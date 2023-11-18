<!DOCTYPE html>
<meta charset="UTF-8">
<html>
<body>

<h2>Perfil de Usuario</h2>

<div id="datos_usuario"></div>
<div id="cursos_inscritos"></div>
<div id="cursos_completados"></div>
<div id="cursos_disponibles"></div>

<?php
// Iniciar la sesión
session_start();

// Conectar a la base de datos
$db = new PDO('pgsql:host=localhost;dbname=certificaciones_DB', 'postgres', '0000');

// Obtener el id del usuario de la sesión
$user_id = $_SESSION['user_id'];

// Consultar la base de datos para obtener los datos del usuario
$stmt = $db->prepare('SELECT * FROM cursos.usuarios WHERE id = :id');
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch();

// Mostrar los datos del usuario
echo '<h3>Datos del usuario</h3>';
echo 'Nombre: ' . $user['nombre'] . '<br>';
echo 'Apellido: ' . $user['apellido'] . '<br>';
echo 'Correo: ' . $user['correo'] . '<br>';
echo 'Cédula: ' . $user['cedula'] . '<br>';
echo 'Rol: ' . $user['id_rol'] . '<br>';

// Consultar la base de datos para obtener los cursos inscritos por el usuario
$stmt = $db->prepare('SELECT * FROM cursos.cursos JOIN cursos.certificaciones ON cursos.cursos.id_curso = cursos.certificaciones.id_curso WHERE cursos.certificaciones.id_usuario = :id_usuario AND cursos.certificaciones.completado = false');
$stmt->execute(['id_usuario' => $user_id]);
$cursos_inscritos = $stmt->fetchAll();

// Mostrar los cursos inscritos por el usuario
echo '<h3>Cursos inscritos</h3>';
foreach ($cursos_inscritos as $curso) {
    echo $curso['nombre_curso'] . '<br>';
    echo '<button onclick="window.location.href=\'curso_acciones.php?action=cancelar_inscripcion&id_curso=' . $curso['id_curso'] . '\'">Cancelar inscripción</button><br>';
}

// Consultar la base de datos para obtener los cursos disponibles
$stmt = $db->prepare("SELECT * FROM cursos.cursos WHERE autorizacion = 'true' AND id_curso NOT IN (SELECT id_curso FROM cursos.certificaciones WHERE id_usuario = :id_usuario)");
$stmt->execute(['id_usuario' => $user_id]);
$cursos_disponibles = $stmt->fetchAll();

// Mostrar los cursos disponibles
echo '<h3>Cursos disponibles</h3>';
foreach ($cursos_disponibles as $curso) {
    echo $curso['nombre_curso'] . '<br>';
    echo '<button onclick="window.location.href=\'curso_acciones.php?action=inscribirse&id_curso=' . $curso['id_curso'] . '\'">Inscribirse</button><br>';
}

// Mostrar botones y características según el rol del usuario
$user_rol = $user['id_rol'];
if ($user_rol == 1) { // Usuario estándar
    // No mostrar nada adicional
} elseif ($user_rol == 2) { // Promotor
    echo '<button id="crear_curso" onclick="window.location.href=\'curso_formulario.html?action=crear\'">Crear Curso</button>';
    echo '<button id="finalizar_curso" onclick="window.location.href=\'curso_formulario.html?action=finalizar\'">Finalizar Curso</button>';
} elseif ($user_rol == 3) { // Autorizador
    echo '<button id="autorizar_curso" onclick="window.location.href=\'curso_formulario.html?action=autorizar\'">Autorizar Curso</button>';
} elseif ($user_rol == 4) { // Administrador
    echo '<button id="crear_curso" onclick="window.location.href=\'curso_formulario.html?action=crear\'">Crear Curso</button>';
    echo '<button id="finalizar_curso" onclick="window.location.href=\'curso_formulario.html?action=finalizar\'">Finalizar Curso</button>';
    echo '<button id="autorizar_curso" onclick="window.location.href=\'curso_formulario.html?action=autorizar\'">Autorizar Curso</button>';
}
?>

</body>
</html>