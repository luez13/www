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

    echo '<div class="main-content">';
    echo '<h3>' . ($tipo_curso ? ucfirst($tipo_curso) : 'Cursos') . ' ' . $estado_texto . '</h3>';
    echo '<ul>';
    foreach ($cursos as $curso) {
        // Consultar si el usuario ya está inscrito en el curso
        $stmt2 = $db->prepare('SELECT * FROM cursos.certificaciones WHERE curso_id = :curso_id AND id_usuario = :id_usuario');
        $stmt2->execute(['curso_id' => $curso['id_curso'], 'id_usuario' => $_SESSION['user_id']]);
        $inscripcion = $stmt2->fetch();
    
        // Si el usuario no está inscrito en el curso, mostrarlo
        if (!$inscripcion) {
            echo '<li><a href="#" onclick="loadCourse(' . $curso['id_curso'] . ')">' . $curso['nombre_curso'] . '</a></li>';
        }
    }    
    echo '</ul>';
    echo '</div>';
} catch (PDOException $e) {
    // Mostrar un mensaje de error al usuario
    echo '<p>Ha ocurrido un error al obtener los cursos disponibles: ' . $e->getMessage() . '</p>';
}

// Incluir el archivo footer.php en views
include '../views/footer.php';
?>