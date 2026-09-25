<?php
require_once __DIR__ . '/config/model.php';
$db = new DB();
$conn = $db->getConn();
$sql = "ALTER TABLE cursos.materias_bimestre ADD COLUMN IF NOT EXISTS temario TEXT;";
$conn->exec($sql);
echo "OK";
?>
