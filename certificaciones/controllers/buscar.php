<?php
// Incluir el archivo model.php en config
include '../config/model.php';

// Crear una instancia de la clase DB
$db = new DB();

$pagina_actual = 'buscar.php'; // Definir la página actual

// ... (La función validar_inscripcion no cambia) ...
function validar_inscripcion($id_usuario, $curso_id) {
    if (empty($id_usuario) || empty($curso_id)) { return false; }
    if (!is_numeric($id_usuario) || !is_numeric($curso_id)) { return false; }
    return true;
}

$message = '';
$type = '';
// El manejo de la solicitud POST no cambia en absoluto.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = isset($_POST['id_usuario']) ? $_POST['id_usuario'] : null;
    $curso_id = isset($_POST['curso_id']) ? $_POST['curso_id'] : null;
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'actualizar_fecha' && validar_inscripcion($id_usuario, $curso_id)) {
        $fecha_inscripcion = isset($_POST['fecha_inscripcion']) ? $_POST['fecha_inscripcion'] : null;
        if ($fecha_inscripcion) {
            try {
                $stmt = $db->prepare('UPDATE cursos.certificaciones SET fecha_inscripcion = :fecha_inscripcion WHERE id_usuario = :id_usuario AND curso_id = :curso_id');
                $stmt->execute(['fecha_inscripcion' => $fecha_inscripcion, 'id_usuario' => $id_usuario, 'curso_id' => $curso_id]);
                $message = "Fecha de inscripción actualizada correctamente.";
                $type = "success";
            } catch (PDOException $e) {
                $message = "Ha ocurrido un error al actualizar la fecha de inscripción: " . $e->getMessage();
                $type = "danger";
            }
        } else {
            $message = "La fecha de inscripción no puede estar vacía.";
            $type = "warning";
        }
    }
    elseif ($action === 'inscribirse' && validar_inscripcion($id_usuario, $curso_id)) {
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

// --- CAMBIOS PARA LA BÚSQUEDA Y PAGINACIÓN ---
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$id_curso = isset($_GET['id_curso']) ? (int)$_GET['id_curso'] : 0;
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : ''; // <-- CAMBIO: Obtenemos el término de búsqueda
$limit = 10;
$offset = ($page - 1) * $limit;

// La lógica para obtener el nombre del curso no cambia
$curso = ['nombre_curso' => 'Curso no encontrado'];
if ($id_curso > 0) {
    $stmt = $db->prepare('SELECT nombre_curso FROM cursos.cursos WHERE id_curso = :id_curso');
    $stmt->execute(['id_curso' => $id_curso]);
    $curso = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$curso) { $curso = ['nombre_curso' => 'Curso no encontrado']; }
}

$total_pages = 0;
$usuarios = [];
try {
    // --- CAMBIO: La consulta para contar ahora también usa la búsqueda ---
    $count_sql = "SELECT COUNT(*) FROM cursos.usuarios u";
    $params = [];
    $whereClause = '';
    if (!empty($busqueda)) {
        $whereClause = " WHERE u.nombre ILIKE :busqueda OR u.apellido ILIKE :busqueda OR u.cedula ILIKE :busqueda";
        $params[':busqueda'] = "%$busqueda%";
    }
    $stmt_count = $db->prepare($count_sql . $whereClause);
    $stmt_count->execute($params);
    $total = $stmt_count->fetchColumn();
    $total_pages = ceil($total / $limit);

    // --- CAMBIO: La consulta principal ahora también filtra por la búsqueda ---
    $sql = "
        SELECT u.id, u.nombre, u.apellido, u.cedula, u.correo, 
               CASE WHEN c.id_usuario IS NOT NULL THEN 1 ELSE 0 END AS inscrito
        FROM cursos.usuarios u
        LEFT JOIN cursos.certificaciones c ON u.id = c.id_usuario AND c.curso_id = :id_curso
    ";
    
    // Añadimos la cláusula WHERE de búsqueda a la consulta principal
    $sql .= $whereClause;

    $sql .= " ORDER BY inscrito DESC, u.nombre ASC LIMIT :limit OFFSET :offset";
    
    $stmt = $db->prepare($sql);
    
    // Unimos los parámetros
    $final_params = array_merge($params, [
        ':id_curso' => $id_curso,
        ':limit' => $limit,
        ':offset' => $offset
    ]);

    // Bindeamos los parámetros dinámicamente
    foreach ($final_params as $key => &$val) {
        $type = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindParam($key, $val, $type);
    }
    
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
    die();
}

