<?php
// Incluir el archivo model.php en config
include '../config/model.php';

require_once '../controllers/init.php';

$user_id = $_SESSION['user_id'];

// Verificar si la solicitud es AJAX
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

echo '<div class="main-content">';

if (isset($_GET['action']) && $_GET['action'] == 'inscritos') {
    // Consultar la base de datos para obtener los cursos en los que el usuario está inscrito
    try {
        $stmt = $db->prepare('SELECT c.* FROM cursos.cursos c JOIN cursos.certificaciones ce ON c.id_curso = ce.curso_id WHERE ce.id_usuario = :id_usuario AND ce.completado = false');
        $stmt->execute(['id_usuario' => $user_id]);
        $cursos_inscritos = $stmt->fetchAll();

        // Mostrar los cursos en los que el usuario está inscrito en formato HTML
        echo '<h3>Cursos en los que estás inscrito</h3>';
        echo '<ul>';
        foreach ($cursos_inscritos as $curso) {
            // Mostrar el nombre del curso como un enlace que redirige al archivo curso.php con el id del curso
            echo '<li><a href="#" onclick="loadCourse(' . $curso['id_curso'] . ')">' . $curso['nombre_curso'] . '</a></li>';
        }
        echo '</ul>';
    } catch (PDOException $e) {
        // Mostrar un mensaje de error al usuario
        echo '<p>Ha ocurrido un error al obtener los cursos en los que estás inscrito: ' . $e->getMessage() . '</p>';
    }
} elseif (isset($_GET['action']) && $_GET['action'] == 'finalizados') {
    // Consultar la base de datos para obtener los cursos que el usuario ha finalizado
    try {
        $stmt = $db->prepare('SELECT c.* FROM cursos.cursos c JOIN cursos.certificaciones ce ON c.id_curso = ce.curso_id WHERE ce.id_usuario = :id_usuario AND ce.completado = true');
        $stmt->execute(['id_usuario' => $user_id]);
        $cursos_finalizados = $stmt->fetchAll();

        // Mostrar los cursos que el usuario ha finalizado en formato HTML
        echo '<h3>Cursos que has finalizado</h3>';
        echo '
        <div class="alert alert-warning border-start border-warning border-4 shadow-sm" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-info-circle fa-2x me-3 text-warning"></i>
                <div>
                    <strong>Aviso Importante sobre Certificación:</strong><br>
                    La emisión del certificado digital es automática. Sin embargo, para la <strong>validación oficial (firmas y sellos)</strong>, es necesario consignar el pago de los aranceles administrativos correspondientes.
                </div>
            </div>
        </div>';
        // --- FIN DEL BLOQUE ---

        echo '<ul>';
        foreach ($cursos_finalizados as $curso) {
            // Mostrar el nombre del curso como un enlace que redirige al archivo curso.php con el id del curso
            echo '<li><a href="#" onclick="loadCourse(' . $curso['id_curso'] . ')">' . $curso['nombre_curso'] . '</a></li>';
        }
        echo '</ul>';
    } catch (PDOException $e) {
        // Mostrar un mensaje de error al usuario
        echo '<p>Ha ocurrido un error al obtener los cursos que has finalizado: ' . $e->getMessage() . '</p>';
    }
}

echo '</div>';

if (!$is_ajax) {
    // Incluir el archivo footer.php en views
    include 'footer.php';
}
?>