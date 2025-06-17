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
            <h6 class="m-0 font-weight-bold text-primary">Firmante por Defecto</h6>
        </div>
        <div class="card-body">
            <p>Selecciona la persona que ocupará el cargo de "Coordinador de Formación Permanente" por defecto. Esta selección se usará al aplicar plantillas de firmas en los cursos.</p>
            
            <form id="formAjustesSistema">
                <input type="hidden" name="action" value="guardar_config">
                <input type="hidden" name="clave_config" value="ID_CARGO_COORD_FP_POR_DEFECTO">

                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label for="selectCoordPorDefecto" class="form-label">Coordinador de Formación Permanente por Defecto:</label>
                        <select class="form-select" id="selectCoordPorDefecto" name="valor_config" required>
                            <option value="">Cargando firmantes...</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">Guardar Ajuste</button>
                    </div>
                </div>
            </form>
            <div id="ajustes-feedback" class="mt-3"></div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const selector = $('#selectCoordPorDefecto');
    let firmantesActivos = [];

    // 1. Obtener la lista de todos los firmantes activos para llenar el selector
    const CargarFirmantes = () => {
        return $.getJSON('../controllers/CargosController.php', { action: 'listar_activos' })
            .done(function(response) {
                if (response.success) {
                    firmantesActivos = response.data;
                    selector.empty().append('<option value="">-- Seleccione un firmante --</option>');
                    firmantesActivos.forEach(firmante => {
                        selector.append(`<option value="${firmante.id}">${firmante.texto_display}</option>`);
                    });
                } else {
                    selector.html('<option value="">Error al cargar firmantes</option>');
                }
            })
            .fail(function() {
                selector.html('<option value="">Error de conexión</option>');
            });
    };
    
    // 2. Obtener el valor guardado actualmente para esta configuración
    const CargarValorActual = () => {
        return $.getJSON('../controllers/ConfigSistemaController.php', { action: 'obtener_config_clave', clave: 'ID_CARGO_COORD_FP_POR_DEFECTO' })
            .done(function(response) {
                if (response.success && response.data) {
                    selector.val(response.data.valor_config);
                }
            });
    };

    // Ejecutar en secuencia: primero cargar firmantes, luego seleccionar el valor actual
    CargarFirmantes().then(function() {
        CargarValorActual();
    });

    // 3. Manejar el guardado del formulario
    $('#formAjustesSistema').on('submit', function(event) {
        event.preventDefault();
        const formData = $(this).serialize();
        const btn = $(this).find('button[type="submit"]');
        const originalBtnText = btn.text();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Guardando...');

        $.post('../controllers/ConfigSistemaController.php', formData, function(response) {
            let feedback = $('#ajustes-feedback');
            if (response.success) {
                feedback.html('<div class="alert alert-success">Ajuste guardado correctamente.</div>');
            } else {
                feedback.html('<div class="alert alert-danger">Error: ' + (response.message || 'No se pudo guardar.') + '</div>');
            }
            setTimeout(() => feedback.empty(), 5000); // Limpiar mensaje después de 5 seg
        }, 'json').fail(function() {
            alert('Error de comunicación con el servidor.');
        }).always(function() {
            btn.prop('disabled', false).text(originalBtnText);
        });
    });
});
</script>