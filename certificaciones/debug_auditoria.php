<?php
require_once __DIR__ . '/config/model.php';
$db = new DB();
$stmt = $db->getConn()->query("SELECT column_name, data_type, character_maximum_length FROM information_schema.columns WHERE table_schema = 'cursos' AND table_name = 'auditoria'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
