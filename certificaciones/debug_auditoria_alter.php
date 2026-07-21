<?php
require_once __DIR__ . '/config/model.php';
$db = new DB();
$db->getConn()->exec("ALTER TABLE cursos.auditoria ALTER COLUMN accion TYPE VARCHAR(100)");
echo "Done";
?>
