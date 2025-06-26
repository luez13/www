<?php
// Incluir el archivo de inicialización para la sesión y la configuración
require_once __DIR__ . '/init.php';
// Incluir el modelo o la clase de conexión a la base de datos
require_once __DIR__ . '/../config/model.php';

// Establecer la cabecera para devolver respuestas en formato JSON
header('Content-Type: application/json');

// Respuesta por defecto
$response = ['success' => false, 'message' => 'Acción no reconocida.'];
// Determinar la acción a realizar (desde GET o POST)
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;

// Solo el rol 4 (Admin) puede ejecutar estas acciones
if ($_SESSION['id_rol'] != 4) {
    $response['message'] = 'Acceso denegado.';
    echo json_encode($response);
    exit;
}

try {
    $db = new DB();

    switch ($action) {
        
        // --- Acción para OBTENER el valor de una clave de configuración ---
        case 'obtener_config_clave':
            if (empty($_GET['clave'])) {
                throw new Exception('No se especificó la clave de configuración.');
            }
            $stmt = $db->prepare("SELECT valor_config FROM cursos.config_sistema WHERE clave_config = :clave");
            $stmt->execute([':clave' => $_GET['clave']]);
            $config = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($config) {
                $response = ['success' => true, 'data' => $config];
            } else {
                // No es un error si la clave aún no existe, simplemente no hay valor guardado.
                $response = ['success' => true, 'data' => null, 'message' => 'Configuración no encontrada, se usará el valor por defecto.'];
            }
            break;

        // --- Acción para GUARDAR o ACTUALIZAR una clave de configuración ---
        case 'guardar_config':
            if (empty($_POST['clave_config']) || !isset($_POST['valor_config'])) {
                throw new Exception('Faltan datos para guardar la configuración.');
            }
            
            $clave = $_POST['clave_config'];
            $valor = $_POST['valor_config'];

            // Usamos la sintaxis "UPSERT" de PostgreSQL. Es muy eficiente.
            // Si la 'clave_config' ya existe, actualiza el 'valor_config'.
            // Si no existe, inserta una nueva fila.
            $sql = "INSERT INTO cursos.config_sistema (clave_config, valor_config, fecha_modificacion)
                    VALUES (:clave, :valor, CURRENT_TIMESTAMP)
                    ON CONFLICT (clave_config) 
                    DO UPDATE SET 
                        valor_config = EXCLUDED.valor_config,
                        fecha_modificacion = CURRENT_TIMESTAMP";

            $stmt = $db->prepare($sql);
            $stmt->execute([':clave' => $clave, ':valor' => $valor]);

            // rowCount() > 0 indica que se hizo una inserción o una actualización real.
            // Si el valor no cambió, rowCount() podría ser 0, pero la operación fue exitosa.
            $response = ['success' => true, 'message' => 'Configuración guardada exitosamente.'];
            
            break;

        // Caso por defecto si la acción no coincide
        default:
             $response['message'] = "La acción '{$action}' no es válida.";
             break;
    }
} catch (Exception $e) {
    // Capturar cualquier error para devolver una respuesta JSON limpia
    $response['message'] = 'Error en el servidor: ' . $e->getMessage();
}

// Enviar la respuesta final en formato JSON
echo json_encode($response);
?>