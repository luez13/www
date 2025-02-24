<?php
include '../config/model.php';

$db = new DB();

// Crear las consultas para obtener la estructura de la base de datos
$query1 = "
    SELECT table_schema, table_name, column_name, data_type
    FROM information_schema.columns
    WHERE table_schema = 'cursos';
";

$query2 = "
    SELECT
        tc.table_schema,
        tc.table_name,
        kcu.column_name,
        ccu.table_schema AS foreign_table_schema,
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
$results1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);

$stmt2 = $db->prepare($query2);
$stmt2->execute();
$results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$stmt3 = $db->prepare($query3);
$stmt3->execute();
$results3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);

// Combinar los resultados en un array
$data = array_merge($results1, $results2, $results3);

// Enviar la información como respuesta JSON
echo json_encode($data);
?>
