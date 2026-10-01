<?php
require 'config/model.php';
$db = new DB();
$conn = $db->getConn();
$stmt = $conn->query("SELECT banco, tipo_cuenta, id_extension FROM cursos.cuentas_bancarias");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
