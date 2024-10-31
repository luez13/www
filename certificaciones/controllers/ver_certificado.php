<?php
require_once '../config/model.php';
require_once '../models/curso.php';

$db = new DB();
$curso = new Curso($db);

if (isset($_GET['valor_unico'])) {
    $valor_unico = $_GET['valor_unico'];
    $datos = $curso->obtener_datos_certificacion($valor_unico);
    
    header('Content-Type: application/json');
    echo json_encode($datos);
}
?>