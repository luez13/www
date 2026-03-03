<?php
require_once __DIR__ . '/config/model.php';

try {
    $db = new DB();

    // 1. Añadir columna imagen_portada a cursos.cursos si no existe
    $sql1 = "ALTER TABLE cursos.cursos ADD COLUMN IF NOT EXISTS imagen_portada VARCHAR(255) NULL;";
    $stmt1 = $db->prepare($sql1);
    $stmt1->execute();
    echo "Columna imagen_portada anadida.\n";

    // 2. Crear tabla cursos.landing_carrusel
    $sql2 = "CREATE TABLE IF NOT EXISTS cursos.landing_carrusel (
        id_carrusel SERIAL PRIMARY KEY,
        ruta_imagen VARCHAR(255) NOT NULL,
        titulo VARCHAR(150),
        descripcion TEXT,
        activo BOOLEAN DEFAULT true,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";
    $stmt2 = $db->prepare($sql2);
    $stmt2->execute();
    echo "Tabla landing_carrusel creada.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>