<?php
// views/admin_auditoria.php

require_once '../controllers/init.php';
require_once '../config/model.php';

// Seguridad: SOLO Súper Administrador (Rol 4)
if (!isset($_SESSION['user_id']) || $_SESSION['id_rol'] != 4) {
    die('<div class="alert alert-danger text-center mt-5"><b>Acceso denegado (403):</b> Esta sección está restringida EXCLUSIVAMENTE al Súper Administrador.</div>');
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-history text-primary me-2"></i> Auditoría General del Sistema</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary">Historial Global de Acciones (DB)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover w-100 align-middle" id="tablaAuditoria">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th>Usuario / Gestor</th>
                            <th>Acción</th>
                            <th>Tabla Afectada</th>
                            <th>Fecha</th>
                            <th style="width: 120px;">Detalles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Datos cargados por AJAX (DataTables Server-Side) -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Detalles de la Tupla -->
<div class="modal fade" id="modalTupla" tabindex="-1" aria-labelledby="modalTuplaLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="modalTuplaLabel"><i class="fas fa-file-code me-2"></i> Tuplas de Base de Datos</h5>
        <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <h6 class="text-danger fw-bold border-bottom pb-2">Dato Previo (OLD):</h6>
                <pre class="bg-light p-3 border rounded shadow-sm" style="max-height: 400px; overflow: auto;"><code id="tuplaPrevia" class="text-dark"></code></pre>
            </div>
            
            <div class="col-md-6 mb-3">
                <h6 class="text-success fw-bold border-bottom pb-2">Dato Modificado (NEW):</h6>
                <pre class="bg-light p-3 border rounded shadow-sm" style="max-height: 400px; overflow: auto;"><code id="tuplaNueva" class="text-dark"></code></pre>
            </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>
    // Función para transformar la tupla horizontal en una lista vertical legible
    function formatearTupla(tuplaBruta) {
        if (!tuplaBruta || tuplaBruta === "null" || tuplaBruta === "") return "(Sin datos)";
        
        // Quitar los paréntesis iniciales y finales de PostgreSQL: (dato1, dato2) -> dato1, dato2
        let texto = tuplaBruta.replace(/^\(|\)$/g, '');
        
        // Dividir por comas, PERO ignorar las comillas que están dentro de comillas dobles
        // Esta regex salva la vida con textos como "banco de venezuela, sede centro"
        let valores = texto.match(/(".*?"|[^",\s]+)(?=\s*,|\s*$)/g) || texto.split(',');
        
        let resultado = "";
        valores.forEach((val, index) => {
            // Limpiar comillas extras y mostrar en lista
            let limpio = val.replace(/^"|"$/g, '').trim();
            if(limpio === "") limpio = "[Vacío/Nulo]";
            resultado += `[Columna ${index + 1}]: ${limpio}\n`;
        });
        
        return resultado;
    }

    $(document).ready(function() {
        if ($.fn.DataTable.isDataTable('#tablaAuditoria')) {
            $('#tablaAuditoria').DataTable().destroy();
        }

        // Inicializar DataTables con Server-Side Processing
        var tableAuditoria = $('#tablaAuditoria').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '../controllers/auditoria_controlador.php',
                type: 'POST',
                error: function(xhr, error, thrown) {
                    console.error("DataTables Ajax error:", xhr.responseText);
                    if(typeof Swal !== 'undefined') {
                        Swal.fire('Error', 'No se pudieron cargar los datos de auditoría. Verifica tus permisos.', 'error');
                    }
                }
            },
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            order: [[4, 'desc']], // Ordenar por fecha desc por defecto
            columnDefs: [
                { targets: 5, orderable: false, searchable: false } // Detalles no se ordena ni se busca directamente
            ]
        });

        // Evento para abrir el modal y mostrar las tuplas en crudo
        $('#tablaAuditoria tbody').on('click', '.btn-detalles', function () {
            var previo = $(this).attr('data-previo');
            var nuevo = $(this).attr('data-nuevo');
            
            // Pasamos los datos crudos por el formateador visual
            $('#tuplaPrevia').text(formatearTupla(previo));
            $('#tuplaNueva').text(formatearTupla(nuevo));

            // Funciona en BS4 o BS5
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var myModal = new bootstrap.Modal(document.getElementById('modalTupla'));
                myModal.show();
            } else {
                $('#modalTupla').modal('show');
            }
        });
    });
</script>
