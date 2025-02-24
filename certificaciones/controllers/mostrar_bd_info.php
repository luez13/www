<?php
include '../config/model.php';

try {
    $db = new DB();
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// Consulta para obtener las columnas de las tablas
$query1 = "
    SELECT table_name, column_name, data_type
    FROM information_schema.columns
    WHERE table_schema = 'cursos';
";

// Consulta para obtener las claves foráneas
$query2 = "
    SELECT
        tc.table_name,
        kcu.column_name,
        ccu.table_name AS foreign_table_name,
        ccu.column_name AS foreign_column_name
    FROM
        information_schema.table_constraints AS tc
        JOIN information_schema.key_column_usage AS kcu
        ON tc.constraint_name = kcu.constraint_name
        AND tc.table_schema = kcu.table_schema
        JOIN information_schema.constraint_column_usage AS ccu
        ON ccu.constraint_name = tc.constraint_name
        AND ccu.table_schema = tc.table_schema
    WHERE tc.constraint_type = 'FOREIGN KEY' AND tc.table_schema = 'cursos';
";

// Consulta para obtener los triggers
$query3 = "
    SELECT
        event_object_table AS table_name,
        trigger_name,
        event_manipulation AS event,
        action_statement AS definition
    FROM
        information_schema.triggers
    WHERE
        event_object_schema = 'cursos';
";

// Ejecutar las consultas y obtener los resultados
$stmt1 = $db->prepare($query1);
$stmt1->execute();
$columns = $stmt1->fetchAll(PDO::FETCH_ASSOC);

$stmt2 = $db->prepare($query2);
$stmt2->execute();
$foreignKeys = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$stmt3 = $db->prepare($query3);
$stmt3->execute();
$triggers = $stmt3->fetchAll(PDO::FETCH_ASSOC);

// Organizar los datos en un array estructurado
$data = [];

// Agregar las columnas de las tablas
foreach ($columns as $column) {
    $tableName = $column['table_name'];
    if (!isset($data[$tableName])) {
        $data[$tableName] = [
            'columns' => [],
            'foreign_keys' => [],
            'triggers' => []
        ];
    }
    $data[$tableName]['columns'][] = [
        'column_name' => $column['column_name'],
        'data_type' => $column['data_type']
    ];
}

// Agregar las claves foráneas
foreach ($foreignKeys as $foreignKey) {
    $tableName = $foreignKey['table_name'];
    if (!isset($data[$tableName])) {
        $data[$tableName] = [
            'columns' => [],
            'foreign_keys' => [],
            'triggers' => []
        ];
    }
    $data[$tableName]['foreign_keys'][] = [
        'column_name' => $foreignKey['column_name'],
        'foreign_table_name' => $foreignKey['foreign_table_name'],
        'foreign_column_name' => $foreignKey['foreign_column_name']
    ];
}

// Agregar los triggers
foreach ($triggers as $trigger) {
    $tableName = $trigger['table_name'];
    if (!isset($data[$tableName])) {
        $data[$tableName] = [
            'columns' => [],
            'foreign_keys' => [],
            'triggers' => []
        ];
    }
    $data[$tableName]['triggers'][] = [
        'trigger_name' => $trigger['trigger_name'],
        'event' => $trigger['event'],
        'definition' => $trigger['definition']
    ];
}

// Enviar la información como respuesta JSON
header('Content-Type: application/json');
echo json_encode($data, JSON_PRETTY_PRINT);