<?php
// Incluir el archivo header.php en views
include '../views/header.php';

// Incluir el archivo model.php en config
include '../config/model.php';

$db = new DB();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'editar_perfil') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo = $_POST['correo'];
    $cedula = $_POST['cedula'];
    $id_rol = $_POST['id_rol'];

    // Actualizar los datos del usuario
    $stmt = $db->prepare("UPDATE cursos.usuarios SET nombre = :nombre, apellido = :apellido, correo = :correo, cedula = :cedula, id_rol = :id_rol WHERE id = :id");
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':apellido', $apellido);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':cedula', $cedula);
    $stmt->bindParam(':id_rol', $id_rol);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    header('Location: ../public/usuarios.php');
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
    $pagination_html = '';

    // Calculate start and end pages for display
    $start_page = ($page <= 4) ? 1 : max(1, $page - 2);
    $end_page = ($page >= $total_pages - 2) ? $total_pages : min($total_pages, $page + 2);

    // Generate pagination links
    if ($total_pages > 0) {
        if ($page > 1) {
            $pagination_html .= '<li class="page-item"><a class="page-link page-link-nav" href="#" data-page="1">Primera</a></li>';
            $pagination_html .= '<li class="page-item"><a class="page-link page-link-nav" href="#" data-page="' . ($page - 1) . '">Anterior</a></li>';
        }

        for ($i = $start_page; $i <= $end_page; $i++) {
            $active_class = ($i == $page) ? 'active' : '';
            $pagination_html .= '<li class="page-item ' . $active_class . '"><a class="page-link page-link-nav" href="#" data-page="' . $i . '">' . $i . '</a></li>';
        }

        if ($page < $total_pages) {
            $pagination_html .= '<li class="page-item"><a class="page-link page-link-nav" href="#" data-page="' . ($page + 1) . '">Siguiente</a></li>';
            $pagination_html .= '<li class="page-item"><a class="page-link page-link-nav" href="#" data-page="' . $total_pages . '">Última</a></li>';
        }
    }

    echo '<div class="accordion" id="accordionUsuarios">';
    foreach ($usuarios as $index => $usuario) {
        echo '<div class="accordion-item">';
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
?>

<!-- Paginación -->
<nav aria-label="Page navigation example">
  <ul class="pagination justify-content-center">
    <?php if ($total_pages > 1) {
        echo $pagination_html;
    }?>
  </ul>
</nav>

<?php
// Incluir el archivo footer.php en views
include '../views/footer.php';
?>