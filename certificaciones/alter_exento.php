<?php
require 'config/model.php';
$db = new DB();
$conn = $db->getConn();

try {
    $conn->exec("ALTER TABLE cursos.materias_bimestre ADD COLUMN exento_prelacion BOOLEAN DEFAULT FALSE");
    echo "Column added successfully.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "Column already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
