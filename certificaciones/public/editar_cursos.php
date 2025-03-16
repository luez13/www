<?php
// Incluir el archivo header.php en views
include '../views/header.php';

// Incluir el archivo model.php en config
include '../config/model.php';

$user_id = $_SESSION['user_id'];
$pagina_actual = 'editar_cursos.php'; // Definir la página actual 

// Verificar si el usuario es administrador
require_once '../controllers/autenticacion.php';
if (esPerfil3($user_id) || esPerfil4($user_id)) {
    // El usuario tiene permiso para ver esta página
} else {
    die('No tienes permiso para ver esta página.');
}

// Obtener el número de página actual
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Obtener todos los cursos con límite, desplazamiento y ordenados alfabéticamente
$db = new DB();
$stmt = $db->prepare("SELECT * FROM cursos.cursos ORDER BY nombre_curso ASC LIMIT :limit OFFSET :offset");
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total de cursos para la paginación
$stmt = $db->prepare("SELECT COUNT(*) FROM cursos.cursos");
$stmt->execute();
$total_cursos = $stmt->fetchColumn();
$total_pages = ceil($total_cursos / $limit);

function renderPagination($total_pages, $current_page, $pagina_actual) {
    $html = '<nav><ul class="pagination">';
    
    // Botón para la primera página
    if ($current_page > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="#" onclick="loadPage(\'' . $pagina_actual . '\', { page: 1 }); return false;">Primera</a></li>';
        $html .= '<li class="page-item"><a class="page-link" href="#" onclick="loadPage(\'' . $pagina_actual . '\', { page: ' . ($current_page - 1) . ' }); return false;">&laquo; Anterior</a></li>';
    }
    
    // Determinar el rango de páginas a mostrar
    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);
    
    // Ajustar si estamos cerca del principio o final
    if ($current_page <= 3) {
        $end_page = min(5, $total_pages);
    }
    if ($current_page >= $total_pages - 2) {
        $start_page = max(1, $total_pages - 4);
    }
    
    // Páginas numéricas
    for ($i = $start_page; $i <= $end_page; $i++) {
        $active = $i == $current_page ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '"><a class="page-link" href="#" onclick="loadPage(\'' . $pagina_actual . '\', { page: ' . $i . ' }); return false;">' . $i . '</a></li>';
    }
    
    // Botón para la última página
    if ($current_page < $total_pages) {
        $html .= '<li class="page-item"><a class="page-link" href="#" onclick="loadPage(\'' . $pagina_actual . '\', { page: ' . ($current_page + 1) . ' }); return false;">Siguiente &raquo;</a></li>';
        $html .= '<li class="page-item"><a class="page-link" href="#" onclick="loadPage(\'' . $pagina_actual . '\', { page: ' . $total_pages . ' }); return false;">Última</a></li>';
    }
    
    $html .= '</ul></nav>';
    return $html;
}

