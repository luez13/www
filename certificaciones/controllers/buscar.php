<?php
// Incluir el archivo model.php en config
include '../config/model.php';

// Crear una instancia de la clase DB
$db = new DB();

// Definir la función validar_inscripcion
function validar_inscripcion($id_usuario, $curso_id) {
    if (empty($id_usuario) || empty($curso_id)) {
        return false;
    }
    if (!is_numeric($id_usuario) || !is_numeric($curso_id)) {
        return false;
    }
    return true;
}

$message = '';
$type = '';

// Manejar la solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = isset($_POST['id_usuario']) ? $_POST['id_usuario'] : null;
    $curso_id = isset($_POST['curso_id']) ? $_POST['curso_id'] : null;
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'inscribirse' && validar_inscripcion($id_usuario, $curso_id)) {
        $stmt = $db->prepare('SELECT * FROM cursos.certificaciones WHERE id_usuario = :id_usuario AND curso_id = :curso_id');
        $stmt->execute(['id_usuario' => $id_usuario, 'curso_id' => $curso_id]);
        $inscripcion = $stmt->fetch();

        if ($inscripcion) {
            $message = "Ya estás inscrito en este curso.";
            $type = "warning";
        } else {
            $valor_unico = hash('sha256', $id_usuario . $curso_id . time());
            try {
                $stmt = $db->prepare('INSERT INTO cursos.certificaciones (id_usuario, curso_id, valor_unico, fecha_inscripcion, completado) VALUES (:id_usuario, :curso_id, :valor_unico, NOW(), false)');
                $stmt->execute(['id_usuario' => $id_usuario, 'curso_id' => $curso_id, 'valor_unico' => $valor_unico]);
                $message = "Te has inscrito correctamente en el curso.";
                $type = "success";
            } catch (PDOException $e) {
                $message = "Ha ocurrido un error al inscribirte en el curso: " . $e->getMessage();
                $type = "danger";
            }
        }
    } elseif ($action === 'cancelar_inscripcion' && validar_inscripcion($id_usuario, $curso_id)) {
        try {
            $stmt = $db->prepare('DELETE FROM cursos.certificaciones WHERE id_usuario = :id_usuario AND curso_id = :curso_id');
            $stmt->execute(['id_usuario' => $id_usuario, 'curso_id' => $curso_id]);
            $message = "Has cancelado la inscripción del curso.";
            $type = "success";
        } catch (PDOException $e) {
            $message = "Ha ocurrido un error al cancelar la inscripción del curso: " . $e->getMessage();
            $type = "danger";
        }
    } else {
        $message = "Datos de inscripción inválidos.";
        $type = "danger";
    }
}

// Obtener la página actual y el ID del curso
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$id_curso = isset($_GET['id_curso']) ? (int)$_GET['id_curso'] : 0;
$limit = 10;
$offset = ($page - 1) * $limit;

// Obtener el nombre del curso
$curso = ['nombre_curso' => 'Curso no encontrado'];
if ($id_curso > 0) {
    $stmt = $db->prepare('SELECT nombre_curso FROM cursos.cursos WHERE id_curso = :id_curso');
    $stmt->execute(['id_curso' => $id_curso]);
    $curso = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$curso) {
        $curso = ['nombre_curso' => 'Curso no encontrado'];
    }
}

// Consultar la base de datos para obtener los usuarios
$total_pages = 0;
$usuarios = [];
try {
    $stmt = $db->prepare('SELECT COUNT(*) FROM cursos.usuarios');
    $stmt->execute();
    $total = $stmt->fetchColumn();
    $total_pages = ceil($total / $limit);

    $stmt = $db->prepare('SELECT id, nombre, cedula, correo FROM cursos.usuarios LIMIT :limit OFFSET :offset');
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
    die();
}

