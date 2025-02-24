<?php
include '../config/model.php';
include '../models/curso.php';

$db = new DB();
$curso = new Curso($db);

$id_curso = $_POST['id_curso'];
$id_usuario = $_POST['id_usuario'];

if (isset($_POST['completado'])) {
    $completado = $_POST['completado'];
    $resultado = $curso->actualizar_completado($id_curso, $id_usuario, $completado);

    if ($resultado) {
        echo "El estado del curso se actualizó correctamente.";
    } else {
        echo "Hubo un error al actualizar el estado del curso.";
    }
}

if (isset($_POST['pagado'])) {
    $pagado = $_POST['pagado'];
    $curso->actualizar_pagado($id_curso, $id_usuario, $pagado);
}

if (isset($_POST['tomo']) && isset($_POST['folio'])) {
    $tomo = $_POST['tomo'];
    $folio = $_POST['folio'];
    $curso->actualizar_tomo_folio($id_curso, $id_usuario, $tomo, $folio);
}

echo json_encode(['status' => 'success']);
?>