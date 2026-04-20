<?php
// Incluir el archivo model.php en config
include '../config/model.php';

// Crear una instancia de la clase DB
$db = new DB();

$pagina_actual = 'buscar.php'; // Definir la página actual

// Asegurar que existe el token CSRF para la carga masiva
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ... (La función validar_inscripcion no cambia) ...
function validar_inscripcion($id_usuario, $curso_id)
{
    if (empty($id_usuario) || empty($curso_id)) {
        return false;
    }
    if (!is_numeric($id_usuario) || !is_numeric($curso_id)) {
        return false;
    }
    return true;
}

$message = '';
$type = '';
// El manejo de la solicitud POST no cambia en absoluto.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = isset($_POST['id_usuario']) ? $_POST['id_usuario'] : null;
    $curso_id = isset($_POST['curso_id']) ? $_POST['curso_id'] : null;
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'actualizar_fecha' && validar_inscripcion($id_usuario, $curso_id)) {
        $fecha_inscripcion = isset($_POST['fecha_inscripcion']) ? $_POST['fecha_inscripcion'] : null;
        if ($fecha_inscripcion) {
            try {
                $stmt = $db->prepare('UPDATE cursos.certificaciones SET fecha_inscripcion = :fecha_inscripcion WHERE id_usuario = :id_usuario AND curso_id = :curso_id');
                $stmt->execute(['fecha_inscripcion' => $fecha_inscripcion, 'id_usuario' => $id_usuario, 'curso_id' => $curso_id]);
                $message = "Fecha de inscripción actualizada correctamente.";
                $type = "success";
            } catch (PDOException $e) {
                $message = "Ha ocurrido un error al actualizar la fecha de inscripción: " . $e->getMessage();
                $type = "danger";
            }
        } else {
            $message = "La fecha de inscripción no puede estar vacía.";
            $type = "warning";
        }
    } elseif ($action === 'inscribirse' && validar_inscripcion($id_usuario, $curso_id)) {
        $stmt = $db->prepare('SELECT * FROM cursos.certificaciones WHERE id_usuario = :id_usuario AND curso_id = :curso_id');
        $stmt->execute(['id_usuario' => $id_usuario, 'curso_id' => $curso_id]);
        $inscripcion = $stmt->fetch();

        if ($inscripcion) {
            $message = "Ya estás inscrito en este curso.";
            $type = "warning";
        } else {
            $valor_unico = hash('sha256', $id_usuario . $curso_id . time());
            try {
                $stmt = $db->prepare('INSERT INTO cursos.certificaciones (id_usuario, curso_id, valor_unico, fecha_inscripcion, completado) VALUES (:id_usuario, :curso_id, :valor_unico, NOW(), false)');
                $stmt->execute(['id_usuario' => $id_usuario, 'curso_id' => $curso_id, 'valor_unico' => $valor_unico]);
                $message = "Te has inscrito correctamente en el curso.";
                $type = "success";
            } catch (PDOException $e) {
                $message = "Ha ocurrido un error al inscribirte en el curso: " . $e->getMessage();
                $type = "danger";
            }
        }
    } elseif ($action === 'cancelar_inscripcion' && validar_inscripcion($id_usuario, $curso_id)) {
        try {
            $stmt = $db->prepare('DELETE FROM cursos.certificaciones WHERE id_usuario = :id_usuario AND curso_id = :curso_id');
            $stmt->execute(['id_usuario' => $id_usuario, 'curso_id' => $curso_id]);
            $message = "Inscripción cancelada correctamente.";
            $type = "success";
        } catch (PDOException $e) {
            $message = "Ha ocurrido un error al cancelar la inscripción del curso: " . $e->getMessage();
            $type = "danger";
        }
    } else {
        $message = "Datos de inscripción inválidos.";
        $type = "danger";
    }
}

// --- CAMBIOS PARA LA BÚSQUEDA Y PAGINACIÓN ---
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$id_curso = isset($_GET['id_curso']) ? (int) $_GET['id_curso'] : 0;
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : ''; // <-- CAMBIO: Obtenemos el término de búsqueda
$limit = 10;
$offset = ($page - 1) * $limit;

