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
        // Mostrar el nombre del curso como un enlace que redirige al archivo curso.php con el id del curso
        echo '<li><a href="../views/curso.php?id=' . $curso['id_curso'] . '">' . $curso['nombre_curso'] . '</a></li>';
    }
    echo '</ul>';
} catch (PDOException $e) {
    // Mostrar un mensaje de error al usuario
    echo '<p>Ha ocurrido un error al obtener los cursos disponibles: ' . $e->getMessage() . '</p>';
}

// Incluir el archivo footer.php en views
include '../views/footer.php';
?>