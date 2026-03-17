<?php
include 'config/model.php';
$db = new DB();

try {
    $stmt = $db->prepare("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'cursos';
    ");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($columns, JSON_PRETTY_PRINT);
    file_put_contents('tmp_columns_output.json', json_encode($columns, JSON_PRETTY_PRINT));
    
    // Check if table buzon_sugerencias even exists
    if(count($columns) == 0){
        echo "Table might not exist or schema is different.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
