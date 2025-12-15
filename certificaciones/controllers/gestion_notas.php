<?php
// controllers/gestion_notas.php
ob_start();

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$response = array('success' => false, 'message' => 'Error desconocido');

try {
    if (!file_exists('../config/model.php')) throw new Exception("Falta config/model.php");
    if (!file_exists('../models/Nota.php')) throw new Exception("Falta models/Nota.php");

    require_once '../config/model.php';
    require_once '../models/Nota.php';

    if (!isset($_SESSION['user_id'])) throw new Exception('No autorizado');

    $db = new DB();
    $notaModel = new Nota($db->getConn());
    
    // Compatibilidad PHP 5
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    switch ($action) {
        // A. Obtener datos para el modal
        case 'obtener_detalle_materia':
            $id_materia = isset($_POST['id_materia']) ? $_POST['id_materia'] : 0;
            $plan = $notaModel->getPlanEvaluacion($id_materia);
            $alumnos = $notaModel->getNotasDetalladas($id_materia);
            $response = array('success' => true, 'plan' => $plan, 'alumnos' => $alumnos);
            break;

        // B. Guardar Plan de Evaluación
        case 'guardar_plan':
            $id_materia = isset($_POST['id_materia']) ? $_POST['id_materia'] : 0;
            $nombres = isset($_POST['nombre_actividad']) ? $_POST['nombre_actividad'] : array();
            $porcentajes = isset($_POST['porcentaje_actividad']) ? $_POST['porcentaje_actividad'] : array();
            
            $actividades = array();
            for($i=0; $i<count($nombres); $i++) {
                if(!empty($nombres[$i])) {
                    $actividades[] = array('nombre' => $nombres[$i], 'porcentaje' => (float)$porcentajes[$i]);
                }
            }
            
            if($notaModel->guardarPlanEvaluacion($id_materia, $actividades)) {
                $response = array('success' => true, 'message' => 'Plan de evaluación guardado.');
            }
            break;

        // C. Guardar las Notas numéricas
        case 'guardar_notas_detalle':
            $notas_raw = isset($_POST['notas']) ? $_POST['notas'] : array();
            $notas_procesadas = array();

            // Procesamiento previo: Convertir NP a 0
            foreach ($notas_raw as $user_id => $actividades) {
                foreach ($actividades as $act_id => $valor) {
                    $valor_limpio = trim(strtoupper($valor));
                    
                    if ($valor_limpio === 'NP') {
                        $notas_procesadas[$user_id][$act_id] = 0;
                    } elseif ($valor_limpio === '') {
                        continue; // Ignorar vacíos
                    } else {
                        $notas_procesadas[$user_id][$act_id] = (float)$valor_limpio;
                    }
                }
            }

            if($notaModel->guardarNotasDetalladas($notas_procesadas)) {
                $response = array('success' => true, 'message' => 'Calificaciones actualizadas.');
            }
            break;

        default: throw new Exception("Acción inválida.");
    }

} catch (Exception $e) {
    $response = array('success' => false, 'message' => $e->getMessage());
}

ob_clean();
echo json_encode($response);
exit;
?>