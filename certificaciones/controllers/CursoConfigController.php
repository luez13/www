<?php
// ../controllers/CursoConfigController.php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/../config/model.php';

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? null;
$response = ['success' => false, 'message' => 'Acción no reconocida.'];

// Solo usuarios con permisos pueden acceder
// if (!(esPerfil3($_SESSION['user_id']) || esPerfil4($_SESSION['user_id']))) {
//     $response['message'] = 'Acceso denegado.';
//     echo json_encode($response);
//     exit;
// }

try {
    $db = new DB();

    switch ($action) {
        case 'obtener_config':
            // Lógica para obtener la configuración guardada para un id_curso
            // (La implementaremos en el siguiente paso)
            $id_curso = $_GET['id_curso'] ?? 0;
            $sql = "SELECT p.codigo_posicion, cc.id_cargo_firmante, cc.usar_promotor_curso 
                    FROM cursos.cursos_config_firmas cc
                    JOIN cursos.posiciones_firma p ON cc.id_posicion = p.id_posicion
                    WHERE cc.id_curso = :id_curso";
            $stmt = $db->prepare($sql);
            $stmt->execute([':id_curso' => $id_curso]);
            $config = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response = ['success' => true, 'data' => $config];
            break;

        case 'guardar_config':
            // Lógica para borrar la configuración vieja y guardar la nueva
            // (La implementaremos en el siguiente paso)
            $response = ['success' => true, 'message' => 'Configuración guardada (Lógica pendiente)'];
            break;
    }
} catch (PDOException $e) {
    $response['message'] = 'Error de base de datos: ' . $e->getMessage();
}

echo json_encode($response);
?>