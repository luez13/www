<?php
require_once __DIR__ . '/../config/model.php';
$db = new DB();
$conn = $db->getConn();

// 1. Get all tables in the 'cursos' and 'public' schemas
$tables_query = "
    SELECT table_schema, table_name 
    FROM information_schema.tables 
    WHERE table_schema IN ('cursos', 'public') AND table_type = 'BASE TABLE'
    ORDER BY table_schema, table_name;
";
$stmt = $conn->query($tables_query);
$tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "DATABASE STATUS REPORT\n";
echo "======================\n\n";

echo "TABLES AND ROW COUNTS:\n";
echo str_repeat("-", 60) . "\n";
printf("%-40s | %-10s\n", "Table Name", "Row Count");
echo str_repeat("-", 60) . "\n";

foreach ($tables as $table) {
    $schema = $table['table_schema'];
    $name = $table['table_name'];
    $full_name = "$schema.$name";
    
    try {
        $count_query = "SELECT COUNT(*) FROM $full_name";
        $count_stmt = $conn->query($count_query);
        $count = $count_stmt->fetchColumn();
        printf("%-40s | %-10d\n", $full_name, $count);
    } catch (Exception $e) {
        printf("%-40s | %-10s\n", $full_name, "ERROR");
    }
}

echo "\n\nTABLE STRUCTURES (cursos schema):\n";
echo str_repeat("-", 60) . "\n";

foreach ($tables as $table) {
    if ($table['table_schema'] !== 'cursos') continue;
    
    $name = $table['table_name'];
    echo "\nTable: cursos.$name\n";
    
    $columns_query = "
        SELECT column_name, data_type, character_maximum_length, is_nullable, column_default
        FROM information_schema.columns 
        WHERE table_schema = 'cursos' AND table_name = :table_name
        ORDER BY ordinal_position;
    ";
    $col_stmt = $conn->prepare($columns_query);
    $col_stmt->execute(['table_name' => $name]);
    $columns = $col_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        $type = $col['data_type'];
        if ($col['character_maximum_length']) {
            $type .= "(" . $col['character_maximum_length'] . ")";
        }
        $nullable = $col['is_nullable'] === 'YES' ? 'NULL' : 'NOT NULL';
        $default = $col['column_default'] ? "DEFAULT " . $col['column_default'] : "";
        
        printf("  - %-25s %-20s %-10s %s\n", $col['column_name'], $type, $nullable, $default);
    }
}
