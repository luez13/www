<?php
require 'config/model.php';
$db = new DB();
$conn = $db->getConn();
$stmt = $conn->query("SELECT id_materia_bimestre, nombre_materia, lapso_academico, exento_prelacion FROM cursos.materias_bimestre ORDER BY id_materia_bimestre DESC LIMIT 10");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
