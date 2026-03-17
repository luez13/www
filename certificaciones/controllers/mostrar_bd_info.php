<?php
include '../config/model.php';

try {
    $db = new DB();
} catch (PDOException $e) {
    die(json_encode(array("error" => "Error de conexión a la base de datos: " . $e->getMessage())));
}

// 1. Consulta al catálogo interno para columnas (Tipos, Nulos, Defaults)
$query1 = "
    SELECT
        c.relname AS table_name,
        a.attname AS column_name,
        pg_catalog.format_type(a.atttypid, a.atttypmod) AS data_type,
        NOT a.attnotnull AS is_nullable,
        pg_catalog.pg_get_expr(d.adbin, d.adrelid) AS default_value
    FROM pg_catalog.pg_attribute a
    JOIN pg_catalog.pg_class c ON a.attrelid = c.oid
    JOIN pg_catalog.pg_namespace n ON c.relnamespace = n.oid
    LEFT JOIN pg_catalog.pg_attrdef d ON a.attrelid = d.adrelid AND a.attnum = d.adnum
    WHERE n.nspname = 'cursos'
      AND a.attnum > 0
      AND NOT a.attisdropped
      AND c.relkind = 'r';
";

// 2. Consulta al catálogo interno para TODAS las restricciones (PK, FK, UNIQUE, CHECK)
$query2 = "
    SELECT
        c.relname AS table_name,
        con.conname AS constraint_name,
        con.contype AS constraint_type,
        pg_catalog.pg_get_constraintdef(con.oid, true) AS constraint_definition
    FROM pg_catalog.pg_constraint con
    JOIN pg_catalog.pg_class c ON con.conrelid = c.oid
    JOIN pg_catalog.pg_namespace n ON c.relnamespace = n.oid
    WHERE n.nspname = 'cursos'
      AND c.relkind = 'r';
";

// 3. Consulta al catálogo interno para Triggers
$query3 = "
    SELECT
        c.relname AS table_name,
        t.tgname AS trigger_name,
        pg_catalog.pg_get_triggerdef(t.oid, true) AS definition
    FROM pg_catalog.pg_trigger t
    JOIN pg_catalog.pg_class c ON t.tgrelid = c.oid
    JOIN pg_catalog.pg_namespace n ON c.relnamespace = n.oid
    WHERE n.nspname = 'cursos'
      AND NOT t.tgisinternal;
";

$data = array();

try {
    $stmt1 = $db->prepare($query1);
    $stmt1->execute();
    $columns = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $col) {
        $tableName = $col['table_name'];
        if (!isset($data[$tableName])) {
            $data[$tableName] = array('columns' => array(), 'constraints' => array(), 'triggers' => array());
        }
        $data[$tableName]['columns'][] = array(
            'column_name' => $col['column_name'],
            'data_type' => $col['data_type'],
            'is_nullable' => $col['is_nullable'],
            'default_value' => $col['default_value']
        );
    }

    $stmt2 = $db->prepare($query2);
    $stmt2->execute();
    $constraints = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    $typeMap = array('p' => 'PRIMARY KEY', 'f' => 'FOREIGN KEY', 'u' => 'UNIQUE', 'c' => 'CHECK');

    foreach ($constraints as $con) {
        $tableName = $con['table_name'];
        if (!isset($data[$tableName])) {
            $data[$tableName] = array('columns' => array(), 'constraints' => array(), 'triggers' => array());
        }

        $typeKey = $con['constraint_type'];
        $type = isset($typeMap[$typeKey]) ? $typeMap[$typeKey] : $typeKey;

        $data[$tableName]['constraints'][] = array(
            'name' => $con['constraint_name'],
            'type' => $type,
            'definition' => $con['constraint_definition']
        );
    }

    $stmt3 = $db->prepare($query3);
    $stmt3->execute();
    $triggers = $stmt3->fetchAll(PDO::FETCH_ASSOC);

    foreach ($triggers as $trg) {
        $tableName = $trg['table_name'];
        if (!isset($data[$tableName])) {
            $data[$tableName] = array('columns' => array(), 'constraints' => array(), 'triggers' => array());
        }
        $data[$tableName]['triggers'][] = array(
            'trigger_name' => $trg['trigger_name'],
            'definition' => $trg['definition']
        );
    }

    header('Content-Type: application/json');
    echo json_encode($data);

} catch (PDOException $e) {
    // Si la consulta falla por permisos en pg_catalog, lo enviará con status 200 pero podrás leer el "error" en el body.
    die(json_encode(array("error" => "Error ejecutando consultas SQL: " . $e->getMessage())));
}
?>