<?php
require_once __DIR__ . '/config/model.php';
$db = new DB();
$stmt = $db->getConn()->query("SELECT table_name, column_name FROM information_schema.columns WHERE table_schema = 'cursos' AND table_name IN ('usuarios', 'materias_bimestre', 'cursos', 'autoridades') ORDER BY table_name, ordinal_position");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
