<?php
// Incluir el archivo header.php en views
include '../views/header.php';

$user_id = $_SESSION['user_id'];

// Consultar la base de datos para obtener los datos del usuario
try {
    $stmt = $db->prepare('SELECT * FROM cursos.usuarios WHERE id = :id');
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch();
    // Mostrar los datos del usuario en formato HTML
    echo '<div class="container">';
    echo '<h3>Datos del usuario</h3>';
    echo '<p>Nombre: ' . $user['nombre'] . '</p>';
    echo '<p>Apellido: ' . $user['apellido'] . '</p>';
    echo '<p>Correo: ' . $user['correo'] . '</p>';
    echo '<p>Cédula: ' . $user['cedula'] . '</p>';
    echo '<p>Rol: ' . $user['id_rol'] . '</p>';
    // Mostrar un botón para editar los datos del usuario
    echo '<form action="../models/datos_usuario.php" method="post">';
    echo '<input type="hidden" name="action" value="editar">';
    echo '<input type="hidden" name="nombre" value="' . $user['nombre'] . '">';
    echo '<input type="hidden" name="apellido" value="' . $user['apellido'] . '">';
    echo '<input type="hidden" name="correo" value="' . $user['correo'] . '">';
    echo '<input type="hidden" name="cedula" value="' . $user['cedula'] . '">';
    echo '<input type="submit" value="Editar datos" class="btn btn-dark">';
    echo '</form>';    
    echo '</div>';

} catch (PDOException $e) {
    // Mostrar un mensaje de error al usuario
    echo '<p>Ha ocurrido un error al obtener los datos del usuario: ' . $e->getMessage() . '</p>';
}
// Mostrar un botón para ver los cursos disponibles
echo '<div class="container">';
echo '<form action="cursos.php" method="get">';
echo '<input type="submit" value="Ver cursos disponibles" class="btn btn-dark">';
echo '</form>';
echo '</div>';

// Mostrar un botón para ver los cursos creados por el usuario
echo '<div class="container">';
echo '<form action="gestion_cursos.php" method="get">';
echo '<input type="submit" value="Ver cursos creados por ti" class="btn btn-secondary">';
echo '</form>';
echo '</div>';

require_once '../controllers/autenticacion.php';
if (esPerfil4($user_id)) {
    echo '<div class="container">';
    echo '<form action="usuarios.php" method="get">';
    echo '<input type="submit" value="Ver usuarios del sistema" class="btn btn-primary">';
    echo '</form>';
    echo '</div>';

    echo '<div class="container">';
    echo '<form action="editar_cursos.php" method="get">';
    echo '<input type="submit" value="Editar cursos" class="btn btn-primary">';
    echo '</form>';
    echo '</div>';
}


// Consultar la base de datos para obtener los cursos en los que el usuario está inscrito
try {
    $stmt = $db->prepare('SELECT c.* FROM cursos.cursos c JOIN cursos.certificaciones ce ON c.id_curso = ce.curso_id WHERE ce.id_usuario = :id_usuario AND ce.completado = false');
    $stmt->execute(['id_usuario' => $user_id]);
    $cursos_inscritos = $stmt->fetchAll();
    // Mostrar los cursos en los que el usuario está inscrito en formato HTML
    echo '<div class="container">';
    echo '<h3>Cursos en los que estás inscrito</h3>';
    echo '<ul>';
    foreach ($cursos_inscritos as $curso) {
        // Mostrar el nombre del curso como un enlace que redirige al archivo curso.php con el id del curso
        echo '<li><a href="../views/curso.php?id=' . $curso['id_curso'] . '">' . $curso['nombre_curso'] . '</a></li>';
    }
    echo '</ul>';
    echo '</div>';
} catch (PDOException $e) {
    // Mostrar un mensaje de error al usuario
    echo '<p>Ha ocurrido un error al obtener los cursos en los que estás inscrito: ' . $e->getMessage() . '</p>';
}

// Consultar la base de datos para obtener los cursos que el usuario ha finalizado
try {
    $stmt = $db->prepare('SELECT c.* FROM cursos.cursos c JOIN cursos.certificaciones ce ON c.id_curso = ce.curso_id WHERE ce.id_usuario = :id_usuario AND ce.completado = true');
    $stmt->execute(['id_usuario' => $user_id]);
    $cursos_finalizados = $stmt->fetchAll();
    // Mostrar los cursos que el usuario ha finalizado en formato HTML
    echo '<div class="container">';
    echo '<h3>Cursos que has finalizado</h3>';
    echo '<ul>';
    foreach ($cursos_finalizados as $curso) {
        // Mostrar el nombre del curso como un enlace que redirige al archivo curso.php con el id del curso
        echo '<li><a href="../views/curso.php?id=' . $curso['id_curso'] . '">' . $curso['nombre_curso'] . '</a></li>';
    }
    echo '</ul>';
    echo '</div>';
} catch (PDOException $e) {
    // Mostrar un mensaje de error al usuario
    echo '<p>Ha ocurrido un error al obtener los cursos que has finalizado: ' . $e->getMessage() . '</p>';
}

// Incluir el archivo footer.php en views
include '../views/footer.php';
?>