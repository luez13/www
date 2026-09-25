<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/model.php';
require_once '../controllers/autenticacion.php';

// Validar acceso: Solo Roles 4 (ADMIN) y 5 (ANALISTA)
if (!isset($_SESSION['user_id']) || !tieneAcceso([4, 5])) {
    echo '<div class="alert alert-danger">Acceso denegado. No tienes permisos para operar la Taquilla.</div>';
    exit;
}

$id_extension_admin = $_SESSION['id_extension'];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800"><i class="fas fa-cash-register text-primary me-2"></i> Taquilla Física / Caja</h2>
        <span class="badge bg-primary fs-6">Sede Activa: <?php echo $_SESSION['es_academico'] ? 'Formación Permanente' : 'Postgrado'; ?></span>
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
            <form id="registrarPagoForm">
                <input type="hidden" id="id_usuario_pago" name="id_usuario">
                <input type="hidden" name="action" value="registrar_pago_taquilla">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Estudiante seleccionado:</label>
                        <input type="text" class="form-control bg-light" id="nombre_estudiante_pago" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Concepto / Curso a pagar:</label>
                        <select class="form-select" name="id_curso" id="select_curso" required>
                            <option value="">Cargando conceptos...</option>
                        </select>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Método / Referencia:</label>
                        <input type="text" class="form-control" name="numero_operacion" placeholder="Ej: Efectivo, Punto de Venta, Ref..." required>
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
                
                <div class="mb-3">
                    <label class="form-label">Observación (Opcional):</label>
                    <textarea class="form-control" name="observacion" rows="2" placeholder="Notas sobre el pago..."></textarea>
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i> <strong>Atención:</strong> Al registrar este pago, quedará <strong>aprobado automáticamente</strong> en el sistema bajo su autoría.
                </div>
                
                <div class="text-end">
                    <button type="submit" class="btn btn-success btn-lg" id="btnProcesarPago"><i class="fas fa-check-circle me-2"></i> Procesar y Aprobar Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Buscar Estudiante
    $('#buscarEstudianteForm').submit(function(e) {
        e.preventDefault();
        const cedula = $('#cedula_buscar').val().trim();
        if (!cedula) return;
        
        // Bloquear botón
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
                    // Mostrar info y habilitar modulo de pago
                    infoDiv.html(`
                        <div class="alert alert-success mb-0">
                            <strong>Estudiante Encontrado:</strong> ${res.usuario.nombre} ${res.usuario.apellido} (${res.usuario.correo})
                            <button type="button" class="btn btn-sm btn-outline-success float-end" onclick="prepararPago(${res.usuario.id}, '${res.usuario.nombre} ${res.usuario.apellido}')">
                                Continuar a Pago <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    `);
                } else {
                    // Mostrar form de JIT
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
                        <div class="alert alert-success mb-0">
                            <strong>Perfil Creado:</strong> ${res.usuario.nombre} ${res.usuario.apellido}
                            <button type="button" class="btn btn-sm btn-outline-success float-end" onclick="prepararPago(${res.usuario.id}, '${res.usuario.nombre} ${res.usuario.apellido}')">
                                Continuar a Pago <i class="fas fa-arrow-right"></i>
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
    
    // Procesar Pago
    $('#registrarPagoForm').submit(function(e) {
        e.preventDefault();
        const btn = $('#btnProcesarPago');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Procesando...');
        
        $.ajax({
            url: '../controllers/taquilla_controlador.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    Swal.fire({
                        title: '¡Pago Aprobado!',
                        text: 'El pago ha sido registrado y acreditado exitosamente.',
                        icon: 'success',
                        confirmButtonText: 'Nueva Operación'
                    }).then(() => {
                        // Resetear para otra operación
                        $('#registrarPagoForm')[0].reset();
                        $('#moduloPago').hide();
                        $('#estudianteInfo').empty().hide();
                        $('#cedula_buscar').val('').focus();
                        btn.prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i> Procesar y Aprobar Pago');
                    });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i> Procesar y Aprobar Pago');
                    Swal.fire('Error', res.message || 'Error al procesar el pago.', 'error');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i> Procesar y Aprobar Pago');
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
    
    // Cargar cursos de la sede
    $.ajax({
        url: '../controllers/taquilla_controlador.php',
        type: 'POST',
        data: { action: 'listar_cursos' },
        dataType: 'json',
        success: function(res) {
            let select = $('#select_curso');
            select.empty();
            if (res.status === 'success' && res.cursos.length > 0) {
                select.append('<option value="">-- Seleccione el Concepto --</option>');
                res.cursos.forEach(function(c) {
                    select.append(`<option value="${c.id_curso}">${c.nombre_curso} (${c.tipo_curso})</option>`);
                });
            } else {
                select.append('<option value="">No hay conceptos activos en esta sede</option>');
            }
            // Inicializar Select2 con el tema Bootstrap 5
            select.select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#moduloPago')
            });
        }
    });
}
</script>
