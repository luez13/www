<?php
// Incluir el archivo de inicialización para la sesión y la configuración
require_once __DIR__ . '/init.php'; 
// Incluir el modelo o la clase de conexión a la base de datos
require_once __DIR__ . '/../config/model.php';

// Establecer la cabecera para devolver respuestas en formato JSON
header('Content-Type: application/json');

// --- Definición de la ruta de subida para las firmas ---
// IMPORTANTE: Asegúrate de que esta carpeta exista y tenga permisos de escritura por el servidor web.
define('UPLOAD_DIR', __DIR__ . '/../public/assets/firmas/');
define('UPLOAD_URL', '../public/assets/firmas/'); // Ruta relativa para el acceso desde el navegador

// Respuesta por defecto
$response = ['success' => false, 'message' => 'Acción no reconocida o no proporcionada.'];

// Determinar la acción a realizar
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;

try {
    $db = new DB(); // Instancia de la conexión a la base de datos

    switch ($action) {
        // --- Acción para LISTAR todos los cargos ---
        case 'listar':
            $stmt = $db->prepare("SELECT * FROM cursos.cargos ORDER BY activo DESC, nombre_cargo, apellido, nombre");
            $stmt->execute();
            $cargos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Asegurar que la ruta de la firma sea una URL accesible por el navegador
            foreach ($cargos as $key => $cargo) {
                if (!empty($cargo['firma_digital'])) {
                    $cargos[$key]['firma_digital'] = UPLOAD_URL . basename($cargo['firma_digital']);
                }
            }

            $response = ['success' => true, 'data' => $cargos];
            break;

        // --- Acción para OBTENER un solo cargo por su ID ---
        case 'obtener':
            if (!isset($_GET['id'])) {
                $response['message'] = 'No se proporcionó un ID de cargo.';
                break;
            }

            $stmt = $db->prepare("SELECT * FROM cursos.cargos WHERE id_cargo = :id");
            $stmt->execute(['id' => $_GET['id']]);
            $cargo = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cargo) {
                $cargo['activo'] = (bool)$cargo['activo'];
                if (!empty($cargo['firma_digital'])) {
                    $cargo['firma_digital'] = UPLOAD_URL . basename($cargo['firma_digital']);
                }
                $response = ['success' => true, 'data' => $cargo];
            } else {
                $response['message'] = 'No se encontró el firmante con el ID proporcionado.';
            }
            break;

        // --- Acción para CREAR un nuevo cargo ---
        case 'crear':
            // Validación de datos requeridos
            if (empty($_POST['nombre']) || empty($_POST['apellido']) || empty($_POST['nombre_cargo'])) {
                $response['message'] = 'Nombre, Apellido y Cargo son campos obligatorios.';
                break;
            }

            $rutaFirma = null;
            // Lógica para subir la imagen de la firma
            if (isset($_FILES['firma_digital']) && $_FILES['firma_digital']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['firma_digital'];
                // Generar un nombre de archivo único para evitar colisiones
                $fileName = uniqid() . '_' . basename($file['name']);
                $uploadPath = UPLOAD_DIR . $fileName;

                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    $rutaFirma = $fileName; // Guardamos solo el nombre del archivo en la BD
                } else {
                    $response['message'] = 'Hubo un error al mover el archivo de la firma.';
                    break;
                }
            }

            $sql = "INSERT INTO cursos.cargos (titulo, nombre, apellido, nombre_cargo, firma_digital, activo) VALUES (:titulo, :nombre, :apellido, :nombre_cargo, :firma_digital, :activo)";
            $stmt = $db->prepare($sql);
            
            $activo = isset($_POST['activo']) ? 1 : 0;

            $stmt->execute(array(
                ':titulo' => isset($_POST['titulo']) ? $_POST['titulo'] : null,
                ':nombre' => $_POST['nombre'],
                ':apellido' => $_POST['apellido'],
                ':nombre_cargo' => $_POST['nombre_cargo'],
                ':firma_digital' => $rutaFirma,
                ':activo' => $activo
            ));

            $response = ['success' => true, 'message' => 'Firmante añadido correctamente.'];
            break;

        // --- Acción para EDITAR un cargo existente ---
        case 'editar':
            if (empty($_POST['id_cargo']) || empty($_POST['nombre']) || empty($_POST['apellido']) || empty($_POST['nombre_cargo'])) {
                $response['message'] = 'Faltan datos obligatorios para la edición (ID, Nombre, Apellido, Cargo).';
                break;
            }
            
            $id_cargo = $_POST['id_cargo'];
            $rutaFirma = null;
            $setFirmaSql = '';

            // Si se sube una nueva firma, procesarla
            if (isset($_FILES['firma_digital']) && $_FILES['firma_digital']['error'] === UPLOAD_ERR_OK) {
                // Primero, opcionalmente borrar la firma antigua del servidor para no acumular archivos
                $stmtOld = $db->prepare("SELECT firma_digital FROM cursos.cargos WHERE id_cargo = :id");
                $stmtOld->execute(['id' => $id_cargo]);
                $oldFileName = $stmtOld->fetchColumn();
                if ($oldFileName && file_exists(UPLOAD_DIR . $oldFileName)) {
                    unlink(UPLOAD_DIR . $oldFileName);
                }

                // Subir el nuevo archivo
                $file = $_FILES['firma_digital'];
                $fileName = uniqid() . '_' . basename($file['name']);
                $uploadPath = UPLOAD_DIR . $fileName;
                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    $rutaFirma = $fileName;
                    $setFirmaSql = ", firma_digital = :firma_digital"; // Añadir al query de actualización
                } else {
                     $response['message'] = 'Hubo un error al subir la nueva firma.';
                     break; // Detener si falla la subida
                }
            }

            $sql = "UPDATE cursos.cargos SET titulo = :titulo, nombre = :nombre, apellido = :apellido, nombre_cargo = :nombre_cargo, activo = :activo {$setFirmaSql} WHERE id_cargo = :id_cargo";
            $stmt = $db->prepare($sql);

            $params = array(
                ':titulo' => isset($_POST['titulo']) ? $_POST['titulo'] : null,
                ':nombre' => $_POST['nombre'],
                ':apellido' => $_POST['apellido'],
                ':nombre_cargo' => $_POST['nombre_cargo'],
                ':activo' => isset($_POST['activo']) ? 1 : 0,
                ':id_cargo' => $id_cargo
            );

            // Añadir el parámetro de la firma solo si se va a actualizar
            if (!empty($setFirmaSql)) {
                $params[':firma_digital'] = $rutaFirma;
            }

            $stmt->execute($params);

            $response = ['success' => true, 'message' => 'Firmante actualizado correctamente.'];
            break;

        // --- Acción para CAMBIAR EL ESTADO (activo/inactivo) ---
        case 'cambiar_estado':
            if (!isset($_POST['id_cargo']) || !isset($_POST['estado'])) {
                $response['message'] = 'Faltan datos para cambiar el estado.';
                break;
            }

            $sql = "UPDATE cursos.cargos SET activo = :activo WHERE id_cargo = :id_cargo";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':activo' => (int)$_POST['estado'],
                ':id_cargo' => $_POST['id_cargo']
            ]);
            
            $accionTexto = $_POST['estado'] ? 'activado' : 'desactivado';
            $response = ['success' => true, 'message' => "Firmante {$accionTexto} correctamente."];
            break;

                // --- NUEVA ACCIÓN para LISTAR solo los cargos ACTIVOS (para los selectores) ---
        case 'listar_activos':
            // Esta acción es específicamente para poblar los menús desplegables en el formulario de configuración.
            // Solo devuelve los firmantes que están marcados como 'activo = true'.
            $stmt = $db->prepare("SELECT id_cargo, titulo, nombre, apellido, nombre_cargo FROM cursos.cargos WHERE activo = TRUE ORDER BY nombre_cargo, apellido, nombre");
            $stmt->execute();
            $cargos_activos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Construimos un array más limpio para el frontend
            $firmantes_disponibles = [];
            foreach ($cargos_activos as $cargo) {
                $firmantes_disponibles[] = [
                    'id' => $cargo['id_cargo'],
                    'texto_display' => "{$cargo['nombre_cargo']} - {$cargo['titulo']} {$cargo['nombre']} {$cargo['apellido']}"
                ];
            }

            $response = ['success' => true, 'data' => $firmantes_disponibles];
            break;
    }
} catch (PDOException $e) {
    // Capturar errores de la base de datos
    $response['message'] = 'Error de base de datos: ' . $e->getMessage();
} catch (Exception $e) {
    // Capturar otros errores generales
    $response['message'] = 'Error en el servidor: ' . $e->getMessage();
}

// Enviar la respuesta final en formato JSON
echo json_encode($response);
?>