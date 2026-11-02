<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/model.php';
if (!function_exists('tieneAcceso')) {
    require_once '../config/auth_helper.php';
}
require_once '../controllers/autenticacion.php';

// Validar acceso: Roles 3, 4 (ADMIN), 5 (ANALISTA), 6 y 7 (SUPER_ADMIN)
if (!isset($_SESSION['user_id']) || !tieneAcceso([3, 4, 5, 6])) {
    echo '<div class="alert alert-danger m-3">Acceso denegado. No tienes permisos para operar la Taquilla.</div>';
    exit;
}

$id_extension_admin = isset($_SESSION['id_extension']) ? (int)$_SESSION['id_extension'] : 1;

// Obtener cuentas bancarias y cursos exclusivos del departamento (extensión) activo
$db = new DB();
$pdo = $db->getConn();

$nombre_sede_activa = !empty($_SESSION['es_academico']) ? 'Formación Permanente' : 'Postgrado / General';
try {
    $stmtSede = $pdo->prepare("SELECT nombre_extension FROM cursos.extensiones WHERE id_extension = :id_ext LIMIT 1");
    $stmtSede->execute([':id_ext' => $id_extension_admin]);
    $rowSede = $stmtSede->fetchColumn();
    if (!empty($rowSede)) {
        $nombre_sede_activa = $rowSede;
    }
} catch (Exception $e) {
    // Fallback al nombre por defecto
}

$stmtCuentas = $pdo->prepare("SELECT id_cuenta, banco, titular, tipo_cuenta, numero_cuenta FROM cursos.cuentas_bancarias WHERE id_extension = :id_ext AND activo = true ORDER BY id_cuenta DESC");
$stmtCuentas->execute([':id_ext' => $id_extension_admin]);
$cuentas_sede = $stmtCuentas->fetchAll(PDO::FETCH_ASSOC);