// Función para renderizar la paginación
function renderPagination($total_pages, $current_page) {
    $html = '<nav><ul class="pagination">';
    
    // Botón para la primera página
    if ($current_page > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="#" onclick="loadPage(\'../controllers/buscar.php\', { page: 1 }); return false;">Primera</a></li>';
        $html .= '<li class="page-item"><a class="page-link" href="#" onclick="loadPage(\'../controllers/buscar.php\', { page: ' . ($current_page - 1) . ' }); return false;">&laquo; Anterior</a></li>';
    }
    
    // Determinar el rango de páginas a mostrar
    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);
    
    // Ajustar si estamos cerca del principio o final
    if ($current_page <= 3) {
        $end_page = min(5, $total_pages);
    }
    if ($current_page >= $total_pages - 2) {
        $start_page = max(1, $total_pages - 4);
    }
    
    // Páginas numéricas
    for ($i = $start_page; $i <= $end_page; $i++) {
        $active = $i == $current_page ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '"><a class="page-link" href="#" onclick="loadPage(\'../controllers/buscar.php\', { page: ' . $i . ' }); return false;">' . $i . '</a></li>';
    }
    
    // Botón para la última página
    if ($current_page < $total_pages) {
        $html .= '<li class="page-item"><a class="page-link" href="#" onclick="loadPage(\'../controllers/buscar.php\', { page: ' . ($current_page + 1) . ' }); return false;">Siguiente &raquo;</a></li>';
        $html .= '<li class="page-item"><a class="page-link" href="#" onclick="loadPage(\'../controllers/buscar.php\', { page: ' . $total_pages . ' }); return false;">Última</a></li>';
    }
    
    $html .= '</ul></nav>';
    return $html;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Usuarios Registrados</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
<div class="container mt-4" id="page-content">
    <h3>Usuarios Registrados para el curso: <?php echo htmlspecialchars($curso['nombre_curso']) . " (ID: " . htmlspecialchars($id_curso) . ")"; ?></h3>
    <?php if ($message): ?>
        <div class="alert alert-<?= $type; ?>" role="alert">
            <?= $message; ?>
        </div>
    <?php endif; ?>
    <div id="user-list">
        <div class="list-group">
        <?php foreach ($usuarios as $usuario): ?>
            <div id="user-<?= htmlspecialchars($usuario['id']); ?>" class="list-group-item">
                <h5 class="mb-1"><?= htmlspecialchars($usuario['nombre']); ?></h5>
                <p class="mb-1">Cédula: <?= htmlspecialchars($usuario['cedula']); ?></p>
                <small>Email: <?= htmlspecialchars($usuario['correo']); ?></small>
                <?php
                $stmt = $db->prepare('SELECT * FROM cursos.certificaciones WHERE id_usuario = :id_usuario AND curso_id = :curso_id');
                $stmt->execute(['id_usuario' => $usuario['id'], 'curso_id' => $id_curso]);
                $inscripcion = $stmt->fetch();
                ?>
                <?php if ($inscripcion): ?>
                    <form id="inscripcionForm-<?= htmlspecialchars($usuario['id']); ?>" action="../controllers/buscar.php" method="post">
                    <input type="hidden" name="action" value="cancelar_inscripcion">
                        <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($usuario['id']); ?>">
                        <input type="hidden" name="curso_id" value="<?= htmlspecialchars($id_curso); ?>">
                        <input type="hidden" name="page" value="<?= htmlspecialchars($page); ?>">
                        <button type="button" class="btn btn-danger" onclick="inscribirUsuario(<?= htmlspecialchars($usuario['id']); ?>)">Cancelar Inscripción</button>
                    </form>
                <?php else: ?>
                    <form id="inscripcionForm-<?= htmlspecialchars($usuario['id']); ?>" action="../controllers/buscar.php" method="post">
                        <input type="hidden" name="action" value="inscribirse">
                        <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($usuario['id']); ?>">
                        <input type="hidden" name="curso_id" value="<?= htmlspecialchars($id_curso); ?>">
                        <input type="hidden" name="page" value="<?= htmlspecialchars($page); ?>">
                        <button type="button" class="btn btn-primary" onclick="inscribirUsuario(<?= htmlspecialchars($usuario['id']); ?>)">Agregar al Curso</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        </div>
        <!-- Renderizar la paginación -->
        <?= renderPagination($total_pages, $page); ?>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Función para desplazarse a un elemento específico después de cargar la página
    var params = new URLSearchParams(window.location.search);
    if (params.has('scrollTo')) {
        var elementId = params.get('scrollTo');
        var element = document.getElementById(elementId);
        if (element) {
            element.scrollIntoView();
        }
    }
});
</script>
</body>
</html>