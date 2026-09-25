<?php
require 'C:/laragon/www/certificaciones/config/model.php';
$db = new DB();
$pdo = $db->getConn();
$stmt = $pdo->query("SELECT column_name, data_type, udt_name FROM information_schema.columns WHERE table_schema = 'cursos' AND table_name = 'cursos'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