// Precargar exclusivamente los cursos/conceptos del departamento activo con su ID
$stmtCursos = $pdo->prepare("SELECT id_curso, nombre_curso, tipo_curso FROM cursos.cursos WHERE id_extension = :id_ext ORDER BY id_curso DESC");
$stmtCursos->execute([':id_ext' => $id_extension_admin]);
$todos_los_cursos = $stmtCursos->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800"><i class="fas fa-cash-register text-primary me-2"></i> Taquilla Física / Caja</h2>
        <span class="badge bg-primary fs-6">Departamento Activo: <?= htmlspecialchars($nombre_sede_activa) ?></span>
    </div>

    <!-- Buscador de Estudiante -->
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">1. Buscar o Registrar Estudiante</h6>
        </div>
        <div class="card-body">
            <form id="buscarEstudianteForm" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Cédula del Estudiante</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                        <input type="text" class="form-control" id="cedula_buscar" placeholder="Ej: V-12345678" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Buscar</button>
                </div>
            </form>
            
            <div id="estudianteInfo" class="mt-4" style="display:none;">
                <!-- Aquí se mostrará la info del estudiante o el formulario JIT -->
            </div>
        </div>
    </div>

    <!-- Formulario de Pago (Inicialmente oculto) -->
    <div class="card shadow mb-4 border-left-success" id="moduloPago" style="display:none;">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-success">2. Registrar Pago en Taquilla</h6>
        </div>
        <div class="card-body">
            <form id="registrarPagoForm" enctype="multipart/form-data">
                <input type="hidden" id="id_usuario_pago" name="id_usuario">
                <input type="hidden" name="action" value="registrar_pago_taquilla">
                
                <div class="row mb-3">
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Estudiante seleccionado:</label>
                        <input type="text" class="form-control bg-light" id="nombre_estudiante_pago" readonly>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label fw-bold">Concepto / Curso a pagar (Buscador por ID o Nombre):</label>
                        <select class="form-select select2-taquilla" name="id_curso" id="select_curso" style="width: 100%;" required>
                            <option value="">-- Busque por ID o Nombre del Concepto / Curso --</option>
                            <?php foreach ($todos_los_cursos as $c): ?>
                                <option value="<?= (int)$c['id_curso'] ?>">
                                    [ID: <?= (int)$c['id_curso'] ?>] - <?= htmlspecialchars($c['nombre_curso']) ?> (<?= htmlspecialchars(!empty($c['tipo_curso']) ? $c['tipo_curso'] : 'Curso') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Selector opcional de Materia si el curso seleccionado es Diplomado/PNFA -->
                <div class="row mb-3" id="row_materia_taquilla" style="display:none;">
                    <div class="col-md-12">
                        <label class="form-label fw-bold text-primary"><i class="fas fa-book me-1"></i> Materia / Bimestre (Opcional si paga materia individual):</label>
                        <select class="form-select" name="id_materia_bimestre" id="select_materia_taquilla">
                            <option value="">-- Curso / Diplomado Completo (General) --</option>
                        </select>
                    </div>
                </div>

                <div class="card bg-light mb-3 border-0">
                    <div class="card-body py-2">
                        <label class="form-label fw-bold mb-2">Tipo de Operación:</label>
                        <div class="d-flex gap-4 flex-wrap">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo_recepcion" id="tipo_transferencia" value="transferencia" checked>
                                <label class="form-check-label text-primary fw-bold" for="tipo_transferencia">
                                    <i class="fas fa-university"></i> Recepción de Transferencia / Pago Móvil
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo_recepcion" id="tipo_efectivo" value="efectivo_punto">
                                <label class="form-check-label text-success fw-bold" for="tipo_efectivo">
                                    <i class="fas fa-money-bill-wave"></i> Efectivo / Punto de Venta
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3" id="row_bancos">
                    <div class="col-md-6">
                        <label class="form-label">Banco de Origen:</label>
                        <input type="text" class="form-control" name="banco_origen" id="banco_origen" placeholder="Ej: Banco de Venezuela, Provincial...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cuenta Destino:</label>
                        <select class="form-select" name="id_cuenta_destino" id="id_cuenta_destino">
                            <option value="">-- Seleccione la Cuenta --</option>
                            <?php foreach($cuentas_sede as $cta): ?>
                                <option value="<?= $cta['id_cuenta'] ?>"><?= htmlspecialchars($cta['banco'] . ' (' . $cta['tipo_cuenta'] . ') - ' . $cta['numero_cuenta']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Referencia / N° Operación o Recibo:</label>
                        <input type="text" class="form-control" name="numero_operacion" id="numero_operacion_taquilla" placeholder="Ej: 123456789" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Monto Recibido:</label>
                        <input type="number" class="form-control" name="monto" step="0.01" min="0.01" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Moneda:</label>
                        <select class="form-select" name="moneda" required>
                            <option value="Bolivares">Bolívares (Bs)</option>
                            <option value="Dolares">Dólares ($)</option>
                            <option value="Pesos">Pesos ($)</option>
                        </select>
                    </div>
                </div>

                <!-- Subida de Comprobante (Disponible tanto para Transferencia como para Efectivo / Punto de Venta) -->
                <div class="mb-3 p-3 border rounded bg-white">
                    <label class="form-label fw-bold text-dark">
                        <i class="fas fa-file-upload text-primary me-1"></i> Adjuntar Comprobante de Pago / Bauche (Opcional - PDF, JPG, PNG):
                    </label>
                    <input type="file" class="form-control" name="comprobante_archivo" id="comprobante_archivo_taquilla" accept=".pdf,.jpg,.jpeg,.png">
                    <small class="text-muted d-block mt-1">
                        Disponible tanto para <b>Recepción de Transferencia</b> como para <b>Efectivo / Punto de Venta</b> (capture, bauche o recibo). Tamaño máx: 5MB.
                    </small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Observación (Opcional):</label>
                    <textarea class="form-control" name="observacion" rows="2" placeholder="Notas sobre el pago..."></textarea>
                </div>
                
                <div class="alert alert-info" id="alert_transferencia">
                    <i class="fas fa-info-circle me-2"></i> <strong>Atención:</strong> Al registrar una transferencia, el pago quedará en estado <strong>Pendiente</strong> a la espera de conciliación bancaria.
                </div>
                
                <div class="alert alert-warning" id="alert_efectivo" style="display:none;">
                    <i class="fas fa-exclamation-triangle me-2"></i> <strong>Atención:</strong> Al registrar Efectivo/Punto, el pago quedará <strong>Aprobado Automáticamente</strong> bajo su autoría.
                </div>
                
                <div class="text-end">
                    <button type="submit" class="btn btn-primary btn-lg" id="btnProcesarPago"><i class="fas fa-save me-2"></i> Registrar Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function inicializarBuscadorCursosTaquilla() {
    let select = $('#select_curso');
    if (select.hasClass("select2-hidden-accessible")) {
        select.select2('destroy');
    }
    select.select2({
        theme: 'bootstrap-5',
        placeholder: "-- Busque por ID o Nombre del Concepto / Curso --",
        allowClear: true,
        width: '100%'
    });
}

$(document).ready(function() {

    inicializarBuscadorCursosTaquilla();

    // Cargar materias si el curso seleccionado tiene materias configuradas
    $('#select_curso').on('change select2:select', function() {
        const idCurso = $(this).val();
        const rowMat = $('#row_materia_taquilla');
        const selMat = $('#select_materia_taquilla');
        if (!idCurso) {
            rowMat.hide();
            selMat.html('<option value="">-- Curso / Diplomado Completo (General) --</option>');
            return;
        }
        $.ajax({
            url: '../controllers/obtener_materias_ajax.php',
            type: 'GET',
            data: { id_curso: idCurso },
            dataType: 'json',
            success: function(res) {
                if (Array.isArray(res) && res.length > 0) {
                    let html = '<option value="">-- Curso / Diplomado Completo (General) --</option>';
                    res.forEach(function(m) {
                        html += `<option value="${m.id}">[ID: ${m.id}] ${m.nombre}</option>`;
                    });
                    selMat.html(html);
                    rowMat.slideDown();
                } else {
                    rowMat.hide();
                    selMat.html('<option value="">-- Curso / Diplomado Completo (General) --</option>');
                }
            },
            error: function() {
                rowMat.hide();
            }
        });
    });

    // Toggle Payment Mode
    $('input[name="tipo_recepcion"]').change(function() {
        if ($(this).val() === 'transferencia') {
            $('#row_bancos').slideDown();
            $('#banco_origen, #id_cuenta_destino').prop('required', true);
            $('#alert_transferencia').show();
            $('#alert_efectivo').hide();
            $('#btnProcesarPago').removeClass('btn-success').addClass('btn-primary').html('<i class="fas fa-save me-2"></i> Registrar Transferencia (Pendiente)');
        } else {
            $('#row_bancos').slideUp();
            $('#banco_origen, #id_cuenta_destino').prop('required', false);
            $('#alert_transferencia').hide();
            $('#alert_efectivo').show();
            $('#btnProcesarPago').removeClass('btn-primary').addClass('btn-success').html('<i class="fas fa-check-circle me-2"></i> Procesar y Aprobar (Efectivo / Punto)');
        }
    });
    $('input[name="tipo_recepcion"]:checked').trigger('change');

    // Buscar Estudiante
    $('#buscarEstudianteForm').submit(function(e) {
        e.preventDefault();
        const cedula = $('#cedula_buscar').val().trim();
        if (!cedula) return;
        
        $(this).find('button').prop('disabled', true);
        
        $.ajax({
            url: '../controllers/taquilla_controlador.php',
            type: 'POST',
            data: { action: 'buscar_estudiante', cedula: cedula },
            dataType: 'json',
            success: function(res) {
                $('#buscarEstudianteForm').find('button').prop('disabled', false);
                const infoDiv = $('#estudianteInfo');
                infoDiv.empty().show();
                $('#moduloPago').hide();
                
                if (res.status === 'found') {
                    infoDiv.html(`
                        <div class="alert alert-success mb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <strong>Estudiante Encontrado:</strong> ${res.usuario.nombre} ${res.usuario.apellido} (${res.usuario.correo})
                            </div>
                            <button type="button" class="btn btn-sm btn-success" onclick="prepararPago(${res.usuario.id}, '${res.usuario.nombre.replace(/'/g, "\\'")} ${res.usuario.apellido.replace(/'/g, "\\'")}')">
                                Continuar a Pago <i class="fas fa-arrow-right ms-1"></i>
                            </button>
                        </div>
                    `);
                } else {
                    infoDiv.html(`
                        <div class="alert alert-info">
                            <strong>Estudiante no encontrado.</strong> Registre sus datos básicos para crearle un perfil automáticamente en esta Sede.
                        </div>
                        <form id="registroJITForm" class="row g-3">
                            <input type="hidden" name="action" value="registro_jit">
                            <input type="hidden" name="cedula" value="${cedula}">
                            <div class="col-md-4">
                                <label class="form-label">Nombre(s)</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Apellido(s)</label>
                                <input type="text" class="form-control" name="apellido" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" name="correo" required>
                            </div>
                            <div class="col-12 text-end mt-3">
                                <button type="submit" class="btn btn-primary">Crear Perfil y Continuar</button>
                            </div>
                        </form>
                    `);
                }
            },
            error: function() {
                $('#buscarEstudianteForm').find('button').prop('disabled', false);
                Swal.fire('Error', 'No se pudo comunicar con el servidor.', 'error');
            }
        });
    });

    // Registro JIT
    $(document).on('submit', '#registroJITForm', function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Registrando...');
        
        $.ajax({
            url: '../controllers/taquilla_controlador.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    $('#estudianteInfo').html(`
                        <div class="alert alert-success mb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <strong>Perfil Creado:</strong> ${res.usuario.nombre} ${res.usuario.apellido}
                            </div>
                            <button type="button" class="btn btn-sm btn-success" onclick="prepararPago(${res.usuario.id}, '${res.usuario.nombre.replace(/'/g, "\\'")} ${res.usuario.apellido.replace(/'/g, "\\'")}')">
                                Continuar a Pago <i class="fas fa-arrow-right ms-1"></i>
                            </button>
                        </div>
                    `);
                    prepararPago(res.usuario.id, res.usuario.nombre + ' ' + res.usuario.apellido);
                } else {
                    btn.prop('disabled', false).text('Crear Perfil y Continuar');
                    Swal.fire('Error', res.message || 'Error al registrar.', 'error');
                }
            },
            error: function() {
                btn.prop('disabled', false).text('Crear Perfil y Continuar');
                Swal.fire('Error', 'Fallo la conexión al servidor.', 'error');
            }
        });
    });
    
    // Procesar Pago (Con soporte para subida de archivo mediante FormData)
    $('#registrarPagoForm').submit(function(e) {
        e.preventDefault();
        const btn = $('#btnProcesarPago');
        const originalHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Procesando...');
        
        const formData = new FormData(this);

        $.ajax({
            url: '../controllers/taquilla_controlador.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    Swal.fire({
                        title: '¡Pago Registrado!',
                        text: res.message || 'El pago ha sido registrado exitosamente.',
                        icon: 'success',
                        confirmButtonText: 'Nueva Operación'
                    }).then(() => {
                        $('#registrarPagoForm')[0].reset();
                        $('#select_curso').val('').trigger('change');
                        $('#row_materia_taquilla').hide();
                        $('input[name="tipo_recepcion"]:checked').trigger('change');
                        $('#moduloPago').hide();
                        $('#estudianteInfo').empty().hide();
                        $('#cedula_buscar').val('').focus();
                        btn.prop('disabled', false);
                    });
                } else {
                    btn.prop('disabled', false).html(originalHtml);
                    Swal.fire('Error', res.message || 'Error al procesar el pago.', 'error');
                }
            },
            error: function() {
                btn.prop('disabled', false).html(originalHtml);
                Swal.fire('Error', 'Fallo la conexión al procesar el pago.', 'error');
            }
        });
    });
});

// Función global para preparar el pago
function prepararPago(id_usuario, nombre_completo) {
    $('#id_usuario_pago').val(id_usuario);
    $('#nombre_estudiante_pago').val(nombre_completo);
    $('#moduloPago').fadeIn();
    
    // Actualizar lista de todos los cursos con su ID y buscador Select2
    $.ajax({
        url: '../controllers/taquilla_controlador.php',
        type: 'POST',
        data: { action: 'listar_cursos' },
        dataType: 'json',
        success: function(res) {
            let select = $('#select_curso');
            if (select.hasClass("select2-hidden-accessible")) {
                select.select2('destroy');
            }
            select.empty();
            if (res.status === 'success' && res.cursos.length > 0) {
                select.append('<option value="">-- Busque por ID o Nombre del Concepto / Curso --</option>');
                res.cursos.forEach(function(c) {
                    const tipo = c.tipo_curso ? c.tipo_curso : 'Curso';
                    select.append(`<option value="${c.id_curso}">[ID: ${c.id_curso}] - ${c.nombre_curso} (${tipo})</option>`);
                });
            } else {
                select.append('<option value="">No hay conceptos registrados</option>');
            }
            inicializarBuscadorCursosTaquilla();
        }
    });
}
</script>
