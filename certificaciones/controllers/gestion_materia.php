<?php
// controllers/gestion_materia.php

// 1. LIMPIEZA DE BUFFER (Vital para evitar errores silenciosos)
ob_start();

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Definimos que la respuesta será JSON
header('Content-Type: application/json');

$response = array('success' => false, 'message' => 'Error desconocido');

try {
    // Verificar archivos antes de incluir
    if (!file_exists('../config/model.php')) throw new Exception("Falta config/model.php");
    if (!file_exists('../models/Materia.php')) throw new Exception("Falta models/Materia.php");

    require_once '../config/model.php';
    require_once '../models/Materia.php';

    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Sesión expirada. Recargue la página.');
    }

    $db = new DB();
    $materiaModel = new Materia($db->getConn());

    // Compatibilidad total (PHP 5 y 8)
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    switch ($action) {
        case 'guardar':
            // Validaciones
            if (empty($_POST['nombre_materia'])) throw new Exception("El nombre es obligatorio.");
            if (empty($_POST['docente_id'])) throw new Exception("Debe seleccionar un docente.");

            $datos = array(
                'id_materia_bimestre' => isset($_POST['id_materia']) ? $_POST['id_materia'] : 0,
                'id_curso'            => isset($_POST['id_curso']) ? $_POST['id_curso'] : 0,
                'nombre_materia'      => $_POST['nombre_materia'],
                'duracion_bimestres'  => isset($_POST['duracion_bimestres']) ? $_POST['duracion_bimestres'] : '',
                'total_horas'         => isset($_POST['total_horas']) ? $_POST['total_horas'] : 0,
                'modalidad'           => isset($_POST['modalidad']) ? $_POST['modalidad'] : 'Virtual',
                'docente_id'          => $_POST['docente_id'],
                
                // AGREGA ESTA LÍNEA EXACTA AQUÍ:
                'lapso_academico'     => isset($_POST['lapso_academico']) ? $_POST['lapso_academico'] : 1
            );

            if ($materiaModel->saveMateria($datos)) {
                $response = array('success' => true, 'message' => 'Materia guardada correctamente.');
            } else {
                throw new Exception("Error SQL al intentar guardar.");
            }
            break;

        case 'obtener':
            $id = isset($_POST['id_materia']) ? (int)$_POST['id_materia'] : 0;
            $data = $materiaModel->getMateriaById($id);
            if ($data) {
                $response = array('success' => true, 'data' => $data);
            } else {
                throw new Exception("Materia no encontrada en BD.");
            }
            break;

        case 'eliminar':
            $id = isset($_POST['id_materia']) ? (int)$_POST['id_materia'] : 0;
            if ($materiaModel->deleteMateria($id)) {
                $response = array('success' => true, 'message' => 'Materia eliminada.');
            } else {
                throw new Exception("Error al eliminar.");
            }
            break;

        default:
            throw new Exception("Acción no válida: " . $action);
    }

} catch (Exception $e) {
    $response = array('success' => false, 'message' => $e->getMessage());
}

// Limpiamos cualquier salida anterior (HTML, Warnings, espacios)
ob_clean();
// Enviamos el JSON limpio
echo json_encode($response);
exit;
?>