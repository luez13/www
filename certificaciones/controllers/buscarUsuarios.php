<?php
// controllers/buscarUsuarios.php

include '../config/model.php';

// --- 1. CONFIGURACIÓN Y OBTENCIÓN DE PARÁMETROS ---
$db = new DB();

// Recibimos los parámetros de la solicitud AJAX (usamos POST)
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$busqueda = isset($_POST['busqueda']) ? trim($_POST['busqueda']) : '';
$limit = 10;
$offset = ($page - 1) * $limit;

// --- 2. CONSTRUCCIÓN DE LA CONSULTA SQL ---
$params = [':limit' => $limit, ':offset' => $offset];
$whereClause = '';

if (!empty($busqueda)) {
    // Si hay un término de búsqueda, construimos la cláusula WHERE
    $whereClause = "WHERE u.nombre ILIKE :busqueda OR u.apellido ILIKE :busqueda OR u.cedula ILIKE :busqueda";
    // ILIKE es para búsqueda insensible a mayúsculas/minúsculas en PostgreSQL
    $params[':busqueda'] = "%$busqueda%";
}

// --- 3. EJECUCIÓN DE CONSULTAS ---

// Primero, contamos el total de resultados para la paginación
$total_stmt = $db->prepare("SELECT COUNT(u.id) FROM cursos.usuarios u $whereClause");
$total_stmt->execute(isset($params[':busqueda']) ? [':busqueda' => $params[':busqueda']] : []);
$total_usuarios = $total_stmt->fetchColumn();
$total_pages = ceil($total_usuarios / $limit);

// Segundo, obtenemos los datos de los usuarios para la página actual
$stmt = $db->prepare("
    SELECT u.*, r.nombre_rol 
    FROM cursos.usuarios u 
    INNER JOIN cursos.roles r ON u.id_rol = r.id_rol 
    $whereClause
    ORDER BY u.nombre ASC 
    LIMIT :limit OFFSET :offset
");
$stmt->execute($params);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtenemos todos los roles para los dropdowns de los formularios
$roles_stmt = $db->prepare("SELECT * FROM cursos.roles ORDER BY id_rol");
$roles_stmt->execute();
$roles = $roles_stmt->fetchAll(PDO::FETCH_ASSOC);


// --- 4. RENDERIZACIÓN DEL HTML ---

// Esta función generará los controles de paginación.
// La adaptamos para que llame a nuestra nueva función JS `loadUsers`.
function renderPagination($total_pages, $current_page, $busqueda) {
    if ($total_pages <= 1) return '';
    
    // Escapamos la búsqueda para que no rompa el string de JS
    $busqueda_js = htmlspecialchars($busqueda, ENT_QUOTES);
    $html = '<nav><ul class="pagination justify-content-center">';

    for ($i = 1; $i <= $total_pages; $i++) {
        $active = $i == $current_page ? 'active' : '';
        // Cada link llama a loadUsers con la página y el término de búsqueda actual
        $html .= "<li class='page-item $active'><a class='page-link' href='#' onclick='event.preventDefault(); loadUsers($i, \"$busqueda_js\");'>$i</a></li>";
    }

    $html .= '</ul></nav>';
    return $html;
}

// --- Generamos el HTML para la lista de usuarios (el acordeón) ---
$userHtml = '';
if (count($usuarios) > 0) {
    foreach ($usuarios as $index => $usuario) {
        $userHtml .= '<div class="accordion-item">';
        $userHtml .= '<h2 class="accordion-header" id="heading' . $usuario['id'] . '">';
        $userHtml .= '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse' . $usuario['id'] . '" aria-expanded="false" aria-controls="collapse' . $usuario['id'] . '">';
        $userHtml .= 'Editar: ' . htmlspecialchars($usuario['nombre']) . ' ' . htmlspecialchars($usuario['apellido']) . ' (Cédula: ' . htmlspecialchars($usuario['cedula']) . ')';
        $userHtml .= '</button></h2>';
        $userHtml .= '<div id="collapse' . $usuario['id'] . '" class="accordion-collapse collapse" aria-labelledby="heading' . $usuario['id'] . '" data-bs-parent="#user-list-container">';
        $userHtml .= '<div class="accordion-body">';
        
        // El formulario de edición es idéntico al que ya tenías
        $userHtml .= '<form class="editar-usuario-form" action="../controllers/usuarios_controlador.php" method="post">';
        $userHtml .= '<input type="hidden" name="id" value="' . $usuario['id'] . '">';
        // ... (campos del formulario: nombre, apellido, correo, cedula)
        $userHtml .= '<div class="mb-3"><label>Nombre</label><input type="text" class="form-control" name="nombre" value="' . htmlspecialchars($usuario['nombre']) . '"></div>';
        $userHtml .= '<div class="mb-3"><label>Apellido</label><input type="text" class="form-control" name="apellido" value="' . htmlspecialchars($usuario['apellido']) . '"></div>';
        $userHtml .= '<div class="mb-3"><label>Correo</label><input type="email" class="form-control" name="correo" value="' . htmlspecialchars($usuario['correo']) . '"></div>';
        $userHtml .= '<div class="mb-3"><label>Cédula</label><input type="text" class="form-control" name="cedula" value="' . htmlspecialchars($usuario['cedula']) . '"></div>';
        $userHtml .= '<div class="mb-3"><label>Título (Ej: Ing., Lic., TSU)</label><input type="text" class="form-control" name="titulo" value="' . htmlspecialchars($usuario['titulo'] ?? '') . '"></div>';
        $userHtml .= '<div class="mb-3"><label>Cargo</label><input type="text" class="form-control" name="cargo" value="' . htmlspecialchars($usuario['cargo'] ?? '') . '"></div>';

        // Campo para la nueva contraseña
        $userHtml .= '<div class="mb-3"><label>Nueva Contraseña (dejar en blanco para no cambiar)</label><input type="password" class="form-control" name="nueva_contrasena"></div>';

        // Dropdown para el rol
        $userHtml .= '<div class="mb-3"><label>Rol</label><select class="form-select" name="id_rol">';
        foreach ($roles as $rol) {
            $selected = ($usuario['id_rol'] == $rol['id_rol']) ? ' selected' : '';
            $userHtml .= '<option value="' . $rol['id_rol'] . '"' . $selected . '>' . htmlspecialchars($rol['nombre_rol']) . '</option>';
        }
        $userHtml .= '</select></div>';
        
        // Botones de acción
        $userHtml .= '<button type="submit" class="btn btn-primary" name="action" value="editar_perfil">Guardar Cambios</button>';
        $userHtml .= ' <button type="submit" class="btn btn-danger" name="action" value="eliminar_usuario">Eliminar Usuario</button>';
        $userHtml .= '</form>';

        $userHtml .= '</div></div></div>';
    }
} else {
    $userHtml = '<div class="alert alert-warning">No se encontraron usuarios que coincidan con la búsqueda.</div>';
}

// --- Generamos el HTML para la paginación ---
$paginationHtml = renderPagination($total_pages, $page, $busqueda);

// --- 5. DEVOLVEMOS UNA RESPUESTA JSON ---
// Devolver los dos bloques de HTML en un formato JSON es más limpio y robusto.
header('Content-Type: application/json');
echo json_encode([
    'userHtml' => $userHtml,
    'paginationHtml' => $paginationHtml
]);

?>