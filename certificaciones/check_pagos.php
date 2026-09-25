<?php
require 'C:/laragon/www/certificaciones/config/model.php';
$db = new DB();
$pdo = $db->getConn();
$stmt = $pdo->query("SELECT * FROM cursos.comprobantes_pago LIMIT 2");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
