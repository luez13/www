<?php
// views/gestion_cuentas_bancarias.php

require_once '../controllers/init.php';
require_once '../config/model.php';
require_once '../models/Pago.php';

// Validar Permisos (Roles 1 y 2)
if (!isset($_SESSION['id_rol']) || !in_array($_SESSION['id_rol'], [3, 4])) {
    die('<div class="alert alert-danger text-center mt-5"><b>Acceso denegado:</b> No tienes permisos administrativos para ver esta página.</div>');
}

$db = new DB();
$pagoModel = new Pago($db);
$cuentas = $pagoModel->obtenerCuentas();

// Función auxiliar para sanitizar HTML
function h($str)
{
    return htmlspecialchars(isset($str) ? $str : '', ENT_QUOTES, 'UTF-8');
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-university me-2"></i> Gestión de Cuentas Bancarias</h1>
        <button class="btn btn-primary shadow-sm" onclick="nuevaCuenta()">
            <i class="fas fa-plus fa-sm text-white-50 me-1"></i> Nueva Cuenta Receptora
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-list me-2"></i> Cuentas Registradas</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0 text-center align-middle">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 5%;">ID</th>
                            <th style="width: 15%;">Banco</th>
                            <th style="width: 20%;">Titular</th>
                            <th style="width: 15%;">Cédula/RIF</th>
                            <th style="width: 15%;">Tipo / Nro.</th>
                            <th style="width: 15%;">Contacto</th>
                            <th style="width: 10%;">Estado</th>
                            <th style="width: 5%;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cuentas)): ?>
                            <tr>
                                <td colspan="8" class="text-muted py-4">No hay cuentas bancarias registradas.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($cuentas as $c): ?>
                                <tr>
                                    <td class="align-middle"><?= $c['id_cuenta'] ?></td>
                                    <td class="align-middle font-weight-bold text-dark"><?= h($c['banco']) ?></td>
                                    <td class="align-middle text-left"><?= h($c['titular']) ?></td>
                                    <td class="align-middle"><?= h($c['cedula_rif']) ?></td>
                                    <td class="align-middle text-left">
                                        <span class="badge badge-info mb-1"><?= h($c['tipo_cuenta']) ?></span><br>
                                        <small><?= h($c['numero_cuenta']) ?: '<i class="text-muted">N/A</i>' ?></small>
                                    </td>
                                    <td class="align-middle text-left small">
                                        <?php if (!empty($c['telefono']))
                                            echo '<i class="fas fa-phone fa-fw text-muted"></i> ' . h($c['telefono']) . '<br>'; ?>
                                        <?php if (!empty($c['correo']))
                                            echo '<i class="fas fa-envelope fa-fw text-muted"></i> ' . h($c['correo']); ?>
                                        <?php if (empty($c['telefono']) && empty($c['correo']))
                                            echo '<i class="text-muted">Sin contacto</i>'; ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php if ($c['activo']): ?>
                                            <span class="badge badge-success p-2">Activa</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary p-2">Inactiva</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <button class="btn btn-sm btn-warning shadow-sm"
                                            onclick="editarCuenta(<?= htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8') ?>)"
                                            title="Editar Cuenta">
                                            <i class="fas fa-edit text-dark"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCuenta" tabindex="-1" role="dialog" aria-labelledby="modalCuentaLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalCuentaLabel"><i class="fas fa-university me-2"></i> <span>Nueva Cuenta
                        Bancaria</span></h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="formCuenta">
                <div class="modal-body bg-light">
                    <input type="hidden" id="cuenta_action" name="action" value="crear_cuenta">
                    <input type="hidden" id="id_cuenta" name="id_cuenta" value="">

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Banco <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="banco" name="banco"
                                placeholder="Ej: Banesco, Mercantil, Zelle" required>
                        </div>

                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Titular de la Cuenta <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="titular" name="titular"
                                placeholder="Nombre de la persona o institución" required>
                        </div>

                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold">Cédula o RIF <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="cedula_rif" name="cedula_rif"
                                placeholder="V-12345678 / J-12345678" required>
                        </div>

                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold">Tipo de Transferencia</label>
                            <select class="form-control" id="tipo_cuenta" name="tipo_cuenta">
                                <option value="Corriente">Corriente</option>
                                <option value="Ahorro">Ahorro</option>
                                <option value="Pago Móvil">Pago Móvil</option>
                                <option value="Zelle">Zelle</option>
                                <option value="Transferencia Internacional">Transferencia Internacional</option>
                            </select>
                        </div>

                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold">N° de Cuenta (Opcional)</label>
                            <input type="text" class="form-control" id="numero_cuenta" name="numero_cuenta"
                                placeholder="0134-...">
                        </div>

                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Teléfono (Opcional - Para Pago Móvil)</label>
                            <input type="text" class="form-control" id="telefono" name="telefono"
                                placeholder="0414-1234567">
                        </div>

                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Correo (Opcional - Para Zelle)</label>
                            <input type="email" class="form-control" id="correo" name="correo"
                                placeholder="ejemplo@correo.com">
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="activo" name="activo" value="1"
                                checked>
                            <label class="custom-control-label font-weight-bold cursor-pointer" for="activo">Habilitar
                                esta cuenta para recibir pagos</label>
                        </div>
                        <small class="form-text text-muted">Si desmarcas esta opción, la cuenta se guardará pero no le
                            aparecerá a los estudiantes.</small>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i
                            class="fas fa-times me-1"></i> Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Guardar
                        Cuenta</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    /**
     * Prepara el modal para crear una nueva cuenta
     */
    function nuevaCuenta() {
        // Limpiar formulario
        $('#formCuenta')[0].reset();

        // Configurar inputs ocultos
        $('#cuenta_action').val('crear_cuenta');
        $('#id_cuenta').val('');

        // Configurar visuales
        $('#modalCuentaLabel span').text('Nueva Cuenta Bancaria');
        $('#activo').prop('checked', true); // Por defecto activo

        // Mostrar modal
        $('#modalCuenta').modal('show');
    }

    /**
     * Prepara el modal para editar una cuenta existente
     * @param {Object} cuenta - Los datos de la cuenta en JSON
     */
    function editarCuenta(cuenta) {
        // Configurar inputs ocultos
        $('#cuenta_action').val('actualizar_cuenta');
        $('#id_cuenta').val(cuenta.id_cuenta);

        // Rellenar campos de texto
        $('#banco').val(cuenta.banco);
        $('#titular').val(cuenta.titular);
        $('#cedula_rif').val(cuenta.cedula_rif);
        $('#tipo_cuenta').val(cuenta.tipo_cuenta);
        $('#numero_cuenta').val(cuenta.numero_cuenta);
        $('#telefono').val(cuenta.telefono);
        $('#correo').val(cuenta.correo);

        // Manejar el checkbox (PostgreSQL suele devolver true/false o "1"/"0")
        let esActiva = (cuenta.activo === true || cuenta.activo === "1" || cuenta.activo === 1 || cuenta.activo === "t");
        $('#activo').prop('checked', esActiva);

        // Configurar visuales
        $('#modalCuentaLabel span').text('Editar Cuenta: ' + cuenta.banco);

        // Mostrar modal
        $('#modalCuenta').modal('show');
    }

    /**
     * Evento submit del formulario para guardar/actualizar
     */
    $('#formCuenta').on('submit', function (e) {
        e.preventDefault(); // Evita que la página recargue

        var formData = new FormData(this);

        // Cambiamos el texto del botón para feedback visual
        var btnSubmit = $(this).find('button[type="submit"]');
        var btnOriginalText = btnSubmit.html();
        btnSubmit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Guardando...');

        $.ajax({
            url: '../controllers/pagos_controlador.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    alert(response.message);
                    $('#modalCuenta').modal('hide');
                    loadPage('../views/gestion_cuentas_bancarias.php'); // Recargar la vista
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert('Error de conexión con el servidor. Por favor, intenta de nuevo.');
            },
            complete: function () {
                // Restaurar botón siempre al finalizar (éxito o error)
                btnSubmit.prop('disabled', false).html(btnOriginalText);
            }
        });
    });
</script>