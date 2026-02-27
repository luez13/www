<?php
require 'config/model.php';
$db = new DB();
$stmt = $db->prepare("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema='cursos' AND table_name='certificaciones'");
$stmt->execute();
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($cols);
