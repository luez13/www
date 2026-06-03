<?php
require_once __DIR__ . '/../config/model.php';
$db = new DB();
$conn = $db->getConn();
$stmt = $conn->query('SELECT * FROM cursos.roles ORDER BY id_rol');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
