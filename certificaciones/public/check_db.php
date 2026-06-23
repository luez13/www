<?php
require 'c:/laragon/www/certificaciones/config/model.php';
$db = new DB();
$pdo = $db->getConn();
$stmt = $pdo->query("SELECT table_name, column_name, data_type FROM information_schema.columns WHERE table_schema = 'cursos' AND table_name IN ('cursos', 'certificaciones', 'notas_participante') ORDER BY table_name, ordinal_position;");
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($res, JSON_PRETTY_PRINT);