echo '<div class="accordion" id="accordionCursos">';
foreach ($cursos as $curso) {
    echo '<div class="accordion-item">';
    echo '<h2 class="accordion-header" id="heading' . $curso['id_curso'] . '">';
    echo '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse' . $curso['id_curso'] . '" aria-expanded="false" aria-controls="collapse' . $curso['id_curso'] . '">';
    echo 'Editar curso ' . $curso['nombre_curso'];
    echo '</button>';
    echo '</h2>';
    echo '<div id="collapse' . $curso['id_curso'] . '" class="accordion-collapse collapse" aria-labelledby="heading' . $curso['id_curso'] . '" data-bs-parent="#accordionCursos">';
    echo '<div class="accordion-body">';
    echo '<form id="editarCursoForm' . $curso['id_curso'] . '" action="../controllers/curso_controlador.php" method="post">';
    echo '<input type="hidden" name="action" value="editar">';
    echo '<input type="hidden" name="id_curso" value="' . $curso['id_curso'] . '">';
    
    echo '<div class="mb-3">';
    echo '<label for="nombre_curso' . $curso['id_curso'] . '" class="form-label">Nombre del curso</label>';
    echo '<input type="text" class="form-control" id="nombre_curso' . $curso['id_curso'] . '" name="nombre_curso" value="' . $curso['nombre_curso'] . '" required>';
    echo '</div>';
    
    echo '<div class="mb-3">';
    echo '<label for="descripcion' . $curso['id_curso'] . '" class="form-label">Descripción</label>';
    echo '<textarea class="form-control" id="descripcion' . $curso['id_curso'] . '" name="descripcion" required>' . $curso['descripcion'] . '</textarea>';
    echo '</div>';
    
    echo '<div class="mb-3">';
    echo '<label for="tiempo_asignado' . $curso['id_curso'] . '" class="form-label">Tiempo asignado (en semanas)</label>';
    echo '<input type="number" class="form-control" id="tiempo_asignado' . $curso['id_curso'] . '" name="tiempo_asignado" value="' . $curso['tiempo_asignado'] . '" min="1" required>';
    echo '</div>';

    echo '<div class="mb-3">';
    echo '<label for="inicio_mes' . $curso['id_curso'] . '" class="form-label">Inicio del mes</label>';
    echo '<input type="date" class="form-control" id="inicio_mes' . $curso['id_curso'] . '" name="inicio_mes" value="' . $curso['inicio_mes'] . '" required>';
    echo '</div>';

    echo '<div class="mb-3">';
    echo '<label for="horas_cronologicas' . $curso['id_curso'] . '" class="form-label">Horas cronológicas</label>';
    echo '<input type="number" class="form-control" id="horas_cronologicas' . $curso['id_curso'] . '" name="horas_cronologicas" value="' . $curso['horas_cronologicas'] . '" step="0.1" required>';
    echo '</div>';

    // echo '<div class="mb-3">';
    // echo '<label for="fecha_finalizacion' . $curso['id_curso'] . '" class="form-label">Fecha de finalización</label>';
    // echo '<input type="datetime-local" class="form-control" id="fecha_finalizacion' . $curso['id_curso'] . '" name="fecha_finalizacion" value="' . $curso['fecha_finalizacion'] . '" required>';
    // echo '</div>';

    echo '<div class="mb-3 d-flex align-items-center">';
    echo '<input type="checkbox" class="form-check-input me-2" id="firma_digital' . $curso['id_curso'] . '" name="firma_digital" ' . ($curso['firma_digital'] ? 'checked' : '') . '>';
    echo '<label for="firma_digital' . $curso['id_curso'] . '" class="form-check-label">Firma digital</label>';
    echo '</div>';    
    
    echo '<div class="mb-3">';
    echo '<label for="tipo_curso' . $curso['id_curso'] . '" class="form-label">Tipo de curso</label>';
    echo '<select class="form-select" id="tipo_curso' . $curso['id_curso'] . '" name="tipo_curso" required>';
    echo '<option value="masterclass"' . ($curso['tipo_curso'] == 'masterclass' ? ' selected' : '') . '>Masterclass</option>';
    echo '<option value="seminario"' . ($curso['tipo_curso'] == 'seminario' ? ' selected' : '') . '>Seminario</option>';
    echo '<option value="diplomado"' . ($curso['tipo_curso'] == 'diplomado' ? ' selected' : '') . '>Diplomado</option>';
    echo '<option value="congreso"' . ($curso['tipo_curso'] == 'congreso' ? ' selected' : '') . '>Congreso</option>';
    echo '<option value="charla"' . ($curso['tipo_curso'] == 'charla' ? ' selected' : '') . '>Charla</option>';
    echo '<option value="taller"' . ($curso['tipo_curso'] == 'taller' ? ' selected' : '') . '>Taller</option>';
    echo '<option value="curso"' . ($curso['tipo_curso'] == 'curso' ? ' selected' : '') . '>Curso</option>';
    echo '<option value="masterclass_rectoria"' . ($curso['tipo_curso'] == 'masterclass_rectoria' ? ' selected' : '') . '>Masterclass Rectoría</option>';
    echo '<option value="seminario_rectoria"' . ($curso['tipo_curso'] == 'seminario_rectoria' ? ' selected' : '') . '>Seminario Rectoría</option>';
    echo '<option value="diplomado_rectoria"' . ($curso['tipo_curso'] == 'diplomado_rectoria' ? ' selected' : '') . '>Diplomado Rectoría</option>';
    echo '<option value="congreso_rectoria"' . ($curso['tipo_curso'] == 'congreso_rectoria' ? ' selected' : '') . '>Congreso Rectoría</option>';
    echo '<option value="charla_rectoria"' . ($curso['tipo_curso'] == 'charla_rectoria' ? ' selected' : '') . '>Charla Rectoría</option>';
    echo '<option value="taller_rectoria"' . ($curso['tipo_curso'] == 'taller_rectoria' ? ' selected' : '') . '>Taller Rectoría</option>';
    echo '<option value="curso_rectoria"' . ($curso['tipo_curso'] == 'curso_rectoria' ? ' selected' : '') . '>Curso Rectoría</option>';
    echo '</select>';
    echo '</div>';    
    
    echo '<div class="mb-3">';
    echo '<label for="limite_inscripciones' . $curso['id_curso'] . '" class="form-label">Límite de inscripción</label>';
    echo '<input type="number" class="form-control" id="limite_inscripciones' . $curso['id_curso'] . '" name="limite_inscripciones" value="' . $curso['limite_inscripciones'] . '" required>';
    echo '</div>';
    
    echo '<div class="mb-3">';
    echo '<label for="promotor' . $curso['id_curso'] . '" class="form-label">Promotor</label>';
    echo '<select class="form-select" id="promotor' . $curso['id_curso'] . '" name="promotor">';
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
    echo '<textarea class="form-control" id="requerimientos_implemento' . $index . '" name="requerimientos_implementos" required>' . $curso['requerimientos_implemento'] . '</textarea>';
    echo '</div>';
    echo '<div class="mb-3">';
    echo '<label for="desempeno_al_concluir' . $index . '" class="form-label">Desempeño al concluir</label>';
    echo '<textarea class="form-control" id="desempeno_al_concluir' . $index . '" name="desempeño_al_concluir" required>' . $curso['desempeno_al_concluir'] . '</textarea>';
    echo '</div>';

    // Obtener y mostrar los módulos del curso
    echo '<h4>Módulos del curso</h4>';
    $stmt = $db->prepare("SELECT * FROM cursos.modulos WHERE id_curso = :curso_id");
    $stmt->execute([':curso_id' => $curso['id_curso']]);
    $modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($modulos as $index => $modulo) {
        echo '<h5>Editar módulo ' . $modulo['nombre_modulo'] . '</h5>';
        echo '<input type="hidden" name="id_modulo[]" value="' . $modulo['id_modulo'] . '">';
        
        echo '<div class="mb-3">';
        echo '<label for="nombre_modulo' . $index . '" class="form-label">Nombre del módulo</label>';
        echo '<input type="text" class="form-control" id="nombre_modulo' . $index . '" name="nombre_modulo[]" value="' . $modulo['nombre_modulo'] . '" required>';
        echo '</div>';
        
        echo '<div class="mb-3">';
        echo '<label for="contenido' . $index . '" class="form-label">Contenido</label>';
        // Dividir el contenido en partes y crear contenedores separados
        $contenidos = explode('][', trim($modulo['contenido'], '[]'));
        foreach ($contenidos as $key => $contenido) {
            echo '<textarea class="form-control" id="contenido' . $index . '_' . $key . '" name="contenido[]" required>' . $contenido . '</textarea>';
            // Añadir campos ocultos para el número y ID del módulo
            echo '<input type="hidden" name="numero_modulo_contenido[]" value="' . $modulo['numero'] . '">';
            echo '<input type="hidden" name="id_modulo_contenido[]" value="' . $modulo['id_modulo'] . '">';
        }        
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
    echo '<button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#detallesCursoModal' . $curso['id_curso'] . '">Ver Detalles del Curso</button>';
    echo '<button type="button" class="btn btn-success" onclick="loadPage(\'../controllers/buscar.php\', { id_curso: ' . $curso['id_curso'] . ' })">Agregar Usuarios</button>';
    echo '<input type="submit" class="btn btn-primary" value="Guardar cambios">';    
    echo '</div>';
    echo '</form>';
    echo '</div>'; // Cerrar accordion-body
    echo '</div>'; // Cerrar accordion-collapse
    echo '</div>'; // Cerrar accordion-item
}
echo '</div>'; // Cerrar accordion

// Renderizar la paginación
echo renderPagination($total_pages, $page, $pagina_actual);

// Incluir el archivo footer.php en views
include '../views/footer.php';
?>

<!-- Modal -->
<?php foreach ($cursos as $index => $curso): ?>
<div class="modal fade" id="detallesCursoModal<?= $curso['id_curso']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl"> <!-- Usar modal-fullscreen para hacer la modal lo más grande posible -->
  <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Detalles del Curso</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <iframe src="detalles_curso.php?id=<?php echo $curso['id_curso']; ?>" style="width: 100%; height: 80vh;" frameborder="0"></iframe> <!-- Ajustar altura a 80vh para ocupar el 80% de la altura de la ventana -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>

<!-- Modal para mostrar usuarios -->
<?php foreach ($cursos as $index => $curso): ?>
<div class="modal fade" id="agregarUsuarioModal<?= $curso['id_curso']; ?>" tabindex="-1" aria-labelledby="agregarUsuarioModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="agregarUsuarioModalLabel">Usuarios Registrados</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <iframe src="../controllers/buscar.php?id_curso=<?= $curso['id_curso']; ?>" width="100%" height="500px" frameborder="0"></iframe>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.editarCursoForm').forEach(function(form) {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            var formData = new FormData(form);

            fetch(form.action, {
                method: form.method,
                body: formData
            })
            .then(response => response.text())
            .then(result => {
                if (result.includes('El curso se ha editado correctamente')) {
                    alert('El curso se ha editado correctamente');
                    // Recargar la página actual con AJAX
                    var page = document.querySelector('.page-item.active .page-link').dataset.page;
                    loadPage('editar_cursos.php', { page: page });
                } else {
                    alert('Hubo un error al editar el curso: ' + result);
                }
            })
            .catch(error => {
                alert('Hubo un error al procesar la solicitud: ' + error);
            });
        });
    });

    // Manejar la navegación de la paginación
    document.querySelectorAll('.page-link-nav').forEach(function(link) {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            var page = link.dataset.page;
            loadPage('editar_cursos.php', { page: page });
        });
    });
});
</script>