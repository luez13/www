<?php
require_once __DIR__ . '/config/model.php';
$db = new DB();
$stmt = $db->getConn()->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'cursos'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