// La lógica para obtener el nombre del curso no cambia
$curso = ['nombre_curso' => 'Curso no encontrado'];
if ($id_curso > 0) {
    $stmt = $db->prepare('SELECT nombre_curso FROM cursos.cursos WHERE id_curso = :id_curso');
    $stmt->execute(['id_curso' => $id_curso]);
    $curso = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$curso) {
        $curso = ['nombre_curso' => 'Curso no encontrado'];
    }
}

$total_pages = 0;
$usuarios = [];
try {
    // --- CAMBIO: La consulta para contar ahora también usa la búsqueda ---
    $count_sql = "SELECT COUNT(*) FROM cursos.usuarios u";
    $params = [];
    $whereClause = '';
    if (!empty($busqueda)) {
        $whereClause = " WHERE u.nombre ILIKE :busqueda OR u.apellido ILIKE :busqueda OR u.cedula ILIKE :busqueda";
        $params[':busqueda'] = "%$busqueda%";
    }
    $stmt_count = $db->prepare($count_sql . $whereClause);
    $stmt_count->execute($params);
    $total = $stmt_count->fetchColumn();
    $total_pages = ceil($total / $limit);

    // --- CAMBIO: La consulta principal ahora también filtra por la búsqueda ---
    $sql = "
        SELECT u.id, u.nombre, u.apellido, u.cedula, u.correo, 
               CASE WHEN c.id_usuario IS NOT NULL THEN 1 ELSE 0 END AS inscrito
        FROM cursos.usuarios u
        LEFT JOIN cursos.certificaciones c ON u.id = c.id_usuario AND c.curso_id = :id_curso
    ";

    // Añadimos la cláusula WHERE de búsqueda a la consulta principal
    $sql .= $whereClause;

    $sql .= " ORDER BY inscrito DESC, u.nombre ASC LIMIT :limit OFFSET :offset";

    $stmt = $db->prepare($sql);

    // Unimos los parámetros
    $final_params = array_merge($params, [
        ':id_curso' => $id_curso,
        ':limit' => $limit,
        ':offset' => $offset
    ]);

    // Bindeamos los parámetros dinámicamente
    foreach ($final_params as $key => &$val) {
        $type = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindParam($key, $val, $type);
    }

    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
    die();
}

// --- CAMBIO: La función de paginación ahora necesita el término de búsqueda ---
function renderPagination($total_pages, $current_page, $pagina_actual, $id_curso, $busqueda)
{
    $html = '<nav><ul class="pagination">';
    $busqueda_js = htmlspecialchars($busqueda, ENT_QUOTES); // Escapamos para JavaScript

    if ($current_page > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="#" onclick="loadUserList(1); return false;">Primera</a></li>';
        $html .= '<li class="page-item"><a class="page-link" href="#" onclick="loadUserList(' . ($current_page - 1) . '); return false;">&laquo; Anterior</a></li>';
    }

    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);
    if ($current_page <= 3) {
        $end_page = min(5, $total_pages);
    }
    if ($current_page >= $total_pages - 2) {
        $start_page = max(1, $total_pages - 4);
    }

    for ($i = $start_page; $i <= $end_page; $i++) {
        $active = $i == $current_page ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '"><a class="page-link" href="#" onclick="loadUserList(' . $i . '); return false;">' . $i . '</a></li>';
    }

    if ($current_page < $total_pages) {
        $html .= '<li class="page-item"><a class="page-link" href="#" onclick="loadUserList(' . ($current_page + 1) . '); return false;">Siguiente &raquo;</a></li>';
        $html .= '<li class="page-item"><a class="page-link" href="#" onclick="loadUserList(' . $total_pages . '); return false;">Última</a></li>';
    }

    $html .= '</ul></nav>';
    return $html;
}

$is_ajax_list = isset($_GET['ajax_list']) && $_GET['ajax_list'] == 1;

