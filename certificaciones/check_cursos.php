<?php
require 'C:/laragon/www/certificaciones/config/model.php';
$db = new DB();
$pdo = $db->getConn();
$stmt = $pdo->query("SELECT id_curso, nombre_curso, tipo_curso FROM cursos.cursos");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
