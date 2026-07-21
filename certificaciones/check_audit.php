<?php
require_once __DIR__ . '/config/model.php';
require_once __DIR__ . '/models/Nota.php';

$db = new DB();
$pdo = $db->getConn();

$notaModel = new Nota($pdo);
$alumnos = $notaModel->getNotasDetalladas(1);
print_r($alumnos);
?>
