<?php
include '../config/model.php';
include '../models/curso.php';

$db = new DB();
$curso = new Curso($db);

$id_curso = $_POST['id_curso'];
$id_usuario = $_POST['id_usuario'];

$response = [];

if (isset($_POST['completado'])) {
    $completado = $_POST['completado'];
    $resultado = $curso->actualizar_completado($id_curso, $id_usuario, $completado);
    if ($resultado) {
        $response['completado'] = "El estado de completado se actualizó correctamente.";
    } else {
        $response['completado'] = "Hubo un error al actualizar el estado de completado.";
    }
}

if (isset($_POST['pagado'])) {
    $pagado = $_POST['pagado'];
    $resultado = $curso->actualizar_pagado($id_curso, $id_usuario, $pagado);
    if ($resultado) {
        $response['pagado'] = "El estado de pago se actualizó correctamente.";
    } else {
        $response['pagado'] = "Hubo un error al actualizar el estado de pago.";
    }
}

if (isset($_POST['tomo']) && isset($_POST['folio'])) {
    $tomo = $_POST['tomo'];
    $folio = $_POST['folio'];
    $resultado = $curso->actualizar_tomo_folio($id_curso, $id_usuario, $tomo, $folio);
    if ($resultado) {
        $response['tomo_folio'] = "El tomo y folio se actualizaron correctamente.";
    } else {
        $response['tomo_folio'] = "Hubo un error al actualizar el tomo y folio.";
    }
}

echo json_encode(['status' => 'success', 'response' => $response]);
?>
