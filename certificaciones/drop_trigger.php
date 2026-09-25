<?php
require_once __DIR__ . '/config/model.php';
try {
    $db = new DB();
    $conn = $db->getConn();
    $conn->exec("DROP TRIGGER IF EXISTS trg_asignar_extension_default ON cursos.usuarios;");
    $conn->exec("DROP FUNCTION IF EXISTS cursos.asignar_extension_default();");
    echo "Trigger and function removed.";
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