if (!$is_ajax_list):
    ?>

    <!DOCTYPE html>
    <html lang="es">

    <body>
        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <div class="container-fluid mt-4" id="page-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="h3 text-gray-800">Inscripciones: <?= htmlspecialchars($curso['nombre_curso']) ?> <small
                        class="text-muted">(ID: <?= htmlspecialchars($id_curso) ?>)</small></h3>
                <button class="btn btn-secondary btn-sm" onclick="loadPage('../public/editar_cursos.php')"><i
                        class="fas fa-arrow-left me-1"></i> Volver a Cursos</button>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Buscador de Participantes</h6>
                    <?php if (!empty($usuarios)): ?>
                        <div class="d-flex">
                            <button type="button" class="btn btn-sm btn-success shadow-sm mr-2" data-toggle="modal"
                                data-target="#massEnrollModal">
                                <i class="fas fa-file-import fa-sm text-white-50"></i> Carga Masiva (CSV)
                            </button>
                            <button type="button" class="btn btn-sm btn-info shadow-sm"
                                onclick="loadPage('../controllers/generar_certificados_lote.php', { curso_id: <?= htmlspecialchars($id_curso); ?> })">
                                <i class="fas fa-download fa-sm text-white-50"></i> Certificados PDF
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="input-group">
                        <input type="text" id="custom-inscripcion-search" class="form-control bg-light border-0 small"
                            placeholder="Buscar por nombre, apellido o cédula..." value="<?= htmlspecialchars($busqueda) ?>"
                            data-id-curso="<?= htmlspecialchars($id_curso) ?>">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button"
                                onclick="$('#custom-inscripcion-search').trigger('keyup')">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                        <?php if (!empty($busqueda)): ?>
                            <div class="input-group-append">
                                <button class="btn btn-danger" type="button"
                                    onclick="loadPage('buscar.php', { id_curso: <?= $id_curso ?>, busqueda: '', page: 1 })">
                                    <i class="fas fa-times fa-sm"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php if ($message): ?>
                <div class="alert alert-<?= $type; ?>" role="alert">
                    <?= $message; ?>
                </div>
            <?php endif; ?>

            <div id="user-list-wrapper">
            <?php endif; // !$is_ajax_list ?>

            <div id="user-list">
                <div class="row">
                    <?php foreach ($usuarios as $usuario): ?>
                        <div class="col-xl-4 col-md-6 mb-4" id="user-<?= htmlspecialchars($usuario['id']); ?>">
                            <div
                                class="card border-left-<?= $usuario['inscrito'] ? 'success' : 'secondary' ?> shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div
                                                class="text-xs font-weight-bold text-<?= $usuario['inscrito'] ? 'success' : 'secondary' ?> text-uppercase mb-1">
                                                <?= $usuario['inscrito'] ? 'Inscrito' : 'No Inscrito' ?>
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= htmlspecialchars($usuario['nombre']) . ' ' . htmlspecialchars($usuario['apellido']); ?>
                                            </div>
                                            <div class="text-xs mt-1 text-muted">
                                                <i class="fas fa-id-card me-1"></i>
                                                <?= htmlspecialchars($usuario['cedula']); ?><br>
                                                <i class="fas fa-envelope me-1"></i>
                                                <?= htmlspecialchars($usuario['correo']); ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                    <hr>
                                    <?php
                                    $stmt = $db->prepare('SELECT * FROM cursos.certificaciones WHERE id_usuario = :id_usuario AND curso_id = :curso_id');
                                    $stmt->execute(['id_usuario' => $usuario['id'], 'curso_id' => $id_curso]);
                                    $inscripcion = $stmt->fetch();

                                    // Nueva consulta para obtener la fecha de inscripción
                                    $stmt_fecha = $db->prepare('SELECT fecha_inscripcion, valor_unico FROM cursos.certificaciones WHERE id_usuario = :id_usuario AND curso_id = :curso_id');
                                    $stmt_fecha->execute(['id_usuario' => $usuario['id'], 'curso_id' => $id_curso]);
                                    $fecha_info = $stmt_fecha->fetch(PDO::FETCH_ASSOC);
                                    $fecha_inscripcion_db = $fecha_info ? $fecha_info['fecha_inscripcion'] : '';
                                    $fecha_para_input = '';
                                    if ($fecha_inscripcion_db) {
                                        // Tomamos solo la parte de la fecha (los primeros 10 caracteres)
                                        $fecha_para_input = substr($fecha_inscripcion_db, 0, 10);
                                    }
                                    $valor_unico = $fecha_info ? htmlspecialchars($fecha_info['valor_unico']) : '';
                                    ?>
                                    <?php if ($inscripcion): ?>
                                        <form id="inscripcionForm-<?= htmlspecialchars($usuario['id']); ?>"
                                            action="../controllers/buscar.php" method="post" class="mb-2">
                                            <input type="hidden" name="action" value="cancelar_inscripcion">
                                            <input type="hidden" name="id_usuario"
                                                value="<?= htmlspecialchars($usuario['id']); ?>">
                                            <input type="hidden" name="curso_id" value="<?= htmlspecialchars($id_curso); ?>">
                                            <input type="hidden" name="page" value="<?= htmlspecialchars($page); ?>">
                                            <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                                onclick="inscribirUsuario(<?= htmlspecialchars($usuario['id']); ?>)"><i
                                                    class="fas fa-times-circle me-1"></i> Cancelar Inscripción</button>
                                        </form>
                                        <form id="fechaForm-<?= htmlspecialchars($usuario['id']); ?>"
                                            action="../controllers/buscar.php" method="post" class="mt-2"
                                            onsubmit="event.preventDefault();">
                                            <input type="hidden" name="action" value="actualizar_fecha">
                                            <input type="hidden" name="id_usuario"
                                                value="<?= htmlspecialchars($usuario['id']); ?>">
                                            <input type="hidden" name="curso_id" value="<?= htmlspecialchars($id_curso); ?>">
                                            <input type="hidden" name="page" value="<?= htmlspecialchars($page); ?>">
                                            <div class="input-group input-group-sm">
                                                <input type="date" class="form-control" name="fecha_inscripcion"
                                                    value="<?= htmlspecialchars($fecha_para_input) ?>">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-success" title="Guardar Fecha"
                                                        onclick="actualizarFechaUsuario(<?= htmlspecialchars($usuario['id']); ?>)"><i
                                                            class="fas fa-save"></i></button>
                                                </div>
                                            </div>
                                        </form>
                                        <?php if ($valor_unico): ?>
                                            <div class="text-center mt-2">
                                                <a href="../controllers/generar_certificado.php?valor_unico=<?= $valor_unico ?>"
                                                    class="btn btn-info btn-sm shadow-sm" target="_blank"><i
                                                        class="fas fa-certificate me-1"></i> PDF</a>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <form id="inscripcionForm-<?= htmlspecialchars($usuario['id']); ?>"
                                            action="../controllers/buscar.php" method="post" class="mt-2">
                                            <input type="hidden" name="action" value="inscribirse">
                                            <input type="hidden" name="id_usuario"
                                                value="<?= htmlspecialchars($usuario['id']); ?>">
                                            <input type="hidden" name="curso_id" value="<?= htmlspecialchars($id_curso); ?>">
                                            <input type="hidden" name="page" value="<?= htmlspecialchars($page); ?>">
                                            <button type="button" class="btn btn-primary btn-sm w-100 shadow-sm"
                                                onclick="inscribirUsuario(<?= htmlspecialchars($usuario['id']); ?>)"><i
                                                    class="fas fa-plus-circle me-1"></i> Agregar al Curso</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php
                // Asegúrate de que `$id_curso` se pase en cada enlace de paginación
                echo renderPagination($total_pages, $page, 'buscar.php', $id_curso, $busqueda);
                ?>
            </div>

            <?php if (!$is_ajax_list): ?>
            </div> <!-- /user-list-wrapper -->
        </div>

        <!-- MODAL: CARGA MASIVA DE INSCRIPCIONES -->
        <div class="modal fade" id="massEnrollModal" tabindex="-1" role="dialog" aria-labelledby="massEnrollModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="massEnrollModalLabel"><i class="fas fa-file-csv mr-2"></i>Inscripción Masiva vía CSV</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="massEnrollForm">
                        <div class="modal-body">
                            <div class="alert alert-info small">
                                <strong><i class="fas fa-info-circle"></i> Instrucciones:</strong>
                                <ul class="mb-0">
                                    <li>El archivo debe ser <strong>.CSV</strong>.</li>
                                    <li>Debe incluir columnas para: <strong>Cédula, Nombre, Apellido</strong>.</li>
                                    <li>Si el usuario no existe, se creará automáticamente.</li>
                                    <li>Los duplicados se ignoran dinámicamente.</li>
                                </ul>
                            </div>
                            <div class="form-group">
                                <label for="csv_file_enroll">Seleccionar Archivo CSV:</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="csv_file_enroll" accept=".csv" required>
                                    <label class="custom-file-label" for="csv_file_enroll">Elegir archivo...</label>
                                </div>
                            </div>
                            <input type="hidden" id="csrf_token_mass" value="<?= isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '' ?>">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-success" id="btnSubmitMass">
                                <i class="fas fa-cloud-upload-alt"></i> Procesar Carga
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            // Actualizar etiqueta del archivo al seleccionar
            $(document).on('change', '.custom-file-input', function (e) {
                var fileName = $(this).val().split('\\').pop();
                $(this).siblings('.custom-file-label').addClass("selected").html(fileName);
            });

            document.addEventListener('DOMContentLoaded', function () {
                // Función para desplazarse a un elemento específico después de cargar la página
                var params = new URLSearchParams(window.location.search);
                if (params.has('scrollTo')) {
                    var elementId = params.get('scrollTo');
                    var element = document.getElementById(elementId);
                    if (element) {
                        element.scrollIntoView();
                    }
                }
            });

            // Almacenar id curso para búsquedas ajax
            var cursoId = <?= json_encode($id_curso) ?>;
            var currentPage = <?= json_encode($page) ?>;

            function loadUserList(page) {
                currentPage = page;
                var busqueda = $('#custom-inscripcion-search').val();

                $('#user-list-wrapper').css('opacity', '0.5');

                $.ajax({
                    url: '../controllers/buscar.php',
                    type: 'GET',
                    data: { id_curso: cursoId, busqueda: busqueda, page: page, ajax_list: 1 },
                    success: function (response) {
                        $('#user-list-wrapper').html(response);
                        $('#user-list-wrapper').css('opacity', '1');
                    },
                    error: function () {
                        $('#user-list-wrapper').css('opacity', '1');
                    }
                });
            }

            $(document).ready(function () {
                var customSearchTimeout;

                $('#custom-inscripcion-search').off('keyup').on('keyup', function (e) {
                    var busqueda = $(this).val();
                    clearTimeout(customSearchTimeout);

                    customSearchTimeout = setTimeout(function () {
                        loadUserList(1);
                    }, 300);
                });

                // MANEJO DE CARGA MASIVA
                $('#massEnrollForm').on('submit', function(e) {
                    e.preventDefault();
                    
                    var fileData = $('#csv_file_enroll').prop('files')[0];
                    if (!fileData) return;

                    var formData = new FormData();
                    formData.append('csv_file', fileData);
                    formData.append('curso_id', cursoId);
                    formData.append('csrf_token', $('#csrf_token_mass').val());

                    $('#btnSubmitMass').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

                    $.ajax({
                        url: '../controllers/importar_inscripciones_ajax.php',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        success: function(res) {
                            $('#massEnrollModal').modal('hide');
                            $('#btnSubmitMass').prop('disabled', false).html('<i class="fas fa-cloud-upload-alt"></i> Procesar Carga');

                            if (res.error) {
                                Swal.fire('Error', res.error, 'error');
                            } else {
                                var msg = `Se procesaron ${res.procesados} filas.\n` +
                                          `- Nuevos inscritos: ${res.nuevos_inscritos}\n` +
                                          `- Ya estaban inscritos: ${res.ya_existentes}`;
                                
                                if (res.count_errores > 0) {
                                    msg += `\n- Errores: ${res.count_errores}`;
                                    console.log("Detalle de errores:", res.errores);
                                }

                                Swal.fire({
                                    title: 'Carga Completada',
                                    text: msg,
                                    icon: res.count_errores > 0 ? 'warning' : 'success',
                                    confirmButtonText: 'Aceptar'
                                }).then(() => {
                                    loadUserList(1);
                                });
                            }
                        },
                        error: function(xhr) {
                            $('#btnSubmitMass').prop('disabled', false).html('<i class="fas fa-cloud-upload-alt"></i> Procesar Carga');
                            Swal.fire('Error crítico', 'No se pudo comunicar con el servidor.', 'error');
                        }
                    });
                });
            });
        </script>
    </body>

    </html>
<?php endif; ?>