// --- CAMBIO: La función de paginación ahora necesita el término de búsqueda ---
function renderPagination($total_pages, $current_page, $pagina_actual, $id_curso, $busqueda) {
    $html = '<nav><ul class="pagination">';
    $busqueda_js = htmlspecialchars($busqueda, ENT_QUOTES); // Escapamos para JavaScript

    if ($current_page > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="#" onclick="loadPage(\'' . $pagina_actual . '\', { page: 1, id_curso: ' . $id_curso . ', busqueda: \'' . $busqueda_js . '\' }); return false;">Primera</a></li>';
        $html .= '<li class="page-item"><a class="page-link" href="#" onclick="loadPage(\'' . $pagina_actual . '\', { page: ' . ($current_page - 1) . ', id_curso: ' . $id_curso . ', busqueda: \'' . $busqueda_js . '\' }); return false;">&laquo; Anterior</a></li>';
    }

    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);
    if ($current_page <= 3) { $end_page = min(5, $total_pages); }
    if ($current_page >= $total_pages - 2) { $start_page = max(1, $total_pages - 4); }

    for ($i = $start_page; $i <= $end_page; $i++) {
        $active = $i == $current_page ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '"><a class="page-link" href="#" onclick="loadPage(\'' . $pagina_actual . '\', { page: ' . $i . ', id_curso: ' . $id_curso . ', busqueda: \'' . $busqueda_js . '\' }); return false;">' . $i . '</a></li>';
    }

    if ($current_page < $total_pages) {
        $html .= '<li class="page-item"><a class="page-link" href="#" onclick="loadPage(\'' . $pagina_actual . '\', { page: ' . ($current_page + 1) . ', id_curso: ' . $id_curso . ', busqueda: \'' . $busqueda_js . '\' }); return false;">Siguiente &raquo;</a></li>';
        $html .= '<li class="page-item"><a class="page-link" href="#" onclick="loadPage(\'' . $pagina_actual . '\', { page: ' . $total_pages . ', id_curso: ' . $id_curso . ', busqueda: \'' . $busqueda_js . '\' }); return false;">Última</a></li>';
    }

    $html .= '</ul></nav>';
    return $html;
}
?>

<!DOCTYPE html>
<html lang="es">
<body>
<div class="container mt-4" id="page-content">
    <h3>Usuarios Registrados para el curso: <?php echo htmlspecialchars($curso['nombre_curso']) . " (ID: " . htmlspecialchars($id_curso) . ")"; ?></h3>

        <div class="input-group mb-3">
        <span class="input-group-text" id="basic-addon1"><i class="fas fa-search"></i></span>
        <input type="text" id="inscripcion-search-input" class="form-control" 
               placeholder="Buscar por nombre, apellido o cédula..." 
               value="<?= htmlspecialchars($busqueda) ?>"
               data-id-curso="<?= htmlspecialchars($id_curso) ?>">
    </div>
    <?php if (!empty($usuarios)): ?>
    <button type="button" class="btn btn-info" onclick="loadPage('../controllers/generar_certificados_lote.php', { curso_id: <?= htmlspecialchars($id_curso); ?> })">
        Ver/Descargar Todos los Certificados
    </button>
    <?php endif; ?>

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

                // Nueva consulta para obtener la fecha de inscripción
                $stmt_fecha = $db->prepare('SELECT fecha_inscripcion, valor_unico FROM cursos.certificaciones WHERE id_usuario = :id_usuario AND curso_id = :curso_id');
                $stmt_fecha->execute(['id_usuario' => $usuario['id'], 'curso_id' => $id_curso]);
                $fecha_info = $stmt_fecha->fetch(PDO::FETCH_ASSOC);
                $fecha_inscripcion_db = $fecha_info ? $fecha_info['fecha_inscripcion'] : '';
                $fecha_para_input = '';
                if ($fecha_inscripcion_db) {
                    // Tomamos solo la parte de la fecha (los primeros 10 caracteres)
                    $fecha_para_input = substr($fecha_inscripcion_db, 0, 10);
                }
                $valor_unico = $fecha_info ? htmlspecialchars($fecha_info['valor_unico']) : '';
                ?>
                <?php if ($inscripcion): ?>
                    <form id="inscripcionForm-<?= htmlspecialchars($usuario['id']); ?>" action="../controllers/buscar.php" method="post">
                        <input type="hidden" name="action" value="cancelar_inscripcion">
                        <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($usuario['id']); ?>">
                        <input type="hidden" name="curso_id" value="<?= htmlspecialchars($id_curso); ?>">
                        <input type="hidden" name="page" value="<?= htmlspecialchars($page); ?>">
                        <button type="button" class="btn btn-danger" onclick="inscribirUsuario(<?= htmlspecialchars($usuario['id']); ?>)">Cancelar Inscripción</button>
                    </form>
                        <form id="fechaForm-<?= htmlspecialchars($usuario['id']); ?>" action="../controllers/buscar.php" method="post" class="mt-2" onsubmit="event.preventDefault();">
                            <input type="hidden" name="action" value="actualizar_fecha">
                            <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($usuario['id']); ?>">
                            <input type="hidden" name="curso_id" value="<?= htmlspecialchars($id_curso); ?>">
                            <input type="hidden" name="page" value="<?= htmlspecialchars($page); ?>">
                            <div class="input-group">
                                <input type="date" class="form-control form-control-sm" name="fecha_inscripcion" value="<?= htmlspecialchars($fecha_para_input) ?>">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="actualizarFechaUsuario(<?= htmlspecialchars($usuario['id']); ?>)">Guardar Fecha</button>
                                </div>
                            </div>
                        </form>
                    <?php if ($valor_unico): ?>
                        <a href="../controllers/generar_certificado.php?valor_unico=<?= $valor_unico ?>" class="btn btn-success mt-2" target="_blank">Ver Certificado Digital</a>
                    <?php endif; ?>
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
        <?php
        // Asegúrate de que `$id_curso` se pase en cada enlace de paginación
        echo renderPagination($total_pages, $page, 'buscar.php', $id_curso, $busqueda);
        ?>
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