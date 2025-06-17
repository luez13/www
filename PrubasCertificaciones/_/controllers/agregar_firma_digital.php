<?php
include '../config/model.php';

try {
    // Crear una instancia de la clase DB
    $db = new DB();

    // Otorgar permisos al usuario
    $grantQuery = "GRANT ALL PRIVILEGES ON TABLE cursos.cursos TO uptaivir_certificacion2;";
    $db->prepare($grantQuery)->execute();

    // Agregar la columna firma_digital
    $alterQuery = "ALTER TABLE cursos.cursos ADD COLUMN firma_digital BOOLEAN DEFAULT FALSE;";
    $db->prepare($alterQuery)->execute();

    // Enviar una respuesta JSON indicando éxito
    echo json_encode([
        'success' => true,
        'message' => 'Columna firma_digital agregada correctamente.'
    ]);
} catch (PDOException $e) {
    // Enviar una respuesta JSON indicando error
    echo json_encode([
        'success' => false,
        'message' => 'Error al agregar la columna: ' . $e->getMessage()
    ]);
}
?>