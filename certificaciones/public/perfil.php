<?php
// Iniciar la sesión
session_start();

// Incluir el archivo model.php en config
include '../config/model.php';

// Incluir el archivo header.php en views
include '../views/header.php';

// Crear una instancia de la clase DB
$db = new DB();

$user_id = $_SESSION['user_id'];

// Consultar la base de datos para obtener los datos del usuario
try {
    $stmt = $db->prepare('SELECT * FROM cursos.usuarios WHERE id = :id');
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch();
    // Mostrar los datos del usuario en formato HTML
    echo '<h3>Datos del usuario</h3>';
    echo '<p>Nombre: ' . $user['nombre'] . '</p>';
    echo '<p>Apellido: ' . $user['apellido'] . '</p>';
    echo '<p>Correo: ' . $user['correo'] . '</p>';
    echo '<p>Cédula: ' . $user['cedula'] . '</p>';
    echo '<p>Rol: ' . $user['id_rol'] . '</p>';
    // Mostrar un botón para editar los datos del usuario
    echo '<form action="../controllers/autenticacion.php" method="post">';
    echo '<input type="hidden" name="action" value="editar">';
    echo '<input type="submit" value="Editar datos">';
    echo '</form>';
} catch (PDOException $e) {
    // Mostrar un mensaje de error al usuario
    echo '<p>Ha ocurrido un error al obtener los datos del usuario: ' . $e->getMessage() . '</p>';
}
// Mostrar un botón para ver los cursos disponibles
echo '<form action="cursos.php" method="get">';
echo '<input type="submit" value="Ver cursos disponibles">';
echo '</form>';

// Mostrar un botón para ver los cursos creados por el usuario
echo '<form action="gestion_cursos.php" method="get">';
echo '<input type="submit" value="Ver cursos creados por ti">';
echo '</form>';


// Consultar la base de datos para obtener los cursos en los que el usuario está inscrito
try {
    $stmt = $db->prepare('SELECT c.* FROM cursos.cursos c JOIN cursos.certificaciones ce ON c.id_curso = ce.id_curso WHERE ce.id_usuario = :id_usuario AND ce.completado = false');
    $stmt->execute(['id_usuario' => $user_id]);
    $cursos_inscritos = $stmt->fetchAll();
    // Mostrar los cursos en los que el usuario está inscrito en formato HTML
    echo '<h3>Cursos en los que estás inscrito</h3>';
    echo '<ul>';
    foreach ($cursos_inscritos as $curso) {
        // Mostrar el nombre del curso como un enlace que redirige al archivo curso.php con el id del curso
        echo '<li><a href="views/curso.php?id=' . $curso['id_curso'] . '">' . $curso['nombre_curso'] . '</a></li>';
    }
    echo '</ul>';
} catch (PDOException $e) {
    // Mostrar un mensaje de error al usuario
    echo '<p>Ha ocurrido un error al obtener los cursos en los que estás inscrito: ' . $e->getMessage() . '</p>';
}

// Consultar la base de datos para obtener los cursos que el usuario ha finalizado
try {
    $stmt = $db->prepare('SELECT c.* FROM cursos.cursos c JOIN cursos.certificaciones ce ON c.id_curso = ce.id_curso WHERE ce.id_usuario = :id_usuario AND ce.completado = true');
    $stmt->execute(['id_usuario' => $user_id]);
    $cursos_finalizados = $stmt->fetchAll();
    // Mostrar los cursos que el usuario ha finalizado en formato HTML
    echo '<h3>Cursos que has finalizado</h3>';
    echo '<ul>';
    foreach ($cursos_finalizados as $curso) {
        // Mostrar el nombre del curso como un enlace que redirige al archivo curso.php con el id del curso
        echo '<li><a href="views/curso.php?id=' . $curso['id_curso'] . '">' . $curso['nombre_curso'] . '</a></li>';
    }
    echo '</ul>';
} catch (PDOException $e) {
    // Mostrar un mensaje de error al usuario
    echo '<p>Ha ocurrido un error al obtener los cursos que has finalizado: ' . $e->getMessage() . '</p>';
}

// Incluir el archivo footer.php en views
include '../views/footer.php';
?>