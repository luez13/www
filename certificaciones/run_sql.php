<?php
require 'config/model.php';
$db = new DB();
$conn = $db->getConn();

$sql = "
ALTER TABLE cursos.materias_bimestre 
ADD COLUMN IF NOT EXISTS fecha_inicio DATE,
ADD COLUMN IF NOT EXISTS fecha_fin DATE;
";

try {
    $conn->exec($sql);
    echo "SQL Executed successfully";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
