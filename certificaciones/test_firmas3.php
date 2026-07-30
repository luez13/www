<?php
require_once __DIR__ . '/config/model.php';
require_once __DIR__ . '/models/curso.php';

$db = new DB();
$cursoModel = new Curso($db);

$conn = $db->getConn();
$stmt = $conn->query("SELECT id_curso FROM cursos.cursos ORDER BY id_curso DESC LIMIT 1");
$id_curso = $stmt->fetchColumn();

if ($id_curso) {
    echo "ID Curso: " . $id_curso . "\n";
    $firmantes = $cursoModel->obtenerFirmasCurso($id_curso);
    echo json_encode($firmantes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} else {
    echo "No courses found.\n";
}
