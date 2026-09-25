<?php
require 'config/model.php';
$db = new DB();
$conn = $db->getConn();

$schema = 'cursos';

$tables_stmt = $conn->query("SELECT table_name FROM information_schema.tables WHERE table_schema = '$schema' ORDER BY table_name");
$tables = $tables_stmt->fetchAll(PDO::FETCH_COLUMN);

$ddl = "";

foreach ($tables as $table) {
    $ddl .= "-- Table: $schema.$table\n";
    $ddl .= "CREATE TABLE $schema.$table (\n";
    
    $cols_stmt = $conn->query("
        SELECT column_name, data_type, character_maximum_length, is_nullable, column_default
        FROM information_schema.columns
        WHERE table_schema = '$schema' AND table_name = '$table'
        ORDER BY ordinal_position
    ");
    $columns = $cols_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $col_defs = [];
    foreach ($columns as $c) {
        $def = "    " . $c['column_name'] . " " . $c['data_type'];
        if ($c['character_maximum_length']) {
            $def .= "(" . $c['character_maximum_length'] . ")";
        }
        if ($c['is_nullable'] === 'NO') {
            $def .= " NOT NULL";
        }
        if ($c['column_default'] !== null) {
            $def .= " DEFAULT " . $c['column_default'];
        }
        $col_defs[] = $def;
    }
    
    $ddl .= implode(",\n", $col_defs) . "\n);\n\n";
}

file_put_contents('schema_dump.sql', $ddl);
echo "Schema dumped to schema_dump.sql";
