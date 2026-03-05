<?php
require_once __DIR__ . '/../controllers/init.php';

// Verificar permisos
if ($_SESSION['id_rol'] != 4) {
    die('<div class="alert alert-danger m-3">Acceso denegado.</div>');
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Ajustes Generales del Sistema</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Firmante por Defecto: Coordinador</h6>
        </div>
        <div class="card-body">
            <p>Selecciona la persona que ocupará el cargo de "Coordinador de Formación Permanente" por defecto.</p>

            <form id="formAjustesSistema">
                <input type="hidden" name="action" value="guardar_config">
                <input type="hidden" name="clave_config" value="ID_CARGO_COORD_FP_POR_DEFECTO">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label for="selectCoordPorDefecto" class="form-label">Coordinador de Formación
                            Permanente:</label>
                        <select class="form-select" id="selectCoordPorDefecto" name="valor_config" required>
                            <option value="">Cargando firmantes...</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">Guardar Coordinador</button>
                    </div>
                </div>
            </form>
            <div id="ajustes-feedback" class="mt-3"></div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Firmante por Defecto: Encargado del area</h6>
        </div>
        <div class="card-body">
            <p>Selecciona la persona que ocupará el cargo de "Encargado del Area de Formación Permanente" por defecto.</p>

            <form id="formAjustesVicerrectora">
                <input type="hidden" name="action" value="guardar_config">
                <input type="hidden" name="clave_config" value="ID_CARGO_VICERRECTORADO_POR_DEFECTO">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label for="selectVicerrectoraPorDefecto" class="form-label">Encargado del Area:</label>
                        <select class="form-select" id="selectVicerrectoraPorDefecto" name="valor_config" required>
                            <option value="">Cargando firmantes...</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">Guardar Vicerrectora</button>
                    </div>
                </div>
            </form>
            <div id="ajustes-feedback-vicerrectora" class="mt-3"></div>
        </div>
    </div>

    <!-- Nuevos ajustes para datos de contacto de la universidad -->
    <div class="row">
        <!-- Correo de Contacto -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Correo de Contacto</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Aparecerá en el pie de página de las constancias.</p>
                    <form id="formAjustesCorreo">
                        <input type="hidden" name="action" value="guardar_config">
                        <input type="hidden" name="clave_config" value="CORREO_CONTACTO_POR_DEFECTO">
                        <div class="mb-3">
                            <input type="email" class="form-control" id="inputCorreoDefecto" name="valor_config"
                                placeholder="Ej: contacto@universidad.edu.ve" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Guardar Correo</button>
                    </form>
                    <div id="ajustes-feedback-correo" class="mt-3"></div>
                </div>
            </div>
        </div>

        <!-- Teléfono de Contacto -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Teléfono de Contacto</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Aparecerá en el pie de página de las constancias.</p>
                    <form id="formAjustesTelefono">
                        <input type="hidden" name="action" value="guardar_config">
                        <input type="hidden" name="clave_config" value="TELEFONO_CONTACTO_POR_DEFECTO">
                        <div class="mb-3">
                            <input type="text" class="form-control" id="inputTelefonoDefecto" name="valor_config"
                                placeholder="Ej: 0276-3532211" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Guardar Teléfono</button>
                    </form>
                    <div id="ajustes-feedback-telefono" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    $(document).ready(function () {

        // --- LÓGICA PARA EL SELECTOR DEL COORDINADOR (SIN CAMBIOS) ---
        const selectorCoord = $('#selectCoordPorDefecto');

        const CargarFirmantes = () => {
            return $.getJSON('../controllers/CargosController.php', { action: 'listar_activos' });
        };

        const CargarValorActualCoord = () => {
            return $.getJSON('../controllers/ConfigSistemaController.php', { action: 'obtener_config_clave', clave: 'ID_CARGO_COORD_FP_POR_DEFECTO' })
                .done(function (response) {
                    if (response.success && response.data) {
                        selectorCoord.val(response.data.valor_config);
                    }
                });
        };

        CargarFirmantes().then(function (response) {
            if (response.success) {
                selectorCoord.empty().append('<option value="">-- Seleccione un firmante --</option>');
                response.data.forEach(firmante => {
                    selectorCoord.append(`<option value="${firmante.id}">${firmante.texto_display}</option>`);
                });
                CargarValorActualCoord();
            } else {
                selectorCoord.html('<option value="">Error al cargar</option>');
            }
        });

        $('#formAjustesSistema').on('submit', function (event) {
            event.preventDefault();
            const formData = $(this).serialize();
            const btn = $(this).find('button[type="submit"]');
            const originalBtnText = btn.text();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Guardando...');

            $.post('../controllers/ConfigSistemaController.php', formData, function (response) {
                let feedback = $('#ajustes-feedback');
                if (response.success) {
                    feedback.html('<div class="alert alert-success">Ajuste guardado correctamente.</div>');
                } else {
                    feedback.html('<div class="alert alert-danger">Error: ' + (response.message || 'No se pudo guardar.') + '</div>');
                }
                setTimeout(() => feedback.empty(), 5000);
            }, 'json').fail(function () {
                alert('Error de comunicación con el servidor.');
            }).always(function () {
                btn.prop('disabled', false).text(originalBtnText);
            });
        });

        // --- ✅ LÓGICA PARA EL NUEVO SELECTOR DE VICERRECTORA ---
        const selectorVicerrectora = $('#selectVicerrectoraPorDefecto');

        const CargarValorActualVicerrectora = () => {
            return $.getJSON('../controllers/ConfigSistemaController.php', { action: 'obtener_config_clave', clave: 'ID_CARGO_VICERRECTORADO_POR_DEFECTO' })
                .done(function (response) {
                    if (response.success && response.data) {
                        selectorVicerrectora.val(response.data.valor_config);
                    }
                });
        };

        CargarFirmantes().then(function (response) {
            if (response.success) {
                selectorVicerrectora.empty().append('<option value="">-- Seleccione un firmante --</option>');
                response.data.forEach(firmante => {
                    selectorVicerrectora.append(`<option value="${firmante.id}">${firmante.texto_display}</option>`);
                });
                CargarValorActualVicerrectora();
            } else {
                selectorVicerrectora.html('<option value="">Error al cargar</option>');
            }
        });

        $('#formAjustesVicerrectora').on('submit', function (event) {
            event.preventDefault();
            const formData = $(this).serialize();
            const btn = $(this).find('button[type="submit"]');
            const originalBtnText = btn.text();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Guardando...');

            $.post('../controllers/ConfigSistemaController.php', formData, function (response) {
                let feedback = $('#ajustes-feedback-vicerrectora');
                if (response.success) {
                    feedback.html('<div class="alert alert-success">Ajuste guardado correctamente.</div>');
                } else {
                    feedback.html('<div class="alert alert-danger">Error: ' + (response.message || 'No se pudo guardar.') + '</div>');
                }
                setTimeout(() => feedback.empty(), 5000);
            }, 'json').fail(function () {
                alert('Error de comunicación con el servidor.');
            }).always(function () {
                btn.prop('disabled', false).text(originalBtnText);
            });
        });

        // --- LÓGICA PARA CORREO Y TELÉFONO ---

        // Cargar valores actuales
        $.getJSON('../controllers/ConfigSistemaController.php', { action: 'obtener_config_clave', clave: 'CORREO_CONTACTO_POR_DEFECTO' })
            .done(function (response) {
                if (response.success && response.data) $('#inputCorreoDefecto').val(response.data.valor_config);
            });

        $.getJSON('../controllers/ConfigSistemaController.php', { action: 'obtener_config_clave', clave: 'TELEFONO_CONTACTO_POR_DEFECTO' })
            .done(function (response) {
                if (response.success && response.data) $('#inputTelefonoDefecto').val(response.data.valor_config);
            });

        function setupSubmitAjax(formId, feedbackId) {
            $('#' + formId).on('submit', function (event) {
                event.preventDefault();
                const formData = $(this).serialize();
                const btn = $(this).find('button[type="submit"]');
                const originalBtnText = btn.text();
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Guardando...');

                $.post('../controllers/ConfigSistemaController.php', formData, function (response) {
                    let feedback = $('#' + feedbackId);
                    if (response.success) {
                        feedback.html('<div class="alert alert-success">Ajuste guardado correctamente.</div>');
                    } else {
                        feedback.html('<div class="alert alert-danger">Error: ' + (response.message || 'No se pudo guardar.') + '</div>');
                    }
                    setTimeout(() => feedback.empty(), 5000);
                }, 'json').fail(function () {
                    alert('Error de comunicación con el servidor.');
                }).always(function () {
                    btn.prop('disabled', false).text(originalBtnText);
                });
            });
        }

        setupSubmitAjax('formAjustesCorreo', 'ajustes-feedback-correo');
        setupSubmitAjax('formAjustesTelefono', 'ajustes-feedback-telefono');

    });
</script>