<?php
include '../config/model.php';
include '../models/curso.php';

$db = new DB();
$curso = new Curso($db);

$id_curso = $_POST['id_curso'];
$id_usuario = $_POST['id_usuario'];
$completado = $_POST['completado'];

$resultado = $curso->actualizar_completado($id_curso, $id_usuario, $completado);

if ($resultado) {
    echo "El estado del curso se actualizó correctamente.";
} else {
    echo "Hubo un error al actualizar el estado del curso.";
}

if (isset($_POST['completado'])) {
    $completado = $_POST['completado'];
    $curso->actualizar_completado($id_curso, $id_usuario, $completado);
}

if (isset($_POST['pagado'])) {
    $pagado = $_POST['pagado'];
    $curso->actualizar_pagado($id_curso, $id_usuario, $pagado);
}

echo json_encode(['status' => 'success']);
?>

