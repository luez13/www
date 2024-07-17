<?php
// Incluir el archivo header.php en views
include '../views/header.php';

$user_id = $_SESSION['user_id'];
echo '<div class="main-content">';
// Consultar la base de datos para obtener los datos del usuario
try {
    $stmt = $db->prepare('SELECT usuarios.*, roles.nombre_rol FROM cursos.usuarios INNER JOIN cursos.roles ON usuarios.id_rol = roles.id_rol WHERE usuarios.id = :id');
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch();
    // Mostrar los datos del usuario en formato HTML
    echo '<div class="container">';
    echo '<h3>Datos del usuario</h3>';
    echo '<p>Nombre: ' . $user['nombre'] . '</p>';
    echo '<p>Apellido: ' . $user['apellido'] . '</p>';
    echo '<p>Correo: ' . $user['correo'] . '</p>';
    echo '<p>Cédula: ' . $user['cedula'] . '</p>';
    echo '<p>Rol: ' . $user['nombre_rol'] . '</p>'; // Ahora muestra el nombre del rol
    // Mostrar un botón para editar los datos del usuario
    echo '<form action="../models/datos_usuario.php" method="post">';
    echo '<input type="hidden" name="action" value="editar">';
    echo '<input type="hidden" name="nombre" value="' . htmlspecialchars($user['nombre']) . '">';
    echo '<input type="hidden" name="apellido" value="' . htmlspecialchars($user['apellido']) . '">';
    echo '<input type="hidden" name="correo" value="' . htmlspecialchars($user['correo']) . '">';
    echo '<input type="hidden" name="cedula" value="' . htmlspecialchars($user['cedula']) . '">';
    echo '<input type="hidden" name="nueva_contrasena" value="' . htmlspecialchars($user['password']) . '">';
    echo '<input type="submit" value="Editar datos" class="btn btn-dark">';
    echo '</form>';  
    echo '</div>';
} catch (PDOException $e) {
    // Mostrar un mensaje de error al usuario
    echo '<p>Ha ocurrido un error al obtener los datos del usuario: ' . $e->getMessage() . '</p>';
}

require_once '../controllers/autenticacion.php';

// Definir los botones comunes
$verCursosDisponiblesBtn = '<div class="container"><form action="cursos.php" method="get"><input type="submit" value="Ver cursos disponibles" class="btn btn-dark"></form></div>';
$verCursosCreadosBtn = '<div class="container"><form action="gestion_cursos.php" method="get"><input type="submit" value="Crear y gestionar tus cursos" class="btn btn-secondary"></form></div>';
$verUsuariosBtn = '<div class="container"><form action="usuarios.php" method="get"><input type="submit" value="Ver usuarios del sistema" class="btn btn-primary"></form></div>';
$editarCursosBtn = '<div class="container"><form action="editar_cursos.php" method="get"><input type="submit" value="Editar cursos" class="btn btn-primary"></form></div>';

if (esPerfil4($user_id)) {
    echo $verCursosDisponiblesBtn;
    echo $verUsuariosBtn;
    echo $editarCursosBtn;
} elseif (esPerfil3($user_id)) {
    echo $verCursosDisponiblesBtn;
    echo $verCursosCreadosBtn;
    echo $editarCursosBtn;
} elseif (esPerfil2($user_id)) {
    echo $verCursosCreadosBtn;
    echo $verCursosDisponiblesBtn;
} elseif (esPerfil1($user_id)) {
    echo $verCursosDisponiblesBtn;
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

echo '</div>';
// Incluir el archivo footer.php en views
include '../views/footer.php';
?>