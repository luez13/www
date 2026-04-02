<?php
require_once 'c:\laragon\www\certificaciones\config\model.php';
$db = new DB();
$conn = $db->getConn();
$stmt = $conn->query("SELECT * FROM cursos.posiciones_firma ORDER BY id_posicion");
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($res, JSON_PRETTY_PRINT);
?>
