<?php
require_once '../config/model.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['id_curso']) || !is_numeric($_GET['id_curso'])) {
    echo json_encode(['error' => 'ID de curso inválido']);
    exit;
}

$id_curso = (int)$_GET['id_curso'];

try {
    $db = new DB();
    $pdo = $db->getConn();

    // Obtener detalles del curso público
    $stmtC = $pdo->prepare("SELECT c.nombre_curso, c.descripcion, c.tipo_curso, c.imagen_portada, c.id_extension, e.es_academico FROM cursos.cursos c JOIN cursos.extensiones e ON c.id_extension = e.id_extension WHERE c.id_curso = :id_curso AND c.estado = true");
    $stmtC->execute([':id_curso' => $id_curso]);
    $curso = $stmtC->fetch(PDO::FETCH_ASSOC);

    if (!$curso) {
        echo json_encode(['error' => 'Curso no encontrado o inactivo']);
        exit;
    }

    // Obtener módulos
    $stmtM = $pdo->prepare("SELECT numero, nombre_modulo, contenido FROM cursos.modulos WHERE id_curso = :id_curso ORDER BY numero ASC");
    $stmtM->execute([':id_curso' => $id_curso]);
    $modulos = $stmtM->fetchAll(PDO::FETCH_ASSOC);

    $curso['modulos'] = $modulos;

    echo json_encode($curso);

} catch (Exception $e) {
    echo json_encode(['error' => 'Error del servidor al obtener datos']);
    error_log("Error en api_curso_detalles: " . $e->getMessage());
}
