<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/../config/model.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Acción no reconocida.'];
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;

// Solo el rol 4 (Admin) puede ejecutar la mayoría de estas acciones
// Se exceptúan visualizaciones públicas si hiciéramos endpoints públicos aquí
if ($_SESSION['id_rol'] != 4 && $action !== 'listar_carrusel_publico' && $action !== 'listar_cursos_landing') {
    $response['message'] = 'Acceso denegado.';
    echo json_encode($response);
    exit;
}

try {
    $db = new DB();

    switch ($action) {

        // --- Carrusel ---
        case 'listar_carrusel':
            $stmt = $db->prepare("SELECT * FROM cursos.landing_carrusel ORDER BY id_carrusel ASC");
            $stmt->execute();
            $response = ['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
            break;

        case 'subir_carrusel':
            if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Error al subir la imagen.');
            }

            $titulo = isset($_POST['titulo']) ? $_POST['titulo'] : '';
            $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';

            // Directorio temporal
            $file = $_FILES['imagen'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $nombre_archivo = 'carousel_' . time() . '_' . uniqid() . '.' . $ext;
            $upload_dir = __DIR__ . '/../public/assets/img/carousel/';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $ruta_destino = $upload_dir . $nombre_archivo;

            if (move_uploaded_file($file['tmp_name'], $ruta_destino)) {
                // Guardar ruta relativa
                $ruta_bd = '../public/assets/img/carousel/' . $nombre_archivo;

                $sql = "INSERT INTO cursos.landing_carrusel (ruta_imagen, titulo, descripcion) VALUES (:ruta, :titulo, :descripcion)";
                $stmt = $db->prepare($sql);
                $stmt->execute([':ruta' => $ruta_bd, ':titulo' => $titulo, ':descripcion' => $descripcion]);

                $response = ['success' => true, 'message' => 'Imagen subida al carrusel correctamente.'];
            } else {
                throw new Exception('No se pudo mover la imagen al directorio destino.');
            }
            break;

        case 'eliminar_carrusel':
            if (empty($_POST['id_carrusel'])) {
                throw new Exception('ID de carrusel no proporcionado.');
            }

            $id = $_POST['id_carrusel'];

            // Primero, buscar la ruta para borrar el archivo
            $stmt = $db->prepare("SELECT ruta_imagen FROM cursos.landing_carrusel WHERE id_carrusel = :id");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $ruta_absoluta = __DIR__ . '/../' . str_replace('../', '', $row['ruta_imagen']);
                if (file_exists($ruta_absoluta)) {
                    unlink($ruta_absoluta);
                }

                // Borrar registro
                $stmtDel = $db->prepare("DELETE FROM cursos.landing_carrusel WHERE id_carrusel = :id");
                $stmtDel->execute([':id' => $id]);

                $response = ['success' => true, 'message' => 'Diapositiva eliminada.'];
            } else {
                throw new Exception('Imagen no encontrada en BD.');
            }
            break;

        // --- Cursos Imagen Portada ---
        case 'listar_cursos_admin':
            // Listar todos los cursos (con o sin imagen)
            $stmt = $db->prepare("SELECT id_curso, nombre_curso, tipo_curso, imagen_portada FROM cursos.cursos ORDER BY nombre_curso ASC");
            $stmt->execute();
            $response = ['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
            break;

        case 'subir_imagen_curso':
            if (empty($_POST['id_curso'])) {
                throw new Exception('ID de curso no proporcionado.');
            }
            $id_curso = $_POST['id_curso'];

            // Opcion 1: si envian imagen file
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                // Borrar foto anterior si existe
                $stmtGet = $db->prepare("SELECT imagen_portada FROM cursos.cursos WHERE id_curso = :id");
                $stmtGet->execute([':id' => $id_curso]);
                $old = $stmtGet->fetchColumn();
                if ($old && file_exists(__DIR__ . '/../' . str_replace('../', '', $old))) {
                    unlink(__DIR__ . '/../' . str_replace('../', '', $old));
                }

                $file = $_FILES['imagen'];
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $nombre_archivo = 'course_' . $id_curso . '_' . time() . '.' . $ext;
                $upload_dir = __DIR__ . '/../public/assets/img/courses/';
                if (!is_dir($upload_dir))
                    mkdir($upload_dir, 0777, true);

                $ruta_destino = $upload_dir . $nombre_archivo;
                if (move_uploaded_file($file['tmp_name'], $ruta_destino)) {
                    $ruta_bd = '../public/assets/img/courses/' . $nombre_archivo;
                    $stmtUpd = $db->prepare("UPDATE cursos.cursos SET imagen_portada = :ruta WHERE id_curso = :id");
                    $stmtUpd->execute([':ruta' => $ruta_bd, ':id' => $id_curso]);
                    $response = ['success' => true, 'message' => 'Imagen del curso actualizada.'];
                } else {
                    throw new Exception('No se pudo guardar la imagen del curso.');
                }
            } else {
                // Opcion 2: Si solicitan "quitar imagen" sin subir archivo podriamos manejarlo aqui
                throw new Exception('No se envió ninguna imagen válida.');
            }
            break;

        case 'quitar_imagen_curso':
            if (empty($_POST['id_curso'])) {
                throw new Exception('ID de curso no proporcionado.');
            }
            $id_curso = $_POST['id_curso'];

            // Borrar foto anterior si existe
            $stmtGet = $db->prepare("SELECT imagen_portada FROM cursos.cursos WHERE id_curso = :id");
            $stmtGet->execute([':id' => $id_curso]);
            $old = $stmtGet->fetchColumn();
            if ($old && file_exists(__DIR__ . '/../' . str_replace('../', '', $old))) {
                unlink(__DIR__ . '/../' . str_replace('../', '', $old));
            }

            $stmtUpd = $db->prepare("UPDATE cursos.cursos SET imagen_portada = NULL WHERE id_curso = :id");
            $stmtUpd->execute([':id' => $id_curso]);
            $response = ['success' => true, 'message' => 'Imagen removida.'];

            break;

        default:
            $response['message'] = "La acción '{$action}' no es válida.";
            break;
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>