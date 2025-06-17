<?php
// Incluimos el init.php para tener acceso a la sesión y funciones de autenticación.
// La ruta puede necesitar ajuste según la estructura final de tu proyecto.
require_once __DIR__ . '/../controllers/init.php';

// Verificamos si el usuario tiene permiso para ver esta página.
// Usaremos una función que definimos en el paso anterior o una similar.
// Esta función debería hacer exit() si el usuario no tiene permiso.
// verificar_permiso_ajax([3, 4]); // Ejemplo para roles de Administrador y Autorizador.
?>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gestión de Firmantes Oficiales</h1>
        <button id="btnAnadirCargo" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Añadir Nuevo Firmante
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Firmantes</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="tablaCargos" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Nombre Completo</th>
                            <th>Cargo a Mostrar</th>
                            <th>Firma</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" class="text-center">Cargando datos...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCargo" tabindex="-1" role="dialog" aria-labelledby="modalCargoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="formCargo" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCargoLabel">Añadir Nuevo Firmante</h5>
                    
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_cargo" id="id_cargo">
                    <input type="hidden" name="action" id="formAction">

                    <div class="row">
                        <div class="col-md-4 form-group"><label for="titulo">Título Académico</label><input type="text" class="form-control" id="titulo" name="titulo" placeholder="Ej: Ing., Dr., Msc."></div>
                        <div class="col-md-4 form-group"><label for="nombre">Nombre(s)</label><input type="text" class="form-control" id="nombre" name="nombre" required></div>
                        <div class="col-md-4 form-group"><label for="apellido">Apellido(s)</label><input type="text" class="form-control" id="apellido" name="apellido" required></div>
                    </div>
                    <div class="form-group mt-3">
                        <label for="nombre_cargo">Cargo a Mostrar en Certificado</label>
                        <input type="text" class="form-control" id="nombre_cargo" name="nombre_cargo" placeholder="Ej: Rector, Coordinador de Formación Permanente" required>
                    </div>
                    <div class="form-group mt-3">
                        <label for="firma_digital">Imagen de la Firma</label>
                        <input type="file" class="form-control-file" id="firma_digital" name="firma_digital" accept="image/png">
                        <small class="form-text text-muted">Subir solo si se va a añadir o cambiar. Se recomienda PNG con fondo transparente.</small>
                        <div id="firma_actual_preview" class="mt-2"></div>
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" checked>
                        <label class="form-check-label" for="activo">
                            Activo (Puede ser seleccionado para firmar)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary" type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
