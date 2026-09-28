<?php
require_once __DIR__ . '/../config/model.php';
require_once __DIR__ . '/../controllers/autenticacion.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_extension'])) {
    $id_extension = (int) $_POST['id_extension'];
    
    $db = new DB();
    $conn = $db->getConn();
    
    // Validar por seguridad que el usuario realmente tiene acceso a esta extensión
    $stmtExt = $conn->prepare('
        SELECT e.id_extension, e.es_academico 
        FROM cursos.usuarios_extensiones ue 
        JOIN cursos.extensiones e ON ue.id_extension = e.id_extension 
        WHERE ue.id_usuario = :id_usuario AND ue.id_extension = :id_extension AND ue.activo = TRUE AND e.activa = TRUE
    ');
    $stmtExt->execute([
        ':id_usuario' => $_SESSION['user_id'],
        ':id_extension' => $id_extension
    ]);
    
    $data = $stmtExt->fetch(PDO::FETCH_ASSOC);
    if ($data) {
        // Es válido, establecer sesión y redirigir
        $_SESSION['id_extension'] = $id_extension;
        $val = $data['es_academico'];
        $_SESSION['es_academico'] = ($val === true || $val === 1 || $val === '1' || $val === 't' || $val === 'true');
        redirigir_login(); // Este manda a perfil.php (Dashboard)
    } else {
        // Intento de inyección o acceso no autorizado
        $_SESSION['auth_error'] = "No tienes acceso a esta dependencia o ha sido desactivada.";
        header('Location: ../public/seleccionar_extension.php');
        exit;
    }
} else {
    header('Location: ../public/seleccionar_extension.php');
    exit;
}
