<?php
// Iniciar la sesión
session_start();

// Conectar a la base de datos
$db = new PDO('pgsql:host=localhost;dbname=certificaciones_DB', 'postgres', '0000');

// Obtener el id del usuario de la sesión
$user_id = $_SESSION['user_id'];

// Obtener el id del curso y la acción de los parámetros GET
$id_curso = $_GET['id_curso'];
$action = $_GET['action'];

// Verificar la acción
if ($action == 'inscribirse') {
    // Inscribir al usuario en el curso
    $stmt = $db->prepare('INSERT INTO cursos.certificaciones (id_usuario, id_curso) VALUES (:id_usuario, :id_curso)');
    $stmt->execute(['id_usuario' => $user_id, 'id_curso' => $id_curso]);
} elseif ($action == 'cancelar_inscripcion') {
    // Cancelar la inscripción del usuario en el curso
    $stmt = $db->prepare('DELETE FROM cursos.certificaciones WHERE id_usuario = :id_usuario AND id_curso = :id_curso');
    $stmt->execute(['id_usuario' => $user_id, 'id_curso' => $id_curso]);
}

// Redirigir a la página de perfil
header('Location: perfil.php');
?>