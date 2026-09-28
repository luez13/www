<?php
require 'config/model.php'; 
$db = new DB(); 
$conn = $db->getConn(); 
$stmt = $conn->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'cursos_config_firmas'"); 
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
