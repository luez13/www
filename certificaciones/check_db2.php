<?php
require_once 'config/database.php';
$db = new Database();
$conn = $db->getConn();
$stmt = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'cuentas_bancarias'");
print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
?>
