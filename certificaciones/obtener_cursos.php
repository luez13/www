<?php
// Conectar a la base de datos
$db = new PDO('pgsql:host=localhost;dbname=certificaciones_DB', 'postgres', '0000');

// Obtener el id del usuario de la sesión
session_start();
$user_id = $_SESSION['user_id'];

// Consultar la base de datos para obtener los cursos disponibles
$stmt = $db->prepare("SELECT * FROM cursos.cursos WHERE estado = true AND id_curso NOT IN (SELECT id_curso FROM cursos.certificaciones WHERE id_usuario = :id_usuario) AND id_curso IN (SELECT id_curso FROM cursos.cursos WHERE limite_inscripciones > (SELECT COUNT(*) FROM cursos.certificaciones WHERE cursos.cursos.id_curso = cursos.certificaciones.id_curso))");
$stmt->execute(['id_usuario' => $user_id]);
$cursos_disponibles = $stmt->fetchAll();

// Mostrar los datos de los cursos
foreach ($cursos_disponibles as $curso) {
    // Use an input of type button to create the collapsible button
    echo '<input type="button" id="curso_' . $curso['id_curso'] . '" class="collapsible" value="' . $curso['nombre_curso'] . '">';
    // Use a div to show the course details when the button is clicked
    echo '<div class="content">';
    echo '<p>' . $curso['descripcion'] . '</p>';
    // Use a form with method POST and a hidden field with value 'inscribirse' to send the action to curso_acciones.php
    echo '<form method="POST" action="curso_acciones.php">';
    echo '<input type="hidden" name="action" value="inscribirse">';
    echo '<input type="hidden" name="id_curso" value="' . $curso['id_curso'] . '">';
    echo '<button type="submit">Inscribirse</button>';
    echo '</form>';
    echo '</div>';
}
?>