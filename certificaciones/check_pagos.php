<?php
require 'config/model.php'; 
$db = new DB(); 
$conn = $db->getConn();

// List tables matching pago
$r = $conn->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'cursos' AND table_name LIKE '%pago%'")->fetchAll(PDO::FETCH_ASSOC);
echo "Tables:\n";
print_r($r);

// List columns for pagos
if (count($r) > 0) {
    foreach ($r as $t) {
        $r2 = $conn->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = 'cursos' AND table_name = '" . $t['table_name'] . "'")->fetchAll(PDO::FETCH_ASSOC);
        echo "\nColumns in " . $t['table_name'] . ":\n";
        print_r($r2);
    }
}
