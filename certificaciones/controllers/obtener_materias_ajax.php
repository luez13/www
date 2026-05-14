<?php
// controllers/obtener_materias_ajax.php
require_once '../config/model.php';
require_once '../models/Materia.php';

header('Content-Type: application/json');

if (!isset($_GET['id_curso'])) {
    echo json_encode([]);
    exit;
}

$db = new DB();
$materiaModel = new Materia($db);
$id_curso = (int)$_GET['id_curso'];

$materias = $materiaModel->getMateriasByCurso($id_curso);

$resultado = [];
foreach ($materias as $m) {
    $resultado[] = [
        'id' => $m['id_materia_bimestre'],
        'nombre' => $m['nombre_materia']
    ];
}

echo json_encode($resultado);
