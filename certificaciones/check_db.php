<?php
require 'config/model.php';
$db = new DB();
$pdo = $db->getConn();
$stmt = $pdo->query('SELECT id_cuenta, banco, titular, activo, id_extension FROM cursos.cuentas_bancarias');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));