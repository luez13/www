<?php
// Incluir el archivo model.php en config
include '../config/model.php';

// Incluir el archivo header.php en views
include '../views/header.php';

// Crear una instancia de la clase DB
$db = new DB();

// Obtener el tipo de curso y el estado de la solicitud GET
$tipo_curso = isset($_GET['tipo_curso']) ? $_GET['tipo_curso'] : null;
$estado = isset($_GET['estado']) ? $_GET['estado'] : FALSE || NULL;

// Determinar el texto del estado
$estado_texto = $estado ? 'Abiertos' : 'Cerrados';

// Consultar la base de datos para obtener los cursos disponibles
try {
    if ($tipo_curso && $estado !== null) {
        $stmt = $db->prepare('SELECT * FROM cursos.cursos WHERE tipo_curso = :tipo_curso AND estado = :estado');
        $stmt->execute(['tipo_curso' => $tipo_curso, 'estado' => $estado]);
    } else {
        $stmt = $db->prepare('SELECT * FROM cursos.cursos WHERE estado = :estado');
        $stmt->execute(['estado' => true]);
    }
    $cursos = $stmt->fetchAll();

    echo '<div class="container mt-4">';
    echo '<h3 class="text-center">' . ($tipo_curso ? ucfirst($tipo_curso) : 'Cursos') . ' ' . $estado_texto . '</h3>';
    echo '<div class="row">';
    foreach ($cursos as $curso) {
        // Consultar si el usuario ya está inscrito en el curso
        $stmt2 = $db->prepare('SELECT * FROM cursos.certificaciones WHERE curso_id = :curso_id AND id_usuario = :id_usuario');
        $stmt2->execute(['curso_id' => $curso['id_curso'], 'id_usuario' => $_SESSION['user_id']]);
        $inscripcion = $stmt2->fetch();
    
        // Si el usuario no está inscrito en el curso, mostrarlo
        if (!$inscripcion) {
            echo '<div class="col-md-4">';
            echo '<div class="card mb-4 shadow-sm">';
            echo '<div class="card-body">';
            echo '<h5 class="card-title">' . $curso['nombre_curso'] . '</h5>';
            echo '<p class="card-text">' . substr($curso['descripcion'], 0, 100) . '...</p>';
            echo '<a href="#" class="btn btn-primary" onclick="loadCourse(' . $curso['id_curso'] . ')">Ver más</a>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    }    
    echo '</div>';
    echo '</div>';
} catch (PDOException $e) {
    // Mostrar un mensaje de error al usuario
    echo '<div class="alert alert-danger" role="alert">';
    echo 'Ha ocurrido un error al obtener los cursos disponibles: ' . $e->getMessage();
    echo '</div>';
}

// Incluir el archivo footer.php en views
include '../views/footer.php';
?>