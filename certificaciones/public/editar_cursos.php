<?php
// Incluir el archivo header.php en views
include '../views/header.php';

// Incluir el archivo model.php en config
include '../config/model.php';

$user_id = $_SESSION['user_id'];

// Verificar si el usuario es administrador
require_once '../controllers/autenticacion.php';
if (esPerfil3($user_id) || esPerfil4($user_id)) {
    // El usuario tiene permiso para ver esta página
} else {
    die('No tienes permiso para ver esta página.');
}
// Obtener todos los cursos
$db = new DB();
$stmt = $db->prepare("SELECT * FROM cursos.cursos");
$stmt->execute();
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo '<div class="main-content">';
foreach ($cursos as $curso) {
    // Mostrar los datos del curso en campos de entrada
    echo '<h3>Editar curso ' . $curso['nombre_curso'] . '</h3>';
    echo '<form action="../controllers/curso_controlador.php" method="post">';
    echo '<input type="hidden" name="action" value="editar">';
    echo '<input type="hidden" name="id_curso" value="' . $curso['id_curso'] . '">';
    echo '<p>Nombre del curso: <input type="text" name="nombre_curso" value="' . $curso['nombre_curso'] . '" required></p>';
    echo '<p>Descripción: <textarea name="descripcion" required>' . $curso['descripcion'] . '</textarea></p>';
    echo '<p>Duración (en días): <input type="number" name="duracion" value="' . $curso['duracion'] . '" min="1" required></p>';
    echo '<p>Periodo (fecha de inicio): <input type="date" name="periodo" value="' . $curso['periodo'] . '" min="1" required></p>';
    echo '<p>Modalidad: <select name="modalidad" required>';
    echo '<option value="Presencial"' . ($curso['modalidad'] == 'Presencial' ? ' selected' : '') . '>Presencial</option>';
    echo '<option value="Virtual"' . ($curso['modalidad'] == 'Virtual' ? ' selected' : '') . '>Virtual</option>';
    echo '<option value="Mixto"' . ($curso['modalidad'] == 'Mixto' ? ' selected' : '') . '>Mixto</option>';
    echo '</select></p>';
    echo '<p>Tipo de evaluación: ';
    echo '<input type="radio" id="calificacion" name="tipo_evaluacion" value="true"' . ($curso['tipo_evaluacion'] ? ' checked' : '') . ' required>';
    echo '<label for="calificacion">Calificacion</label>';
    echo '<input type="radio" id="participacion" name="tipo_evaluacion" value="false"' . (!$curso['tipo_evaluacion'] ? ' checked' : '') . ' required>';
    echo '<label for="participacion">Participacion</label>';
    echo '</p>';;
    echo '<p>Tipo de curso: <select name="tipo_curso" required>';
    echo '<option value="seminarios"' . ($curso['tipo_curso'] == 'seminarios' ? ' selected' : '') . '>Seminarios</option>';
    echo '<option value="diplomados"' . ($curso['tipo_curso'] == 'diplomados' ? ' selected' : '') . '>Diplomados</option>';
    echo '<option value="congreso"' . ($curso['tipo_curso'] == 'congreso' ? ' selected' : '') . '>Congreso</option>';
    echo '<option value="charlas"' . ($curso['tipo_curso'] == 'charlas' ? ' selected' : '') . '>Charlas</option>';
    echo '<option value="talleres"' . ($curso['tipo_curso'] == 'talleres' ? ' selected' : '') . '>Talleres</option>';
    echo '</select></p>';
    echo '<p>Límite de inscripciones: <input type="number" name="limite_inscripciones" value="' . $curso['limite_inscripciones'] . '" min="1" required></p>';
    echo '<p>Estado (curso en progreso si esta marcada la casilla, inactivo si no esta marcada la casilla): <input type="checkbox" id="estado" name="estado" value="true"' . ($curso['estado'] ? ' checked' : '') . '></p>';
    echo '<p>Promotor: <select name="promotor">';
    // Obtener todos los promotores
    $stmt = $db->prepare("SELECT id, nombre FROM cursos.usuarios"); // Asumiendo que el rol 4 corresponde a los promotores
    $stmt->execute();
    $promotores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($promotores as $promotor) {
        echo '<option value="' . $promotor['id'] . '"' . ($curso['promotor'] == $promotor['id'] ? ' selected' : '') . '>ID ' . $promotor['id'] . ' Nombre ' . $promotor['nombre'] . '</option>';
    }
    echo '</select></p>';
    if ($curso['autorizacion']) {
        // Obtener el nombre del usuario que autorizó el curso
        $stmt = $db->prepare("SELECT nombre FROM cursos.usuarios WHERE id = :id");
        $stmt->execute([':id' => $curso['autorizacion']]);
        $nombre_autorizador = $stmt->fetch(PDO::FETCH_ASSOC)['nombre'];
        echo '<p>Autorizado por: ID ' . $curso['autorizacion'] . ' Nombre ' . $nombre_autorizador . '</p>';
    } else {
        echo '<input type="hidden" id="autorizacion" name="autorizacion" value="no">';
        echo '<p>Autorización: <input type="checkbox" id="autorizacion" name="autorizacion" value="' . $user_id . '"></p>';
    }
    echo '<input type="submit" value="Guardar cambios">';
    echo '</form>';
}
echo '</div>';
// Incluir el archivo footer.php en views
include '../views/footer.php';
?>