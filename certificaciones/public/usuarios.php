<?php
// Incluir el archivo header.php en views
include '../views/header.php';
include '../config/model.php';

$db = new DB();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Campo de búsqueda y botón
echo '<div class="input-group mb-3">';
echo '<input type="text" id="searchInput" class="form-control" placeholder="Buscar por cédula, nombre o apellido">';
echo '<button class="btn btn-primary" type="button" onclick="searchUsers()">Buscar</button>';
echo '</div>';
// Contenedor para los resultados de la búsqueda
echo '<div id="searchResults" class="accordion" id="accordionUsuarios"></div>';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'editar_perfil') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo = $_POST['correo'];
    $cedula = $_POST['cedula'];
    $id_rol = $_POST['id_rol']; // Obtener el id del rol seleccionado

    // Actualizar los datos del usuario
    $stmt = $db->prepare("UPDATE cursos.usuarios SET nombre = :nombre, apellido = :apellido, correo = :correo, cedula = :cedula, id_rol = :id_rol WHERE id = :id");
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':apellido', $apellido);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':cedula', $cedula);
    $stmt->bindParam(':id_rol', $id_rol); // Actualizar el rol del usuario
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    header('Location: ../public/usuarios.php'); // Redirige de nuevo a la página de usuarios
} else {
    // Obtener todos los usuarios y sus roles con límite, desplazamiento y ordenados alfabéticamente
    $stmt = $db->prepare("SELECT usuarios.*, roles.nombre_rol FROM cursos.usuarios INNER JOIN cursos.roles ON usuarios.id_rol = roles.id_rol ORDER BY usuarios.nombre ASC LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener todos los roles
    $stmt = $db->prepare("SELECT * FROM cursos.roles");
    $stmt->execute();
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->prepare("SELECT password FROM cursos.usuarios WHERE id = :id");
    $stmt->bindParam(':id', $usuario['id']);
    $stmt->execute();
    $contrasenaDesdeBD = $stmt->fetchColumn(); // Obtenemos la contraseña desde la base de datos;

    // Total de usuarios para la paginación
    $stmt = $db->prepare("SELECT COUNT(*) FROM cursos.usuarios");
    $stmt->execute();
    $total_usuarios = $stmt->fetchColumn();
    $total_pages = ceil($total_usuarios / $limit);

    echo '<div class="accordion" id="accordionUsuarios">';
    foreach ($usuarios as $index => $usuario) {
        echo '<div class="accordion-item list-group-item">'; // Añadir la clase list-group-item aquí
        echo '<h2 class="accordion-header" id="heading' . $index . '">';
        echo '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse' . $index . '" aria-expanded="false" aria-controls="collapse' . $index . '">';
        echo 'Editar usuario ' . $usuario['nombre'];
        echo '</button>';
        echo '</h2>';
        echo '<div id="collapse' . $index . '" class="accordion-collapse collapse" aria-labelledby="heading' . $index . '" data-bs-parent="#accordionUsuarios">';
        echo '<div class="accordion-body">';
        echo '<form id="editarUsuarioForm' . $index . '" class="editar-usuario-form" data-index="' . $index . '" action="../controllers/usuarios_controlador.php" method="post">';
        echo '<input type="hidden" name="action" value="editar_perfil">';
        echo '<input type="hidden" name="id" value="' . $usuario['id'] . '">';
        echo '<div class="mb-3">';
        echo '<label for="nombre' . $index . '" class="form-label">Nombre</label>';
        echo '<input type="text" class="form-control" id="nombre' . $index . '" name="nombre" value="' . $usuario['nombre'] . '">';
        echo '</div>';
        echo '<div class="mb-3">';
        echo '<label for="apellido' . $index . '" class="form-label">Apellido</label>';
        echo '<input type="text" class="form-control" id="apellido' . $index . '" name="apellido" value="' . $usuario['apellido'] . '">';
        echo '</div>';
        echo '<div class="mb-3">';
        echo '<label for="correo' . $index . '" class="form-label">Correo</label>';
        echo '<input type="text" class="form-control" id="correo' . $index . '" name="correo" value="' . $usuario['correo'] . '">';
        echo '</div>';
        echo '<div class="mb-3">';
        echo '<label for="cedula' . $index . '" class="form-label">Cédula</label>';
        echo '<input type="text" class="form-control" id="cedula' . $index . '" name="cedula" value="' . $usuario['cedula'] . '">';
        echo '</div>';
        if ($_SESSION['user_rol'] == 4) {
            echo '<div class="mb-3">';
            echo '<label for="nueva_contrasena' . $index . '" class="form-label">Nueva Contraseña</label>';
            echo '<input type="text" class="form-control" id="nueva_contrasena' . $index . '" name="nueva_contrasena">';
            echo '</div>';
        }
        echo '<div class="mb-3">';
        echo '<label for="id_rol' . $index . '" class="form-label">Rol</label>';
        echo '<select class="form-select" id="id_rol' . $index . '" name="id_rol">';
        foreach ($roles as $rol) {
            echo '<option value="' . $rol['id_rol'] . '"' . ($usuario['id_rol'] == $rol['id_rol'] ? ' selected' : '') . '>' . $rol['nombre_rol'] . '</option>';
        }
        echo '</select>';
        echo '</div>';
        echo '<input type="submit" class="btn btn-primary" value="Guardar cambios">';
        echo '</form>';
        echo '</div>'; // Cerrar accordion-body
        echo '</div>'; // Cerrar accordion-collapse
        echo '</div>'; // Cerrar accordion-item
    }
    echo '</div>'; // Cerrar accordion    
}

// Agregar la función de paginación al inicio del archivo
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

// Botón para abrir la ventana modal
$botonVerContrasena = '
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalContrasena">
        Ver Contraseña
    </button>
';

// Ventana modal
$ventanaModal = '
    <div class="modal fade" id="modalContrasena" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Contraseña del Usuario</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>La contraseña actual es: ' . $contrasenaDesdeBD . '</p>
                </div>
            </div>
        </div>
    </div>
';

// Total de usuarios para la paginación
$stmt = $db->prepare("SELECT COUNT(*) FROM cursos.usuarios");
$stmt->execute();
$total_usuarios = $stmt->fetchColumn();
$total_pages = ceil($total_usuarios / $limit);

// Renderizar la paginación
echo renderPagination($total_pages, $page, 'usuarios.php');

// Incluir el archivo footer.php en views
include '../views/footer.php';
?>

<script>
// Función para manejar el envío de formularios
function handleFormSubmission() {
    $('.editar-usuario-form').submit(function(event) {
        event.preventDefault();
        var form = $(this);
        var formData = form.serialize();

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.includes('El usuario se ha editado correctamente')) {
                    alert('El usuario se ha editado correctamente');
                } else {
                    alert('Hubo un error al editar el usuario: ' + response);
                }
            },
            error: function() {
                alert('Hubo un error al procesar la solicitud.');
            }
        });
    });
}

// Script para búsqueda en el servidor
function searchUsers() {
    var input = document.getElementById('searchInput').value.toLowerCase();
    console.log("Buscando: " + input);  // Agregar esta línea para depuración

    $.ajax({
        url: '../controllers/buscarUsuarios.php',
        type: 'POST',
        data: { search: input },
        success: function(response) {
            console.log("Resultados: " + response);  // Agregar esta línea para depuración
            document.getElementById('searchResults').innerHTML = response;
            reapplyEvents(); // Reaplicar eventos de JavaScript
        },
        error: function() {
            alert('Hubo un error al realizar la búsqueda.');
        }
    });
}

$(document).ready(function() {
    handleFormSubmission(); // Inicializa el manejo de formularios en la carga de la página
});
</script>