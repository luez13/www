<?php
// Incluir el archivo model.php en config
include '../config/model.php';

// Incluir el archivo header.php en views
include '../views/header.php';

// Crear una instancia de la clase DB
$db = new DB();

// Consultar la base de datos para obtener los cursos disponibles
try {
    $stmt = $db->prepare('SELECT * FROM cursos.cursos WHERE estado = :estado');
    $stmt->execute(['estado' => true]);
    $cursos = $stmt->fetchAll();

    // Mostrar los cursos en formato HTML
    echo '<h3>Cursos disponibles</h3>';
    echo '<ul>';
    foreach ($cursos as $curso) {
        // Consultar si el usuario ya está inscrito en el curso
        $stmt2 = $db->prepare('SELECT * FROM cursos.certificaciones WHERE curso_id = :curso_id AND id_usuario = :id_usuario');
        $stmt2->execute(['curso_id' => $curso['id_curso'], 'id_usuario' => $_SESSION['user_id']]);
        $inscripcion = $stmt2->fetch();
    
        // Si el usuario no está inscrito en el curso, mostrarlo
        if (!$inscripcion) {
            echo '<li><a href="../views/curso.php?id=' . $curso['id_curso'] . '">' . $curso['nombre_curso'] . '</a></li>';
        }
    }    
    echo '</ul>';
} catch (PDOException $e) {
    // Mostrar un mensaje de error al usuario
    echo '<p>Ha ocurrido un error al obtener los cursos disponibles: ' . $e->getMessage() . '</p>';
}

// Incluir el archivo footer.php en views
include '../views/footer.php';
?>