$(document).ready(function() {
    
    // Función para cargar los cargos en la tabla
    function cargarCargos() {
        $.ajax({
            url: '../controllers/CargosController.php', // El controlador que crearemos
            type: 'GET',
            data: { action: 'listar' },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    let tbody = $('#tablaCargos tbody');
                    tbody.empty(); // Limpiar la tabla antes de añadir nuevos datos
                    if(response.data.length > 0) {
                        response.data.forEach(cargo => {
                            let firmaHtml = cargo.firma_digital ? `<img src="${cargo.firma_digital}" alt="Firma" style="max-height: 30px; background-color: #f0f0f0;">` : 'No asignada';
                            let estadoHtml = cargo.activo ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>';
                            let botonEstadoTexto = cargo.activo ? 'Desactivar' : 'Activar';
                            let botonEstadoClase = cargo.activo ? 'btn-warning' : 'btn-info';
                            
                            let fila = `
                                <tr>
                                    <td>${cargo.titulo || ''} ${cargo.nombre} ${cargo.apellido}</td>
                                    <td>${cargo.nombre_cargo}</td>
                                    <td>${firmaHtml}</td>
                                    <td>${estadoHtml}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary btnEditar" data-id="${cargo.id_cargo}">Editar</button>
                                        <button class="btn btn-sm ${botonEstadoClase} btnCambiarEstado" data-id="${cargo.id_cargo}" data-estado-actual="${cargo.activo ? 1 : 0}">${botonEstadoTexto}</button>
                                    </td>
                                </tr>
                            `;
                            tbody.append(fila);
                        });
                    } else {
                        tbody.html('<tr><td colspan="5" class="text-center">No hay cargos registrados.</td></tr>');
                    }
                } else {
                    alert('Error al cargar los firmantes: ' + response.message);
                }
            },
            error: function() {
                alert('No se pudo establecer comunicación con el servidor para cargar los firmantes.');
                $('#tablaCargos tbody').html('<tr><td colspan="5" class="text-center">Error de conexión.</td></tr>');
            }
        });
    }

    // Carga inicial de los cargos al entrar a la página
    cargarCargos();

    // Abrir modal para AÑADIR nuevo cargo
    $('#btnAnadirCargo').on('click', function() {
        $('#formCargo')[0].reset(); // Limpiar el formulario
        $('#id_cargo').val(''); // Asegurarse que el ID está vacío
        $('#formAction').val('crear');
        $('#modalCargoLabel').text('Añadir Nuevo Firmante');
        $('#firma_actual_preview').empty(); // Limpiar la vista previa de la firma
        $('#modalCargo').modal('show');
    });

    // Abrir modal para EDITAR un cargo (usando delegación de eventos)
    $('#tablaCargos').on('click', '.btnEditar', function() {
        let id = $(this).data('id');
        $.ajax({
            url: '../controllers/CargosController.php',
            type: 'GET',
            data: { action: 'obtener', id: id },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    let cargo = response.data;
                    $('#formCargo')[0].reset();
                    $('#id_cargo').val(cargo.id_cargo);
                    $('#formAction').val('editar');
                    $('#titulo').val(cargo.titulo);
                    $('#nombre').val(cargo.nombre);
                    $('#apellido').val(cargo.apellido);
                    $('#nombre_cargo').val(cargo.nombre_cargo);
                    $('#activo').prop('checked', cargo.activo);
                    
                    // Mostrar firma actual si existe
                    $('#firma_actual_preview').empty();
                    if(cargo.firma_digital) {
                        $('#firma_actual_preview').html(`<strong>Firma actual:</strong><br><img src="${cargo.firma_digital}" style="max-width: 200px; max-height: 80px; border: 1px solid #ddd; margin-top: 5px;">`);
                    }

                    $('#modalCargoLabel').text('Editar Firmante');
                    $('#modalCargo').modal('show');
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('No se pudo obtener la información del firmante.');
            }
        });
    });

    // Enviar formulario (Crear o Editar)
    $('#formCargo').on('submit', function(event) {
        event.preventDefault();
        let formData = new FormData(this);
        // Asegurarse de enviar el estado 'activo' incluso si el checkbox no está marcado
        if(!$('#activo').is(':checked')) {
            formData.append('activo', '0');
        }

        $.ajax({
            url: '../controllers/CargosController.php',
            type: 'POST',
            data: formData,
            contentType: false, // Necesario para FormData
            processData: false, // Necesario para FormData
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    $('#modalCargo').modal('hide');
                    alert(response.message);
                    cargarCargos(); // Recargar la tabla
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Ocurrió un error en el servidor. Revise la consola para más detalles.');
                console.log(xhr.responseText);
            }
        });
    });

    // Cambiar estado (Activo/Inactivo)
    $('#tablaCargos').on('click', '.btnCambiarEstado', function() {
        let id = $(this).data('id');
        let estadoActual = $(this).data('estado-actual');
        let nuevoEstado = estadoActual ? 0 : 1; // Invertir estado
        let accionTexto = estadoActual ? 'desactivar' : 'activar';

        if(confirm(`¿Estás seguro de que quieres ${accionTexto} a este firmante?`)) {
            $.ajax({
                url: '../controllers/CargosController.php',
                type: 'POST',
                data: { action: 'cambiar_estado', id_cargo: id, estado: nuevoEstado },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        alert(response.message);
                        cargarCargos();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Ocurrió un error al intentar cambiar el estado.');
                }
            });
        }
    });

});
</script>