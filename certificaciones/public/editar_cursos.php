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
    echo '<form id="editarCursoForm" action="../controllers/curso_controlador.php" method="post">';
    echo '<input type="hidden" name="action" value="editar">';
    echo '<input type="hidden" name="id_curso" value="' . $curso['id_curso'] . '">';
    echo '<p>Nombre del curso: <input type="text" name="nombre_curso" value="' . $curso['nombre_curso'] . '" required></p>';
    echo '<p>Descripción: <textarea name="descripcion" required>' . $curso['descripcion'] . '</textarea></p>';
    echo '<p>Tiempo asignado (en semanas): <input type="number" name="tiempo_asignado" value="' . $curso['tiempo_asignado'] . '" min="1" required></p>';
    echo '<p>Inicio del mes: <input type="date" name="inicio_mes" value="' . $curso['inicio_mes'] . '" required></p>';
    echo '<p>Tipo de curso: <select name="tipo_curso" required>';
    echo '<option value="seminarios"' . ($curso['tipo_curso'] == 'seminarios' ? ' selected' : '') . '>Seminarios</option>';
    echo '<option value="diplomados"' . ($curso['tipo_curso'] == 'diplomados' ? ' selected' : '') . '>Diplomados</option>';
    echo '<option value="congreso"' . ($curso['tipo_curso'] == 'congreso' ? ' selected' : '') . '>Congreso</option>';
    echo '<option value="charlas"' . ($curso['tipo_curso'] == 'charlas' ? ' selected' : '') . '>Charlas</option>';
    echo '<option value="talleres"' . ($curso['tipo_curso'] == 'talleres' ? ' selected' : '') . '>Talleres</option>';
    echo '</select></p>';
    echo '<p>Limite de inscripcion: <input type="number" name="limite_inscripciones" value="' . $curso['limite_inscripciones'] . '" required></p>';
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
    echo '<p>Días de clase: <textarea name="dias_clase" required>' . $curso['dias_clase'] . '</textarea></p>';
    echo '<p>Horario de inicio: <input type="time" name="horario_inicio" value="' . $curso['horario_inicio'] . '" required></p>';
    echo '<p>Horario de fin: <input type="time" name="horario_fin" value="' . $curso['horario_fin'] . '" required></p>';
    echo '<p>Nivel del curso: <input type="text" name="nivel_curso" value="' . $curso['nivel_curso'] . '" required></p>';
    echo '<p>Costo: <input type="number" name="costo" value="' . $curso['costo'] . '" step="0.01" required></p>';
    echo '<p>Conocimientos previos: <textarea name="conocimientos_previos" required>' . $curso['conocimientos_previos'] . '</textarea></p>';
    echo '<p>Requerimientos de implemento: <textarea name="requerimientos_implemento" required>' . $curso['requerimientos_implemento'] . '</textarea></p>';
    echo '<p>Desempeño al concluir: <textarea name="desempeno_al_concluir" required>' . $curso['desempeno_al_concluir'] . '</textarea></p>';

    // Obtener y mostrar los módulos del curso
    echo '<h4>Módulos del curso</h4>';
    $stmt = $db->prepare("SELECT * FROM cursos.modulos WHERE id_curso = :curso_id");
    $stmt->execute([':curso_id' => $curso['id_curso']]);
    $modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($modulos as $modulo) {
        echo '<h5>Editar módulo ' . $modulo['nombre_modulo'] . '</h5>';
        echo '<input type="hidden" name="id_modulo[]" value="' . $modulo['id_modulo'] . '">';
        echo '<p>Nombre del módulo: <input type="text" name="nombre_modulo[]" value="' . $modulo['nombre_modulo'] . '" required></p>';
        echo '<p>Contenido: <textarea name="contenido_modulo[]" required>' . $modulo['contenido'] . '</textarea></p>';
        echo '<p>Número del modulo: <input type="number" name="numero_modulo[]" value="' . $modulo['numero'] . '" required></p>';
        echo '<p>Actividad: <input type="text" name="actividad_modulo[]" value="' . $modulo['actividad'] . '" required></p>';
        echo '<p>Instrumento: <input type="text" name="instrumento_modulo[]" value="' . $modulo['instrumento'] . '" required></p>';
    }

    echo '<input type="submit" value="Guardar cambios">';
    echo '</form>';
}
echo '</div>';
// Incluir el archivo footer.php en views
include '../views/footer.php';
?>

<script>
document.getElementById('editarCursoForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Evitar el envío del formulario
    var form = event.target;
    var formData = new FormData(form);

    fetch(form.action, {
        method: form.method,
        body: formData
    })
    .then(response => response.text())
    .then(result => {
        if (result.includes('El curso se ha editado correctamente')) {
            alert('El curso se ha editado correctamente');
            window.location.href = '../public/perfil.php';
        } else {
            alert('Hubo un error al editar el curso: ' + result);
        }
    })
    .catch(error => {
        alert('Hubo un error al procesar la solicitud: ' + error);
    });
});
</script>