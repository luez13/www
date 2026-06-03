<?php
require_once __DIR__ . '/../config/model.php';
$db = new DB();
$conn = $db->getConn();

try {
    $conn->beginTransaction();
    
    // Rename Rol 2 to FACILITADOR
    $stmt = $conn->prepare("UPDATE cursos.roles SET nombre_rol = 'FACILITADOR' WHERE id_rol = 2");
    $stmt->execute();
    
    // Insert Roles 5 and 6 (ignoring duplicates if already run)
    $stmt = $conn->prepare("INSERT INTO cursos.roles (id_rol, nombre_rol) VALUES (5, 'ANALISTA_ADMINISTRATIVO') ON CONFLICT (id_rol) DO NOTHING");
    $stmt->execute();
    
    $stmt = $conn->prepare("INSERT INTO cursos.roles (id_rol, nombre_rol) VALUES (6, 'COORDINADOR_ADMINISTRATIVO') ON CONFLICT (id_rol) DO NOTHING");
    $stmt->execute();
    
    // Also reset sequence for id_rol to avoid issues
    $stmt = $conn->prepare("SELECT setval('cursos.roles_id_rol_seq', (SELECT MAX(id_rol) FROM cursos.roles))");
    $stmt->execute();

    $conn->commit();
    echo "Database updated successfully.\n";
} catch (Exception $e) {
    $conn->rollBack();
    echo "Error updating DB: " . $e->getMessage() . "\n";
}
