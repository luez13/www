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

// Fetch active courses for modal
$stmt_cursos = $db->prepare("SELECT id_curso, nombre_curso, descripcion, estado, autorizacion FROM cursos.cursos ORDER BY id_curso DESC");
$stmt_cursos->execute();
$cursos = $stmt_cursos->fetchAll(PDO::FETCH_ASSOC);

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
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h3 class="h3 mb-2 text-gray-800"><i class="fas fa-users-cog me-2 text-primary"></i>Verificación de Usuarios
        </h3>
        <button id="abrir-modal-btn" class="btn btn-primary shadow-sm mb-2">
            <i class="fas fa-user-plus me-1"></i> Inscribir Seleccionados
        </button>
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
                        value="<?= htmlspecialchars($busqueda); ?>">
                    <button class="btn btn-primary" type="submit"
                        onclick="$('#user-list-container').load('../views/usuarios.php?busqueda=' + encodeURIComponent($('#busqueda-input').val()));">Buscar</button>
                    <?php if (!empty($busqueda)): ?>
                        <button class="btn btn-outline-secondary" type="button"
                            onclick="$('#user-list-container').load('../views/usuarios.php');"><i class="fas fa-times"></i>
                            Limpiar</button>
                    <?php endif; ?>
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
                            <th scope="col" class="text-center" style="width: 50px;">Sel</th>
                            <th scope="col">Usuario</th>
                            <th scope="col">Cédula</th>
                            <th scope="col">Contacto</th>
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
                                <tr onclick="toggleCheckbox(this, event)">
                                    <td class="text-center" style="cursor: pointer;">
                                        <div class="form-check d-flex justify-content-center m-0">
                                            <input class="form-check-input usuario-checkbox" type="checkbox"
                                                data-id="<?= htmlspecialchars($usuario['id']); ?>"
                                                style="transform: scale(1.3); cursor: pointer;"
                                                onclick="event.stopPropagation(); actualizarContadorModal();">
                                        </div>
                                    </td>
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

    <!-- Modal para seleccionar curso -->
    <div class="modal fade" id="seleccionarCursoModal" tabindex="-1" aria-labelledby="seleccionarCursoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title" id="seleccionarCursoModalLabel"><i
                            class="fas fa-graduation-cap me-2"></i>Inscribir en Curso</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center">
                        <i class="fas fa-info-circle fa-2x me-3"></i>
                        <div>
                            Selecciona el curso al que deseas inscribir a los <strong><span id="countSelectedBadge"
                                    class="badge bg-primary rounded-pill px-2">0</span></strong> usuarios marcados.
                        </div>
                    </div>

                    <div class="input-group mb-3 shadow-sm rounded">
                        <span class="input-group-text bg-white border-end-0"><i
                                class="fas fa-search text-muted"></i></span>
                        <input type="text" id="buscar-curso-input" class="form-control border-start-0 ps-0"
                            placeholder="Buscar curso para inscribir...">
                    </div>

                    <div class="form-floating shadow-sm rounded">
                        <select id="curso-id" class="form-select border-0" style="height: 60px;">
                            <?php foreach ($cursos as $curso): ?>
                                <?php
                                $statusTxt = $curso['estado'] ? 'Activo' : 'Inac.';
                                $authTxt = $curso['autorizacion'] ? 'Aut.' : 'Pend.';
                                ?>
                                <option value="<?= htmlspecialchars($curso['id_curso']); ?>">
                                    <?= htmlspecialchars($curso['nombre_curso']); ?> [<?= $statusTxt ?>][<?= $authTxt ?>]
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label for="curso-id">Curso Destino</label>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary text-dark bg-white border"
                        data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="inscribir-usuarios-btn" class="btn btn-primary px-4 shadow-sm"><i
                            class="fas fa-user-plus me-1"></i> Inscribir a Todos</button>
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

    // Filtrar los cursos en tiempo real dentro del modal
    document.getElementById('buscar-curso-input').addEventListener('input', function () {
        var filter = this.value.toLowerCase();
        var options = document.getElementById('curso-id').getElementsByTagName('option');
        for (var i = 0; i < options.length; i++) {
            var option = options[i];
            var optionText = option.textContent.toLowerCase();
            if (optionText.indexOf(filter) > -1) {
                option.style.display = "";
            } else {
                option.style.display = "none";
            }
        }
    });

    // Seleccionar todos los checkboxes
    document.getElementById('selectAllUsers').addEventListener('change', function () {
        var isChecked = this.checked;
        document.querySelectorAll('.usuario-checkbox').forEach(function (checkbox) {
            checkbox.checked = isChecked;
        });
        actualizarContadorModal();
    });

    // Actualizar contador del modal
    function actualizarContadorModal() {
        var count = document.querySelectorAll('.usuario-checkbox:checked').length;
        document.getElementById('countSelectedBadge').textContent = count;

        // Sincronizar el "Seleccionar Todos"
        var total = document.querySelectorAll('.usuario-checkbox').length;
        var selectAll = document.getElementById('selectAllUsers');
        if (selectAll && total > 0) {
            selectAll.checked = (count === total);
        }
    }

    // Escuchar cambios en checkboxes individuales (usado por el onclick en linea también)
    document.querySelectorAll('.usuario-checkbox').forEach(function (checkbox) {
        checkbox.addEventListener('change', actualizarContadorModal);
    });

    // Manejar el botón para abrir el modal
    document.getElementById('abrir-modal-btn').addEventListener('click', function () {
        actualizarContadorModal();
        var count = document.querySelectorAll('.usuario-checkbox:checked').length;
        if (count === 0) {
            alert('Por favor, selecciona al menos un usuario de la lista para inscribir.');
            return;
        }
        var modal = new bootstrap.Modal(document.getElementById('seleccionarCursoModal'));
        modal.show();
    });

    // Manejar la paginación con AJAX o recarga
    $('.pagination-link').on('click', function (e) {
        e.preventDefault();
        var page = $(this).data('page');
        var busqueda = $('#busqueda-input').val();

        // Animacion sencilla de carga
        $('#user-list-container').css('opacity', '0.5');

        if ($('#user-list-container').length) {
            $('#user-list-container').load('../views/usuarios.php?page=' + page + '&busqueda=' + encodeURIComponent(busqueda), function () {
                $('#user-list-container').css('opacity', '1');
            });
        } else {
            window.location.href = '?page=' + page + '&busqueda=' + encodeURIComponent(busqueda);
        }
    });

    // Manejar el botón para inscribir usuarios seleccionados
    document.getElementById('inscribir-usuarios-btn').addEventListener('click', function () {
        var selectedUsers = [...document.querySelectorAll('.usuario-checkbox:checked')].map(cb => cb.dataset.id);
        if (selectedUsers.length > 0) {
            var cursoId = document.getElementById('curso-id').value;

            if (!cursoId) {
                alert("Debes seleccionar un curso de destino.");
                return;
            }

            var btn = this;
            var originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Inscribiendo...';
            btn.disabled = true;

            $.ajax({
                url: '../controllers/usuarios_controlador.php',
                method: 'POST',
                data: {
                    action: 'inscribir_usuarios',
                    usuarios: selectedUsers,
                    curso_id: cursoId
                },
                success: function (response) {
                    alert('Acción completada con éxito. Los usuarios han sido inscritos en el curso seleccionado.');

                    var modalEl = document.getElementById('seleccionarCursoModal');
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();

                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').css('padding-right', '');

                    if ($('#user-list-container').length) {
                        $('#user-list-container').load('../views/usuarios.php?page=<?= $page ?>&busqueda=<?= urlencode($busqueda) ?>');
                    } else {
                        location.reload();
                    }
                },
                error: function () {
                    alert('Hubo un error de validación o del servidor al registrar los usuarios. Verifica que no estén inscritos previamente.');
                },
                complete: function () {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            });
        }
    });
</script>