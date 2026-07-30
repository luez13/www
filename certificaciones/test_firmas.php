<?php
require_once __DIR__ . '/controllers/init.php';
require_once __DIR__ . '/config/model.php';
require_once __DIR__ . '/models/curso.php';

$db = new DB();
$cursoModel = new Curso($db);

// We need a course ID. Let's just find one that has firmas.
$conn = $db->getConn();
$stmt = $conn->query("SELECT DISTINCT id_curso FROM cursos.cursos_config_firmas LIMIT 1");
$id_curso = $stmt->fetchColumn();

if ($id_curso) {
    echo "Course ID: $id_curso\n";
    $firmantes = $cursoModel->obtenerFirmasCurso($id_curso);
    print_r($firmantes);
} else {
    echo "No firmas configured for any course.\n";
}
