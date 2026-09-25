<?php
require 'C:/laragon/www/certificaciones/config/model.php';
$db = new DB();
$pdo = $db->getConn();
$stmt = $pdo->query("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_schema = 'cursos' AND table_name = 'materias_bimestre'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
