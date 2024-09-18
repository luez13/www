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
echo '<div class="accordion" id="accordionCursos">';
foreach ($cursos as $index => $curso) {
    echo '<div class="accordion-item">';
    echo '<h2 class="accordion-header" id="heading' . $index . '">';
    echo '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse' . $index . '" aria-expanded="false" aria-controls="collapse' . $index . '">';
    echo 'Editar curso ' . $curso['nombre_curso'];
    echo '</button>';
    echo '</h2>';
    echo '<div id="collapse' . $index . '" class="accordion-collapse collapse" aria-labelledby="heading' . $index . '" data-bs-parent="#accordionCursos">';
    echo '<div class="accordion-body">';
    echo '<form id="editarCursoForm' . $index . '" action="../controllers/curso_controlador.php" method="post">';
    echo '<input type="hidden" name="action" value="editar">';
    echo '<input type="hidden" name="id_curso" value="' . $curso['id_curso'] . '">';
    echo '<div class="mb-3">';
    echo '<label for="nombre_curso' . $index . '" class="form-label">Nombre del curso</label>';
    echo '<input type="text" class="form-control" id="nombre_curso' . $index . '" name="nombre_curso" value="' . $curso['nombre_curso'] . '" required>';
    echo '</div>';
    echo '<div class="mb-3">';
    echo '<label for="descripcion' . $index . '" class="form-label">Descripción</label>';
    echo '<textarea class="form-control" id="descripcion' . $index . '" name="descripcion" required>' . $curso['descripcion'] . '</textarea>';
    echo '</div>';
    echo '<div class="mb-3">';
    echo '<label for="tiempo_asignado' . $index . '" class="form-label">Tiempo asignado (en semanas)</label>';
    echo '<input type="number" class="form-control" id="tiempo_asignado' . $index . '" name="tiempo_asignado" value="' . $curso['tiempo_asignado'] . '" min="1" required>';
    echo '</div>';
    echo '<div class="mb-3">';
    echo '<label for="inicio_mes' . $index . '" class="form-label">Inicio del mes</label>';
    echo '<input type="date" class="form-control" id="inicio_mes' . $index . '" name="inicio_mes" value="' . $curso['inicio_mes'] . '" required>';
    echo '</div>';
    echo '<div class="mb-3">';
    echo '<label for="tipo_curso' . $index . '" class="form-label">Tipo de curso</label>';
    echo '<select class="form-select" id="tipo_curso' . $index . '" name="tipo_curso" required>';
    echo '<option value="seminarios"' . ($curso['tipo_curso'] == 'seminarios' ? ' selected' : '') . '>Seminarios</option>';
    echo '<option value="diplomados"' . ($curso['tipo_curso'] == 'diplomados' ? ' selected' : '') . '>Diplomados</option>';
    echo '<option value="congreso"' . ($curso['tipo_curso'] == 'congreso' ? ' selected' : '') . '>Congreso</option>';
    echo '<option value="charlas"' . ($curso['tipo_curso'] == 'charlas' ? ' selected' : '') . '>Charlas</option>';
    echo '<option value="talleres"' . ($curso['tipo_curso'] == 'talleres' ? ' selected' : '') . '>Talleres</option>';
    echo '</select>';
    echo '</div>';
    echo '<div class="mb-3">';
    echo '<label for="limite_inscripciones' . $index . '" class="form-label">Límite de inscripción</label>';
    echo '<input type="number" class="form-control" id="limite_inscripciones' . $index . '" name="limite_inscripciones" value="' . $curso['limite_inscripciones'] . '" required>';
    echo '</div>';
    echo '<div class="mb-3">';
    echo '<label for="promotor' . $index . '" class="form-label">Promotor</label>';
    echo '<select class="form-select" id="promotor' . $index . '" name="promotor">';
    // Obtener todos los promotores
    $stmt = $db->prepare("SELECT id, nombre FROM cursos.usuarios WHERE id_rol = 2"); // Asumiendo que el rol 2 corresponde a los promotores
    $stmt->execute();
    $promotores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($promotores as $promotor) {
        echo '<option value="' . $promotor['id'] . '"' . ($curso['promotor'] == $promotor['id'] ? ' selected' : '') . '>ID ' . $promotor['id'] . ' Nombre ' . $promotor['nombre'] . '</option>';
    }
    echo '</select>';
    echo '</div>';
    if ($curso['autorizacion']) {
        // Obtener el nombre del usuario que autorizó el curso
        $stmt = $db->prepare("SELECT nombre FROM cursos.usuarios WHERE id = :id");
        $stmt->execute([':id' => $curso['autorizacion']]);
        $nombre_autorizador = $stmt->fetch(PDO::FETCH_ASSOC)['nombre'];
        echo '<div class="mb-3">';
        echo '<label class="form-label">Autorizado por</label>';
        echo '<p>ID ' . $curso['autorizacion'] . ' Nombre ' . $nombre_autorizador . '</p>';
        echo '</div>';
    } else {
        echo '<div class="mb-3 form-check">';
        echo '<input type="checkbox" class="form-check-input" id="autorizacion' . $index . '" name="autorizacion" value="' . $user_id . '">';
        echo '<label class="form-check-label" for="autorizacion' . $index . '">Autorización</label>';
        echo '</div>';
    }
    echo '<div class="mb-3">';
    echo '<label for="dias_clase' . $index . '" class="form-label">Días de clase</label>';
    echo '<textarea class="form-control" id="dias_clase' . $index . '" name="dias_clase" required>' . $curso['dias_clase'] . '</textarea>';
    echo '</div>';
    echo '<div class="mb-3">';
    echo '<label for="horario_inicio' . $index . '" class="form-label">Horario de inicio</label>';
    echo '<input type="time" class="form-control" id="horario_inicio' . $index . '" name="horario_inicio" value="' . $curso['horario_inicio'] . '" required>';
    echo '</div>';
    echo '<div class="mb-3">';
    echo '<label for="horario_fin' . $index . '" class="form-label">Horario de fin</label>';
    echo '<input type="time" class="form-control" id="horario_fin' . $index . '" name="horario_fin" value="' . $curso['horario_fin'] . '" required>';
    echo '</div>';
    echo '<div class="mb-3">';
    echo '<label for="nivel_curso' . $index . '" class="form-label">Nivel del curso</label>';
    echo '<input type="text" class="form-control" id="nivel_curso' . $index . '" name="nivel_curso" value="' . $curso['nivel_curso'] . '" required>';
    echo '</div>';
    echo '<div class="mb-3">';
    echo '<label for="costo' . $index . '" class="form-label">Costo</label>';
    echo '<input type="number" class="form-control" id="costo' . $index . '" name="costo" value="' . $curso['costo'] . '" step="0.01" required>';
    echo '</div>';
    echo '<div class="mb-3">';
    echo '<label for="conocimientos_previos' . $index . '" class="form-label">Conocimientos previos</label>';
        echo '<textarea class="form-control" id="conocimientos_previos' . $index . '" name="conocimientos_previos" required>' . $curso['conocimientos_previos'] . '</textarea>';
    echo '</div>';
    echo '<div class="mb-3">';
    echo '<label for="requerimientos_implemento' . $index . '" class="form-label">Requerimientos de implemento</label>';
    echo '<textarea class="form-control" id="requerimientos_implemento' . $index . '" name="requerimientos_implemento" required>' . $curso['requerimientos_implemento'] . '</textarea>';
    echo '</div>';
    echo '<div class="mb-3">';
    echo '<label for="desempeno_al_concluir' . $index . '" class="form-label">Desempeño al concluir</label>';
    echo '<textarea class="form-control" id="desempeno_al_concluir' . $index . '" name="desempeno_al_concluir" required>' . $curso['desempeno_al_concluir'] . '</textarea>';
    echo '</div>';

    // Obtener y mostrar los módulos del curso
    echo '<h4>Módulos del curso</h4>';
    $stmt = $db->prepare("SELECT * FROM cursos.modulos WHERE id_curso = :curso_id");
    $stmt->execute([':curso_id' => $curso['id_curso']]);
    $modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($modulos as $modulo) {
        echo '<h5>Editar módulo ' . $modulo['nombre_modulo'] . '</h5>';
        echo '<input type="hidden" name="id_modulo[]" value="' . $modulo['id_modulo'] . '">';
        echo '<div class="mb-3">';
        echo '<label for="nombre_modulo' . $index . '" class="form-label">Nombre del módulo</label>';
        echo '<input type="text" class="form-control" id="nombre_modulo' . $index . '" name="nombre_modulo[]" value="' . $modulo['nombre_modulo'] . '" required>';
        echo '</div>';
        echo '<div class="mb-3">';
        echo '<label for="contenido_modulo' . $index . '" class="form-label">Contenido</label>';
        echo '<textarea class="form-control" id="contenido_modulo' . $index . '" name="contenido_modulo[]" required>' . $modulo['contenido'] . '</textarea>';
        echo '</div>';
        echo '<div class="mb-3">';
        echo '<label for="numero_modulo' . $index . '" class="form-label">Número del módulo</label>';
        echo '<input type="number" class="form-control" id="numero_modulo' . $index . '" name="numero_modulo[]" value="' . $modulo['numero'] . '" required>';
        echo '</div>';
        echo '<div class="mb-3">';
        echo '<label for="actividad_modulo' . $index . '" class="form-label">Actividad</label>';
        echo '<input type="text" class="form-control" id="actividad_modulo' . $index . '" name="actividad_modulo[]" value="' . $modulo['actividad'] . '" required>';
        echo '</div>';
        echo '<div class="mb-3">';
        echo '<label for="instrumento_modulo' . $index . '" class="form-label">Instrumento</label>';
        echo '<input type="text" class="form-control" id="instrumento_modulo' . $index . '" name="instrumento_modulo[]" value="' . $modulo['instrumento'] . '" required>';
        echo '</div>';
    }

    echo '<div class="d-flex">';
    echo '<button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#detallesCursoModal' . $index . '">Ver Detalles del Curso</button>';
    echo '<input type="submit" class="btn btn-primary" value="Guardar cambios">';
    echo '</div>';
    echo '</form>';
    echo '</div>'; // Cerrar accordion-body
    echo '</div>'; // Cerrar accordion-collapse
    echo '</div>'; // Cerrar accordion-item
}
echo '</div>'; // Cerrar accordion
// Incluir el archivo footer.php en views
include '../views/footer.php';
?>

<!-- Modal -->
<?php foreach ($cursos as $index => $curso): ?>
<div class="modal fade" id="detallesCursoModal<?php echo $index; ?>" tabindex="-1" aria-labelledby="detallesCursoModalLabel<?php echo $index; ?>" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detallesCursoModalLabel<?php echo $index; ?>">Detalles del Curso</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <iframe src="detalles_curso.php?id=<?php echo $curso['id_curso']; ?>" style="width: 100%; height: 500px;" frameborder="0"></iframe>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>

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