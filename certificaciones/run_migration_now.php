<?php
require 'config/model.php';
try {
    $db = new DB();
    $conn = $db->getConn();

    $conn->exec("ALTER TABLE cursos.plantillas_certificados DROP COLUMN IF EXISTS configuracion_json CASCADE");
    $conn->exec("ALTER TABLE cursos.plantillas_certificados DROP COLUMN IF EXISTS coordenadas CASCADE");
    $conn->exec("ALTER TABLE cursos.plantillas_certificados ADD COLUMN IF NOT EXISTS archivo_vista VARCHAR(255) DEFAULT 'certificado_base.php'");

    echo "Migration OK\n";
} catch (Exception $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
}
