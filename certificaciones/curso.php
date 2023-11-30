<?php
// Iniciar la sesión
session_start();

// Conectar a la base de datos
$db = new PDO('pgsql:host=localhost;dbname=certificaciones_DB', 'postgres', '0000');

// Obtener el id del usuario de la sesión
$user_id = $_SESSION['user_id'];

// Obtener el id del curso del parámetro GET
$id_curso = $_GET['id_curso'];

// Consultar la base de datos para obtener los datos del curso
$stmt = $db->prepare('SELECT * FROM cursos.cursos WHERE id_curso = :id_curso');
$stmt->execute(['id_curso' => $id_curso]);
$curso = $stmt->fetch();

// Consultar la base de datos para obtener el nombre del autor del curso
$stmt = $db->prepare('SELECT nombre FROM cursos.usuarios WHERE id = :id');
$stmt->execute(['id' => $curso['id_usuario']]);
$autor = $stmt->fetch()['nombre'];

// Consultar la base de datos para obtener el estado de finalización del curso para el usuario
$stmt = $db->prepare('SELECT completado FROM cursos.certificaciones WHERE id_usuario = :id_usuario AND id_curso = :id_curso');
$stmt->execute(['id_usuario' => $user_id, 'id_curso' => $id_curso]);
$completado = $stmt->fetch()['completado'];
?>

<!DOCTYPE html>
<meta charset="UTF-8">
<html>
<body>

<h2><?php echo $curso['nombre_curso']; ?></h2>

<p>Autor: <?php echo $autor; ?></p>
<p>Descripción: <?php echo $curso['descripcion']; ?></p>
<p>Contenido: <?php echo $curso['contenido']; ?></p>

<?php
if ($completado) {
    // Mostrar el botón para ver el certificado
    echo '<button onclick="window.location.href=\'ver_certificado.php?id_curso=' . $id_curso . '\'">Ver certificado</button>';
} else {
    // Mostrar el botón para completar el curso
    echo '<button onclick="window.location.href=\'curso_acciones.php?action=completar&id_curso=' . $id_curso . '\'">Completar curso</button>';
}
?>

</body>
</html>