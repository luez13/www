<?php
// views/usuarios.php
include '../config/model.php';

$db = new DB();

// Get search query
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Base query for counting and selecting
$sql_base = "FROM cursos.usuarios";
$params = [];

if (!empty($busqueda)) {
    $sql_base .= " WHERE nombre ILIKE :busqueda OR apellido ILIKE :busqueda OR cedula ILIKE :busqueda OR correo ILIKE :busqueda";
    $params[':busqueda'] = "%$busqueda%";
}

// Count total users
$stmt_total = $db->prepare("SELECT COUNT(*) " . $sql_base);
foreach ($params as $key => $val) {
    $stmt_total->bindValue($key, $val);
}
$stmt_total->execute();
$total_usuarios = $stmt_total->fetchColumn();
$total_pages = ceil($total_usuarios / $limit);

// Fetch users
$sql_users = "SELECT id, nombre, apellido, cedula, correo " . $sql_base . " ORDER BY id DESC LIMIT :limit OFFSET :offset";
$stmt_usuarios = $db->prepare($sql_users);
foreach ($params as $key => $val) {
    $stmt_usuarios->bindValue($key, $val);
}
$stmt_usuarios->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt_usuarios->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt_usuarios->execute();
$usuarios = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC);

// Fetch roles for edit modal
$stmt_roles = $db->prepare("SELECT id_rol, nombre_rol FROM cursos.roles ORDER BY id_rol ASC");
$stmt_roles->execute();
$roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);

