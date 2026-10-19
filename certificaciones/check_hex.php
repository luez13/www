<?php
require 'config/model.php';
$db = new DB();
$pdo = $db->getConn();
$stmt = $pdo->query("SELECT banco, bin2hex(banco) as hex FROM cursos.cuentas_bancarias WHERE banco LIKE 'CAJA%'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
