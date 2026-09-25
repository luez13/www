<?php
include '../config/model.php';
$db = new DB();
try {
    $stmt = $db->query('SELECT * FROM cursos.extensiones');
    $extensiones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($extensiones);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
