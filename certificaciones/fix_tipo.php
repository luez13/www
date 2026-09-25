<?php
require 'C:/laragon/www/certificaciones/config/model.php';
$db = new DB();
$pdo = $db->getConn();
$pdo->exec("UPDATE cursos.cursos SET tipo_curso = 'recepcion_pago' WHERE nombre_curso LIKE '%aranceles%' OR nombre_curso LIKE '%recepcion%'");
echo 'OK';
