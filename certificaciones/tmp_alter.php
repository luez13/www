<?php
require 'config/model.php';
try {
    $db = new DB();
    $pdo = $db->getConn();
    $pdo->exec("ALTER TABLE cursos.cursos ADD COLUMN IF NOT EXISTS id_plantilla INT REFERENCES cursos.plantillas_certificados(id) ON DELETE SET NULL");
    echo "OK";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
