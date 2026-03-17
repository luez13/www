<?php
require 'config/model.php';
try {
    $db = new DB();
    $conn = $db->getConn();
    $stmt = $conn->query("SELECT character_maximum_length, data_type FROM information_schema.columns WHERE table_schema='cursos' AND table_name='modulos' AND column_name='nombre_modulo'");
    print_r($stmt->fetch(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo $e->getMessage();
}
