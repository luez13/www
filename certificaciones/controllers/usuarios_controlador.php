<?php
// Incluir el archivo model.php en config
include '../config/model.php';

// Crear una instancia de la clase DB
$db = new DB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'editar_perfil') {
        // Manejar la solicitud POST para editar perfil
        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $correo = $_POST['correo'];
        $cedula = $_POST['cedula'];
        $id_rol = $_POST['id_rol'];
        $fecha_inscripcion = $_POST['fecha_inscripcion']; // Añadir este campo

        // Actualizar los datos del usuario (nombre, apellido, correo, cédula, rol, fecha de inscripción)
        $stmt = $db->prepare("UPDATE cursos.usuarios SET nombre = :nombre, apellido = :apellido, correo = :correo, cedula = :cedula, id_rol = :id_rol, fecha_inscripcion = :fecha_inscripcion WHERE id = :id");
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido', $apellido);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':cedula', $cedula);
        $stmt->bindParam(':id_rol', $id_rol);
        $stmt->bindParam(':fecha_inscripcion', $fecha_inscripcion); // Añadir esta línea
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    } elseif ($action === 'inscribir_usuarios') {
        // Manejar la inscripción de usuarios seleccionados en el curso
        $curso_id = $_POST['curso_id'];
        $usuarios = $_POST['usuarios'];
        $fecha_inscripcion = date('Y-m-d H:i:s'); // Fecha de inscripción actual

        foreach ($usuarios as $usuario_id) {
            // Verificar si el usuario ya está inscrito
            $stmt = $db->prepare("SELECT COUNT(*) FROM cursos.certificaciones WHERE curso_id = :curso_id AND id_usuario = :id_usuario");
            $stmt->bindParam(':curso_id', $curso_id);
            $stmt->bindParam(':id_usuario', $usuario_id);
            $stmt->execute();
            $count = $stmt->fetchColumn();

            if ($count == 0) {
                // Generar siempre un nuevo hash
                $valor_unico = hash('sha256', $usuario_id . $curso_id . time());

                // Insertar los datos en la base de datos
                try {
                    $stmt = $db->prepare('INSERT INTO cursos.certificaciones (curso_id, id_usuario, valor_unico, fecha_inscripcion) VALUES (:curso_id, :id_usuario, :valor_unico, :fecha_inscripcion)');
                    $stmt->bindParam(':curso_id', $curso_id);
                    $stmt->bindParam(':id_usuario', $usuario_id);
                    $stmt->bindParam(':valor_unico', $valor_unico);
                    $stmt->bindParam(':fecha_inscripcion', $fecha_inscripcion);
                    $stmt->execute();
                } catch (PDOException $e) {
                    // Mostrar un mensaje de error al usuario
                    echo json_encode(['success' => false, 'message' => 'Ha ocurrido un error al inscribir el usuario: ' . $e->getMessage()]);
                    exit;
                }
            }
        }

        echo json_encode(['success' => true, 'message' => 'Usuarios registrados correctamente en el curso.']);
        exit;
    }
}

// Manejar la solicitud GET para mostrar y buscar usuarios
$action = isset($_GET['action']) ? $_GET['action'] : '';
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

if ($action === 'buscar' && !empty($busqueda)) {
    // Utilizar % solo en la consulta, no en el valor del input
    $busqueda_query = "%" . $busqueda . "%";
    $stmt = $db->prepare('SELECT id, nombre, cedula, correo FROM cursos.usuarios WHERE nombre LIKE :busqueda OR correo LIKE :busqueda LIMIT :limit OFFSET :offset');
    $stmt->bindParam(':busqueda', $busqueda_query, PDO::PARAM_STR);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $db->prepare('SELECT COUNT(*) FROM cursos.usuarios');
    $stmt->execute();
    $total = $stmt->fetchColumn();
    $total_pages = ceil($total / $limit);

    $stmt = $db->prepare('SELECT id, nombre, cedula, correo FROM cursos.usuarios LIMIT :limit OFFSET :offset');
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Total de usuarios para la paginación
$stmt = $db->prepare("SELECT COUNT(*) FROM cursos.usuarios WHERE nombre LIKE :busqueda OR correo LIKE :busqueda");
$stmt->bindParam(':busqueda', $busqueda_query, PDO::PARAM_STR);
$stmt->execute();
$total_usuarios = $stmt->fetchColumn();
$total_pages = ceil($total_usuarios / $limit);

$pagination_html = '';

// Calculate start and end pages for display
$start_page = ($page <= 4) ? 1 : max(1, $page - 3);
$end_page = ($page >= $total_pages - 3) ? $total_pages : min($total_pages, $page + 3);

// Generate pagination links
if ($total_pages > 0) {
    if ($page > 1) {
        $pagination_html .= '<li class="page-item"><a class="page-link page-link-nav" href="#" data-page="' . ($page - 1) . '">Anterior</a></li>';
    }

    for ($i = $start_page; $i <= $end_page; $i++) {
        $active_class = ($i == $page) ? 'active' : '';
        $pagination_html .= '<li class="page-item ' . $active_class . '"><a class="page-link page-link-nav" href="#" data-page="' . $i . '">' . $i . '</a></li>';
    }

    if ($page < $total_pages) {
        $pagination_html .= '<li class="page-item"><a class="page-link page-link-nav" href="#" data-page="' . ($page + 1) . '">Siguiente</a></li>';
        $pagination_html .= '<li class="page-item"><a class="page-link page-link-nav" href="#" data-page="' . $total_pages . '">Última</a></li>';
    }
}

// Incluir la vista de usuarios sin el header
include '../views/usuarios.php';
?>