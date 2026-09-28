<?php
require 'config/model.php'; 
$db = new DB(); 
$conn = $db->getConn(); 
$stmt = $conn->query("SELECT constraint_name FROM information_schema.key_column_usage WHERE table_name = 'cursos_config_firmas' AND column_name = 'id_cargo_firmante'"); 
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