function renderPaginationUsuarios($total_pages, $current_page, $busqueda)
{
    if ($total_pages <= 1)
        return '';
    $html = '<nav aria-label="Navegación de páginas"><ul class="pagination justify-content-center flex-wrap m-0">';

    // First / Prev
    if ($current_page > 1) {
        $html .= '<li class="page-item"><a class="page-link pagination-link" href="#" data-page="1" title="Primera"><i class="fas fa-angle-double-left"></i></a></li>';
        $html .= '<li class="page-item"><a class="page-link pagination-link" href="#" data-page="' . ($current_page - 1) . '" title="Anterior"><i class="fas fa-angle-left"></i></a></li>';
    }

    // Pages sliding window
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);

    if ($start > 1) {
        $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }

    for ($i = $start; $i <= $end; $i++) {
        $active = $i == $current_page ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '"><a class="page-link pagination-link" href="#" data-page="' . $i . '">' . $i . '</a></li>';
    }

    if ($end < $total_pages) {
        $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }

    // Next / Last
    if ($current_page < $total_pages) {
        $html .= '<li class="page-item"><a class="page-link pagination-link" href="#" data-page="' . ($current_page + 1) . '" title="Siguiente"><i class="fas fa-angle-right"></i></a></li>';
        $html .= '<li class="page-item"><a class="page-link pagination-link" href="#" data-page="' . $total_pages . '" title="Última"><i class="fas fa-angle-double-right"></i></a></li>';
    }

    $html .= '</ul></nav>';
    return $html;
}
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h3 class="h3 mb-2 text-gray-800"><i class="fas fa-users-cog me-2 text-primary"></i>Verificación de Usuarios
        </h3>
        <div class="d-flex gap-2 mb-2 flex-wrap">
            <button type="button" class="btn btn-success shadow-sm" data-toggle="modal" data-target="#modalImportarCSV">
                <i class="fas fa-file-csv me-1"></i> Importar CSV
            </button>
        </div>
    </div>

    <!-- Buscador -->
    <div class="card shadow mb-4 border-0">
        <div class="card-body p-3 bg-light rounded">
            <form id="buscar-usuarios-form" method="GET" action="javascript:void(0);" class="w-100 mb-0">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-primary"><i
                            class="fas fa-search"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" name="busqueda" id="busqueda-input"
                        placeholder="Buscar por nombre, apellido, cédula o correo..."
                        value="<?= htmlspecialchars($busqueda); ?>" onkeyup="buscarUsuariosDinamico(event)">
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Usuarios -->
    <div class="card shadow mb-4 border-0">
        <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Listado de Usuarios (<?= $total_usuarios ?> resultados)</h6>
            <div class="form-check form-switch m-0">
                <input class="form-check-input" type="checkbox" id="selectAllUsers">
                <label class="form-check-label text-muted small" for="selectAllUsers">Seleccionar Todos</label>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Usuario</th>
                            <th scope="col">Cédula</th>
                            <th scope="col">Contacto</th>
                            <th scope="col" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fas fa-search fa-3x mb-3 text-gray-300 d-block"></i>
                                    <h5>No se encontraron resultados</h5>
                                    <p class="small">Intenta ajustar tu búsqueda para encontrar más usuarios.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usuarios as $usuario): ?>
                                <?php
                                $n = !empty($usuario['nombre']) ? $usuario['nombre'] : '?';
                                $a = !empty($usuario['apellido']) ? $usuario['apellido'] : '';
                                $iniciales = mb_strtoupper(mb_substr($n, 0, 1) . mb_substr($a, 0, 1));
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3 shadow-sm"
                                                style="width: 45px; height: 45px; font-weight: bold; font-size: 1.1rem;">
                                                <?= $iniciales ?>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">
                                                    <?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?>
                                                </h6>
                                                <small class="text-muted">ID: #<?= $usuario['id'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border p-2"><i
                                                class="fas fa-id-card me-1 text-secondary"></i>
                                            <?= htmlspecialchars($usuario['cedula']); ?></span>
                                    </td>
                                    <td>
                                        <a href="mailto:<?= htmlspecialchars($usuario['correo']); ?>"
                                            class="text-decoration-none text-primary fw-medium"
                                            onclick="event.stopPropagation();">
                                            <i class="fas fa-envelope me-1"></i> <?= htmlspecialchars($usuario['correo']); ?>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button class="btn btn-sm btn-outline-primary shadow-sm" title="Editar Usuario"
                                                onclick="event.stopPropagation(); abrirModalEdicion(<?= htmlspecialchars($usuario['id']); ?>)">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger shadow-sm btn-delete-usuario"
                                                title="Eliminar Usuario" data-id="<?= htmlspecialchars($usuario['id']); ?>">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if ($total_pages > 1): ?>
            <div class="card-footer bg-light py-3 border-top-0">
                <?= renderPaginationUsuarios($total_pages, $page, $busqueda) ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal para Editar Usuario -->
    <div class="modal fade" id="editarUsuarioModal" tabindex="-1" aria-labelledby="editarUsuarioModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white border-0">
                    <h5 class="modal-title" id="editarUsuarioModalLabel"><i class="fas fa-user-edit me-2"></i> Editar
                        Usuario</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <form id="formEditarUsuario" onsubmit="guardarUsuarioAjax(event)">
                    <div class="modal-body p-4 bg-light">
                        <input type="hidden" id="edit_id_usuario" name="id_usuario">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Nombres</label>
                                <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Apellidos</label>
                                <input type="text" class="form-control" id="edit_apellido" name="apellido" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Cédula</label>
                                <input type="text" class="form-control" id="edit_cedula" name="cedula" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Correo Electrónico</label>
                                <input type="email" class="form-control" id="edit_correo" name="correo" required>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h6 class="fw-bold text-primary mb-3">Seguridad y Acceso</h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Rol en el Sistema</label>
                                <select class="form-select" id="edit_id_rol" name="id_rol" required>
                                    <?php foreach ($roles as $rol): ?>
                                        <option value="<?= htmlspecialchars($rol['id_rol']) ?>">
                                            <?= htmlspecialchars($rol['nombre_rol']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Nueva Contraseña
                                    <small>(Opcional)</small></label>
                                <input type="password" class="form-control" id="edit_nueva_password"
                                    name="nueva_password" placeholder="Dejar en blanco para no cambiar">
                            </div>
                        </div>

                        <hr class="my-4">
                        <h6 class="fw-bold text-success mb-3">Datos Universitarios (Facilitadores/Promotores)</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Título Institucional</label>
                                <input type="text" class="form-control" id="edit_titulo" name="titulo"
                                    placeholder="Ej: Lcdo. MgSc.">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Cargo</label>
                                <input type="text" class="form-control" id="edit_cargo" name="cargo"
                                    placeholder="Dejar vacío si aplica base">
                            </div>
                            <div class="col-12 mt-3">
                                <label class="form-label small fw-bold text-muted">Firma Digital (Imagen
                                    JPG/PNG)</label>
                                <input class="form-control" type="file" id="edit_firma_digital" name="firma_digital"
                                    accept="image/png, image/jpeg, image/jpg">
                                <div class="form-text mt-2"><i class="fas fa-info-circle"></i> Sube una nueva imagen
                                    transparente de la firma si deseas reemplazar la actual. Necesaria para promotores
                                    para que sus cursos generen constancias.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 py-3">
                        <button type="button" class="btn btn-secondary shadow-sm" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm" id="btnGuardarCambios"><i
                                class="fas fa-save me-1"></i> Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Importar CSV -->
    <div class="modal fade" id="modalImportarCSV" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-file-csv me-2"></i>Importación Masiva de Usuarios</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body bg-light">
                    <div class="row align-items-center mb-4">
                        <div class="col-md-7">
                            <p class="mb-1"><strong>Paso 1:</strong> Descarga la plantilla CSV y llénala con los datos
                                correspondientes.</p>
                            <p class="mb-0 text-muted small">La contraseña será auto-generada (NombreApellido20). El
                                correo,
                                de no proveerse, también será generado automáticamente.</p>
                        </div>
                        <div class="col-md-5 text-md-end mt-2 mt-md-0">
                            <a href="../public/plantillas/plantilla_usuarios.csv" download
                                class="btn btn-outline-success border-2 shadow-sm">
                                <i class="fas fa-download me-1"></i> Descargar Plantilla
                            </a>
                        </div>
                    </div>
                    <hr>
                    <div class="row align-items-center mb-4">
                        <div class="col-md-7">
                            <p class="mb-1"><strong>Paso 2:</strong> Sube el archivo completado para su validación.</p>
                            <form id="formCargarCSV">
                                <div class="input-group">
                                    <input type="file" class="form-control" id="archivoCSV" accept=".csv" required>
                                    <button class="btn btn-primary" type="submit" id="btnProcesarCSV"><i
                                            class="fas fa-upload me-1"></i> Procesar</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Vista Previa de la Importación -->
                    <div id="vistaPreviaCSV" style="display:none;">
                        <h6 class="font-weight-bold text-primary mb-3">Vista Previa de Importación</h6>
                        <div class="alert alert-warning d-none" id="alertaErroresCSV"></div>
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-dark sticky-top">
                                    <tr>
                                        <th>Cédula</th>
                                        <th>Nombres</th>
                                        <th>Apellidos</th>
                                        <th>Correo Asignado</th>
                                        <th>Contraseña Asignada</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaVistaPreviaCSV">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success px-4 disabled" id="btnConfirmarImportacion"><i
                            class="fas fa-check-circle me-1"></i> Confirmar Importación</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleCheckbox(row, event) {
        // Obviar clics en enlaces o en el propio checkbox para no disparar dos veces
        if (event.target.tagName !== 'INPUT' && event.target.tagName !== 'A') {
            const checkbox = row.querySelector('.usuario-checkbox');
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                actualizarContadorModal();
            }
        }
    }

    // Manejar la paginación con AJAX o recarga
    $(document).off('click', '.pagination-link').on('click', '.pagination-link', function (e) {
        e.preventDefault();
        var page = $(this).data('page');
        var busqueda = $('#busqueda-input').val();
        loadPage('../views/usuarios.php', { page: page, busqueda: busqueda });
    });



    // Búsqueda dinámica con Debounce
    let timeoutBusquedaUsuarios;
    function buscarUsuariosDinamico(event) {
        clearTimeout(timeoutBusquedaUsuarios);
        timeoutBusquedaUsuarios = setTimeout(function () {
            loadPage('../views/usuarios.php', { busqueda: $('#busqueda-input').val() });
        }, 500); // Esperar medio segundo después de que deja de escribir
    }

    // --- LÓGICA DE EDICIÓN DE USUARIOS ---
    function abrirModalEdicion(id) {
        // Obtenemos los datos del usuario mediante AJAX
        $.ajax({
            url: '../controllers/obtener_datos_usuario_ajax.php',
            type: 'GET',
            data: { id: id },
            dataType: 'json',
            success: function (user) {
                // Poblamos los campos del modal
                document.getElementById('edit_id_usuario').value = user.id;
                document.getElementById('edit_nombre').value = user.nombre || '';
                document.getElementById('edit_apellido').value = user.apellido || '';
                document.getElementById('edit_cedula').value = user.cedula || '';
                document.getElementById('edit_correo').value = user.correo || '';
                document.getElementById('edit_id_rol').value = user.id_rol;
                document.getElementById('edit_nueva_password').value = ''; // Siempre limpiar contraseña

                // Campos de autoridades
                document.getElementById('edit_titulo').value = user.titulo || '';
                document.getElementById('edit_cargo').value = user.cargo || '';

                // Limpiar el validador de archivo de firma por seguridad
                document.getElementById('edit_firma_digital').value = '';

                // Abrimos el modal
                var modal = new bootstrap.Modal(document.getElementById('editarUsuarioModal'));
                modal.show();
            },
            error: function () {
                alert('No se pudieron recuperar los datos del usuario. Intente nuevamente.');
            }
        });
    }

    function guardarUsuarioAjax(event) {
        event.preventDefault();
        var form = document.getElementById('formEditarUsuario');
        var formData = new FormData(form);

        var btn = document.getElementById('btnGuardarCambios');
        var originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        btn.disabled = true;

        $.ajax({
            url: '../controllers/admin_usuarios_ajax.php',
            type: 'POST',
            data: formData,
            contentType: false, // Requerido para FormData con archivos
            processData: false, // Requerido para FormData con archivos
            success: function (response) {
                alert(response);

                // Ocultar modal y recargar la lista
                var modalEl = document.getElementById('editarUsuarioModal');
                var modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('padding-right', '');

                loadPage('../views/usuarios.php', { page: <?= $page ?>, busqueda: '<?= addslashes($busqueda) ?>' });
            },
            error: function (xhr) {
                alert('Ocurrió un error al guardar los cambios: ' + xhr.responseText);
            },
            complete: function () {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    }

    // --- LÓGICA IMPORTACIÓN CSV ---
    let usuariosConfirmadosCSV = [];

    document.getElementById('formCargarCSV').addEventListener('submit', function (e) {
        e.preventDefault();
        let fileInput = document.getElementById('archivoCSV');
        if (!fileInput.files.length) return;

        let btn = document.getElementById('btnProcesarCSV');
        let originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';
        btn.disabled = true;

        let formData = new FormData();
        formData.append('csv_file', fileInput.files[0]);

        $.ajax({
            url: '../controllers/importar_usuarios_ajax.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function (res) {
                $('#vistaPreviaCSV').fadeIn();
                let tbody = document.getElementById('tablaVistaPreviaCSV');
                tbody.innerHTML = '';
                usuariosConfirmadosCSV = [];
                let errores = 0;

                res.forEach(row => {
                    let tr = document.createElement('tr');
                    let icon = row.valido ? '<i class="fas fa-check-circle text-success"></i> Válido' : `<i class="fas fa-times-circle text-danger"></i> ${row.error}`;
                    tr.className = row.valido ? 'table-success' : 'table-danger';

                    if (row.valido) {
                        usuariosConfirmadosCSV.push(row);
                    } else {
                        errores++;
                    }

                    tr.innerHTML = `
                        <td>${row.cedula || ''}</td>
                        <td>${row.nombre || ''}</td>
                        <td>${row.apellido || ''}</td>
                        <td>${row.correo || ''}</td>
                        <td>${row.password || ''}</td>
                        <td class="small fw-bold">${icon}</td>
                    `;
                    // Prepend errors so they show at the top
                    if (row.valido) tbody.appendChild(tr);
                    else tbody.insertBefore(tr, tbody.firstChild);
                });

                let btnConfirmar = document.getElementById('btnConfirmarImportacion');
                if (usuariosConfirmadosCSV.length > 0) {
                    btnConfirmar.classList.remove('disabled');
                    if (errores > 0) {
                        $('#alertaErroresCSV').html(`<strong>Atención:</strong> Encontramos <b>${errores}</b> filas con errores provocados por duplicidad. Solo los <b>${usuariosConfirmadosCSV.length}</b> registros válidos serán procesados.`).removeClass('d-none');
                    } else {
                        $('#alertaErroresCSV').addClass('d-none');
                    }
                } else {
                    btnConfirmar.classList.add('disabled');
                    $('#alertaErroresCSV').html(`<strong>Error:</strong> Ninguna fila en el archivo es válida para importar.`).removeClass('d-none');
                }
            },
            error: function () {
                alert('Error crítico al procesar el archivo CSV.');
            },
            complete: function () {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    });

    document.getElementById('btnConfirmarImportacion').addEventListener('click', function () {
        if (usuariosConfirmadosCSV.length === 0) return;
        if (!confirm(`¿Estás completamente seguro de importar estos ${usuariosConfirmadosCSV.length} usuarios a la base de datos?`)) return;

        let btn = this;
        let originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        btn.disabled = true;

        $.ajax({
            url: '../controllers/procesar_importacion_usuarios.php',
            type: 'POST',
            data: { usuarios: JSON.stringify(usuariosConfirmadosCSV) },
            success: function (res) {
                alert(res);
                location.reload();
            },
            error: function () {
                alert('Error al intentar guardar los usuarios en la base de datos.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    });

        // Lógica para el Botón de Eliminar en la tabla (Doble Verificación)
    $(document).off('click', '.btn-delete-usuario').on('click', '.btn-delete-usuario', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const userId = $(this).data('id');

        // Primera Validación
        if (confirm("¿Está seguro de que desea eliminar este usuario? Esta acción es irreversible.")) {
            // Segunda Validación
            if (confirm("⚠️ ¡ADVERTENCIA FINAL! ⚠️\n\n¿Estás ABSOLUTAMENTE SEGURO de eliminar al usuario? Se perderá todo su historial en el sistema.")) {
                
                // Mostrar estado de carga en el botón
                const originalHtml = $(this).html();
                $(this).html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

                $.ajax({
                    url: '../controllers/usuarios_controlador.php',
                    type: 'POST',
                    data: {
                        action: 'eliminar_usuario',
                        id: userId
                    },
                    success: function (response) {
                        alert("Usuario eliminado correctamente.");
                        // Recargar la lista
                        var page = $('.pagination .active .page-link').text() || 1;
                        var busqueda = $('#busqueda-input').val();
                        loadPage('../views/usuarios.php', { page: page, busqueda: busqueda });
                    },
                    error: function (xhr) {
                        alert('Error al intentar eliminar el usuario: ' + xhr.responseText);
                        // Restaurar botón
                        $(`.btn-delete-usuario[data-id="${userId}"]`).html(originalHtml).prop('disabled', false);
                    }
                });
            }
        }
    });
</script>