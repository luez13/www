<?php
require_once '../config/model.php';
require_once '../models/curso.php';

header('Content-Type: application/json');

$db = new DB();
$curso = new Curso($db);

if (isset($_GET['valor_unico'])) {
    $valor_unico = $_GET['valor_unico'];
    $datos = $curso->obtener_datos_certificacion($valor_unico);

    if ($datos) {
        echo json_encode($datos);
    } else {
        echo json_encode(['error' => 'No se encontraron datos para el valor único proporcionado.']);
    }
} else {
    echo json_encode(['error' => 'Valor único no proporcionado.']);
}
?>