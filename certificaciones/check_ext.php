<?php
require_once __DIR__ . '/config/model.php';
try {
    $db = new DB();
    $conn = $db->getConn();
    $stmt = $conn->query("
        SELECT u.id, u.correo, u.id_rol, ue.id_extension 
        FROM cursos.usuarios u 
        LEFT JOIN cursos.usuarios_extensiones ue ON u.id = ue.id_usuario
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($users);
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
