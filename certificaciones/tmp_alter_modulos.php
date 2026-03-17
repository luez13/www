<?php
require 'config/model.php';
try {
    $db = new DB();
    $conn = $db->getConn();
    $conn->exec("ALTER TABLE cursos.modulos ALTER COLUMN nombre_modulo TYPE TEXT;");
    echo "Migration OK: Modulos nombre now TEXT";
} catch (Exception $e) {
    echo $e->getMessage();
}
