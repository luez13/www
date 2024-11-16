<?php
// Incluir el archivo model.php en config
include '../config/model.php';

// Crear una instancia de la clase DB
$db = new DB();

// Definir la función validar_inscripcion
function validar_inscripcion($id_usuario, $curso_id) {
    // Verificar que los datos no estén vacíos
    if (empty($id_usuario) || empty($curso_id)) {
        return false;
    }
    // Verificar que los datos sean numéricos
    if (!is_numeric($id_usuario) || !is_numeric($curso_id)) {
        return false;
    }
    // Si todo está bien, devolver true
    return true;
}

$message = '';
$type = '';

// Procesar la inscripción si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'inscribirse') {
    $id_usuario = $_POST['id_usuario'];
    $curso_id = $_POST['curso_id'];

    if (validar_inscripcion($id_usuario, $curso_id)) {
        // Verificar si el usuario ya está inscrito en el curso
        $stmt = $db->prepare('SELECT * FROM cursos.certificaciones WHERE id_usuario = :id_usuario AND curso_id = :curso_id');
        $stmt->execute(['id_usuario' => $id_usuario, 'curso_id' => $curso_id]);
        $inscripcion = $stmt->fetch();

        if ($inscripcion) {
            $message = "Ya estás inscrito en este curso.";
            $type = "warning";
        } else {
            // Generar siempre un nuevo hash
            $valor_unico = hash('sha256', $id_usuario . $curso_id . time());

            // Insertar los datos en la base de datos
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
    } else {
        $message = "Los datos de inscripción son inválidos.";
        $type = "danger";
    }
}

// Procesar la cancelación de inscripción si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'cancelar_inscripcion') {
    $id_usuario = $_POST['id_usuario'];
    $curso_id = $_POST['curso_id'];

    // Validar los datos
    if (validar_inscripcion($id_usuario, $curso_id)) {
        // Eliminar los datos de la base de datos
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
        $message = "Los datos para cancelar la inscripción son inválidos.";
        $type = "danger";
    }
}

// Obtener la página actual y el ID del curso
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$id_curso = isset($_GET['id_curso']) ? (int)$_GET['id_curso'] : 0;
$limit = 10;
$offset = ($page - 1) * $limit;

// Consultar la base de datos para obtener los usuarios
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Usuarios Registrados</title>
</head>
<body>
<div class="container mt-4">
    <h3>Usuarios Registrados</h3>
    <?php if ($message): ?>
        <div class="alert alert-<?= $type; ?>" role="alert">
            <?= $message; ?>
        </div>
    <?php endif; ?>
    <div class="list-group">
        <?php foreach ($usuarios as $usuario): ?>
            <div class="list-group-item">
                <h5 class="mb-1"><?= htmlspecialchars($usuario['nombre']); ?></h5>
                <p class="mb-1">Cédula: <?= htmlspecialchars($usuario['cedula']); ?></p>
                <small>Email: <?= htmlspecialchars($usuario['correo']); ?></small>
                <?php
                // Verificar si el usuario ya está inscrito en el curso
                $stmt = $db->prepare('SELECT * FROM cursos.certificaciones WHERE id_usuario = :id_usuario AND curso_id = :curso_id');
                $stmt->execute(['id_usuario' => $usuario['id'], 'curso_id' => $id_curso]);
                $inscripcion = $stmt->fetch();
                ?>
                <?php if ($inscripcion): ?>
                    <form action="buscar.php?id_curso=<?= htmlspecialchars($id_curso); ?>" method="post" class="mt-2">
                        <input type="hidden" name="action" value="cancelar_inscripcion">
                        <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($usuario['id']); ?>">
                        <input type="hidden" name="curso_id" value="<?= htmlspecialchars($id_curso); ?>">
                        <button type="submit" class="btn btn-danger">Cancelar Inscripción</button>
                    </form>
                <?php else: ?>
                    <form action="buscar.php?id_curso=<?= htmlspecialchars($id_curso); ?>" method="post" class="mt-2">
                        <input type="hidden" name="action" value="inscribirse">
                        <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($usuario['id']); ?>">
                        <input type="hidden" name="curso_id" value="<?= htmlspecialchars($id_curso); ?>">
                        <button type="submit" class="btn btn-primary">Agregar al Curso</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <nav>
        <ul class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="buscar.php?page=<?= $i; ?>&id_curso=<?= $id_curso ?>"><?= $i; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>
</body>
</html>