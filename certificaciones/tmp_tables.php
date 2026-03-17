<?php
require 'config/model.php';
try {
    $db = new DB();
    $conn = $db->getConn();

    $stmt = $conn->query("SELECT table_schema, table_name FROM information_schema.tables WHERE table_schema NOT IN ('information_schema', 'pg_catalog')");
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($tables as $t) {
        echo $t['table_schema'] . "." . $t['table_name'] . "\n";
    }

} catch (Exception $e) {
    echo "Err: " . $e->getMessage() . "\n";
}
