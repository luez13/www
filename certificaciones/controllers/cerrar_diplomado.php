<?php
// controllers/cerrar_diplomado.php

include 'init.php';
require_once '../config/model.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

$id_curso = isset($_POST['id_curso']) ? (int)$_POST['id_curso'] : 0;
$alumnos = isset($_POST['alumnos']) ? $_POST['alumnos'] : [];

if ($id_curso === 0 || empty($alumnos)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit;
}

$db = new DB();
$conn = $db->getConn();

try {
    $conn->beginTransaction();

    // 1. Actualizar las notas y el estado (aprobado/reprobado) en certificaciones
    $sql_update_cert = "UPDATE cursos.certificaciones 
                        SET nota = :nota, completado = :completado 
                        WHERE curso_id = :id_curso AND id_usuario = :id_usuario";
    $stmt_cert = $conn->prepare($sql_update_cert);

    foreach ($alumnos as $alumno) {
        $id_usuario = (int)$alumno['id_usuario'];
        $nota = (int)$alumno['nota'];
        // Si el estado es APROBADO, completado es true, sino false.
        $completado = ($alumno['estado'] === 'APROBADO') ? 'true' : 'false';

        $stmt_cert->execute([
            ':nota' => $nota,
            ':completado' => $completado,
            ':id_curso' => $id_curso,
            ':id_usuario' => $id_usuario
        ]);
    }

    // 2. Cambiar el estado del curso a Finalizado (estado = false)
    $sql_update_curso = "UPDATE cursos.cursos SET estado = false WHERE id_curso = :id_curso";
    $stmt_curso = $conn->prepare($sql_update_curso);
    $stmt_curso->execute([':id_curso' => $id_curso]);

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Diplomado cerrado correctamente y notas guardadas.']);

}
catch (Exception $e) {
    $conn->rollBack();
    error_log("Error al cerrar diplomado: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ha ocurrido un error en la base de datos al cerrar el diplomado.']);
}
?>
