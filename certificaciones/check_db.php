<?php
require_once 'config/model.php';
$db = new DB();
$conn = $db->getConn();

try {
    $stmt = $conn->query("
        SELECT column_name, data_type, is_nullable, column_default 
        FROM information_schema.columns 
        WHERE table_schema = 'cursos' AND table_name = 'comprobantes_pago'
    ");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($columns);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>