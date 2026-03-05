<?php
include 'config/model.php';
$db = new DB();
try {
    $stmt = $db->prepare("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = 'cursos' AND table_name = 'usuarios'");
    $stmt->execute();
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "COLUMNS:\n";
    foreach ($cols as $c) {
        echo $c['column_name'] . " - " . $c['data_type'] . "\n";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
?>