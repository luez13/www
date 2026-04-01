<?php
// views/gestion_pagos.php

require_once '../controllers/init.php';
// require_once '../controllers/autenticacion.php'; // Descomenta según tu flujo de seguridad
require_once '../config/model.php';
require_once '../models/Pago.php';

// Validar Permisos (Roles 1 y 2)
if (!isset($_SESSION['id_rol']) || !in_array($_SESSION['id_rol'], [3, 4])) {
    die('<div class="alert alert-danger text-center mt-5"><b>Acceso denegado:</b> No tienes permisos administrativos para ver esta página.</div>');
}

$db = new DB();
$pagoModel = new Pago($db);
$todos_comprobantes = $pagoModel->obtenerTodosLosComprobantes();

// Contadores para los badges de las pestañas
$conteo = [
    'Pendiente' => 0,
    'Comprobado' => 0,
    'Rechazado' => 0
];

foreach ($todos_comprobantes as $comp) {
    if (isset($conteo[$comp['estado']])) {
        $conteo[$comp['estado']]++;
    }
}

// Función auxiliar para escape seguro de HTML
function h($str)
{
    return htmlspecialchars(isset($str) ? $str : '', ENT_QUOTES, 'UTF-8');
}

// Extraer cursos únicos para rellenar el filtro dinámicamente
$cursos_filtros = [];
foreach ($todos_comprobantes as $comp) {
    if (!empty($comp['nombre_curso'])) {
        $cursos_filtros[$comp['id_curso']] = $comp['nombre_curso'];
    }
}
asort($cursos_filtros);
?>

<style>
    /* Efecto hover/seleccionado para filas clickeables */
    table.dataTable tbody tr.selected {
        background-color: #e2e3e5 !important;
        color: #383d41;
    }

    table.dataTable tbody tr {
        cursor: pointer;
    }
</style>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-money-check-alt me-2"></i> Administración de Pagos</h1>
    </div>

    <?php
    $directorio_comprobantes = '../public/assets/comprobantes';
    $archivos_comprobantes = glob($directorio_comprobantes . '/*');
    $cantidad_comprobantes = $archivos_comprobantes !== false ? count($archivos_comprobantes) : 0;

    // Alerta Amarilla si hay más de 100 comprobantes
    if ($cantidad_comprobantes > 100):
        ?>
        <div class="alert alert-warning shadow-sm border-left-warning h-100 py-2 mb-4">
            <div class="d-flex align-items-center">
                <div class="mr-3">
                    <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                </div>
                <div>
                    <h4 class="alert-heading font-weight-bold mb-1">¡Alerta de Almacenamiento!</h4>
                    <p class="mb-0">El sistema ha acumulado <b><?= $cantidad_comprobantes ?></b> archivos de comprobantes.
                        Considera realizar un Backup (.TAR) y luego Limpiar el servidor para evitar quedarte sin espacio en
                        el disco duro a largo plazo.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4 border-left-danger">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-danger"><i class="fas fa-server"></i> Mantenimiento y Backup de
                Servidor</h6>
        </div>
        <div class="card-body">
            <p>El servidor almacena los comprobantes físicos (imágenes y PDFs). Para liberar espacio de almacenamiento,
                realiza periódicamente un backup de tus archivos de pago, y posteriormente, elimínalos del servidor.</p>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-success" onclick="descargarBackupComprobantes()">
                    <i class="fas fa-file-archive"></i> Descargar Backup en (.TAR)
                </button>
                <button type="button" class="btn btn-danger ml-2" onclick="limpiarTodosLosComprobantes()">
                    <i class="fas fa-trash-alt"></i> Borrar todos los comprobantes (Peligro)
                </button>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter"></i> Filtros y Exportación</h6>
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-3 mb-3">
                    <label for="fechaDesde" class="form-label font-weight-bold">Desde fecha de pago:</label>
                    <input type="date" class="form-control" id="fechaDesde">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="fechaHasta" class="form-label font-weight-bold">Hasta fecha de pago:</label>
                    <input type="date" class="form-control" id="fechaHasta">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="cursoFiltro" class="form-label font-weight-bold">Filtrar por Curso/Diplomado:</label>
                    <select class="form-select form-control" id="cursoFiltro">
                        <option value="">-- Todos los cursos --</option>
                        <?php foreach ($cursos_filtros as $id_c => $nombre_c): ?>
                            <option value="<?= h($nombre_c) ?>"><?= h($nombre_c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-3 text-right">
                    <button class="btn btn-secondary w-100" id="btnLimpiarFiltros"><i class="fas fa-eraser"></i>
                        Limpiar</button>
                </div>
            </div>
            <div class="alert alert-info mb-0 small mt-2">
                <i class="fas fa-info-circle"></i> <strong>Tip de exportación:</strong> Puedes hacer clic en las filas
                que desees para seleccionarlas. Si hay filas seleccionadas, al presionar "Excel", "PDF" o "Imprimir"
                <b>solo se exportarán esas filas específicas</b>. Si no seleccionas ninguna, se exportará todo el
                listado visible actualmente.
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs mb-4" id="pagosTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active font-weight-bold text-warning" id="pendientes-tab" data-toggle="tab"
                href="#pendientes" role="tab" aria-controls="pendientes" aria-selected="true">
                <i class="fas fa-clock"></i> Pendientes
                <span class="badge badge-warning ml-1"><?= $conteo['Pendiente'] ?></span>
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link font-weight-bold text-success" id="aprobados-tab" data-toggle="tab" href="#aprobados"
                role="tab" aria-controls="aprobados" aria-selected="false">
                <i class="fas fa-check-circle"></i> Comprobados
                <span class="badge badge-success ml-1"><?= $conteo['Comprobado'] ?></span>
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link font-weight-bold text-danger" id="rechazados-tab" data-toggle="tab" href="#rechazados"
                role="tab" aria-controls="rechazados" aria-selected="false">
                <i class="fas fa-times-circle"></i> Rechazados
                <span class="badge badge-danger ml-1"><?= $conteo['Rechazado'] ?></span>
            </a>
        </li>
    </ul>

    <div class="tab-content bg-white p-3 border border-top-0 rounded-bottom shadow-sm" id="pagosTabsContent">

        <?php
        // Array para generar las tres tablas dinámicamente y no repetir tanto código HTML
        $secciones = [
            ['id' => 'pendientes', 'estado_filtro' => 'Pendiente', 'activa' => true],
            ['id' => 'aprobados', 'estado_filtro' => 'Comprobado', 'activa' => false],
            ['id' => 'rechazados', 'estado_filtro' => 'Rechazado', 'activa' => false]
        ];

        foreach ($secciones as $sec):
            ?>
            <div class="tab-pane fade <?= $sec['activa'] ? 'show active' : '' ?>" id="<?= $sec['id'] ?>" role="tabpanel"
                aria-labelledby="<?= $sec['id'] ?>-tab">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover w-100 text-center align-middle mb-0 tabla-pagos">
                        <thead class="thead-dark">
                            <tr>
                                <th>Fecha Pago (Oculta para filtro)</th>
                                <th>Fecha</th>
                                <th>Usuario</th>
                                <th>Curso / Diplomado</th>
                                <th>Moneda</th>
                                <th>Referencia / Banco</th>
                                <th>Monto</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $hayDatos = false;
                            foreach ($todos_comprobantes as $comp):
                                if ($comp['estado'] !== $sec['estado_filtro'])
                                    continue;
                                $hayDatos = true;
                                ?>
                                <tr>
                                    <td style="display:none;"><?= date('Y-m-d', strtotime($comp['fecha_pago'])) ?></td>
                                    <td data-sort="<?= date('Y-m-d', strtotime($comp['fecha_pago'])) ?>">
                                        <?= date('d/m/Y', strtotime($comp['fecha_pago'])) ?><br>
                                        <small class="text-muted">Subido:
                                            <?= date('d/m/Y H:i', strtotime($comp['fecha_subida'])) ?></small>
                                    </td>
                                    <td class="text-left">
                                        <strong><?= h($comp['apellido'] . ', ' . $comp['nombre']) ?></strong><br>
                                        <span class="badge badge-secondary"><?= h($comp['cedula']) ?></span>
                                    </td>
                                    <td class="text-left font-weight-bold text-primary">
                                        <?= h($comp['nombre_curso']) ?>
                                    </td>
                                    <td>
                                        <?php if (isset($comp['moneda']) && $comp['moneda'] === 'Divisas'): ?>
                                            <span class="badge badge-success">Divisas</span>
                                        <?php else: ?>
                                            <span class="badge badge-primary">Bs.</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= h($comp['numero_operacion'] ? $comp['numero_operacion'] : 'N/A') ?></strong><br>
                                        <small class="text-muted"><?= h($comp['banco_origen']) ?></small>
                                        <?php if (!empty($comp['observacion'])): ?>
                                            <hr class="m-1">
                                            <small class="text-secondary d-block" style="max-width:150px; white-space: normal;">
                                                <b>Obs:</b> <?= h($comp['observacion']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <span class="text-success font-weight-bold" style="font-size: 1.1rem;">
                                            <?php 
                                            $simbolo = (isset($comp['moneda']) && $comp['moneda'] === 'Divisas') ? '$' : 'Bs.';
                                            echo $simbolo . number_format($comp['monto'], 2);
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-info shadow-sm"
                                                onclick="verComprobante('<?= h($comp['archivo_ruta']) ?>')"
                                                title="Ver Comprobante">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            <?php if ($sec['estado_filtro'] === 'Pendiente'): ?>
                                                <button type="button" class="btn btn-sm btn-success shadow-sm"
                                                    onclick="actualizarEstadoPago(<?= $comp['id_comprobante'] ?>, 'Comprobado')"
                                                    title="Aprobar">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger shadow-sm"
                                                    onclick="actualizarEstadoPago(<?= $comp['id_comprobante'] ?>, 'Rechazado')"
                                                    title="Rechazar">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-warning shadow-sm"
                                                    onclick="actualizarEstadoPago(<?= $comp['id_comprobante'] ?>, 'Pendiente')"
                                                    title="Revertir a Pendiente">
                                                    <i class="fas fa-undo text-dark"></i>
                                                </button>
                                            <?php endif; ?>

                                            <button type="button" class="btn btn-sm btn-secondary shadow-sm"
                                                onclick="abrirModalEditComprobanteAdmin(<?= $comp['id_comprobante'] ?>, '<?= h($comp['banco_origen']) ?>', '<?= h(isset($comp['numero_operacion']) ? $comp['numero_operacion'] : '') ?>', <?= $comp['monto'] ?>, '<?= date('Y-m-d', strtotime($comp['fecha_pago'])) ?>', '<?= isset($comp['moneda']) ? $comp['moneda'] : 'Bs' ?>', this)"
                                                title="Modificar datos del pago">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <button type="button" class="btn btn-sm btn-dark shadow-sm"
                                                onclick="eliminarComprobanteAdmin(<?= $comp['id_comprobante'] ?>)"
                                                title="Eliminar permanentemente">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
</div>

<div class="modal fade" id="modalVisorComprobante" tabindex="-1" role="dialog" aria-labelledby="visorModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="visorModalLabel"><i class="fas fa-file-invoice-dollar me-2"></i>
                    Previsualización de Comprobante</h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center bg-light" id="visorContenido">
            </div>
            <div class="modal-footer justify-content-between">
                <div>
                    <a href="#" id="btnDescargarComprobante" class="btn btn-success" download><i
                            class="fas fa-download"></i> Descargar</a>
                    <button type="button" id="btnImprimirComprobante" class="btn btn-info text-white"><i
                            class="fas fa-print"></i> Imprimir</button>
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar Visor</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarComprobanteAdmin" tabindex="-1" role="dialog" aria-hidden="true"
    data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Editar Pago (Admin)</h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEditarPagoAdmin" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="editar_comprobante">
                    <input type="hidden" name="id_comprobante" id="admin_edit_id_comprobante">

                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label>Moneda:</label>
                            <select name="moneda" id="admin_edit_moneda" class="form-control" onchange="toggleReferenciaAdmin()" required>
                                <option value="Bs" selected>Bolívares (Bs.)</option>
                                <option value="Divisas">Divisas ($)</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label>Banco de Origen:</label>
                            <input type="text" name="banco_origen" id="admin_edit_banco_origen" class="form-control"
                                required>
                        </div>
                        <div class="col-md-4 form-group mb-3" id="admin_grupo_referencia">
                            <label>N° de Referencia:</label>
                            <input type="text" name="numero_operacion" id="admin_edit_numero_operacion"
                                class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label>Monto Pagado:</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text" id="admin_edit_monto_simbolo">$</span></div>
                                <input type="number" step="0.01" name="monto" id="admin_edit_monto" class="form-control"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label>Fecha del Pago:</label>
                            <input type="date" name="fecha_pago" id="admin_edit_fecha_pago" class="form-control"
                                required>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label>Reemplazar Archivo (Opcional):</label>
                        <input type="file" name="comprobante_archivo" class="form-control-file border p-2 rounded"
                            accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted">Si no seleccionas un archivo, se mantendrá el comprobante
                            original.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="guardarEdicionComprobanteAdmin()">
                    <i class="fas fa-save me-2"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // 1. Evitar acumulación de filtros al recargar esta vista por AJAX múltiples veces
        if ($.fn.dataTable.ext && $.fn.dataTable.ext.search) {
            $.fn.dataTable.ext.search = [];
        }

        // Definir función de filtrado personalizada para fechas y cursos
        $.fn.dataTable.ext.search.push(
            function (settings, data, dataIndex) {
                let fechaInicio = $('#fechaDesde').val();
                let fechaFin = $('#fechaHasta').val();
                let cursoFiltrado = $('#cursoFiltro').val();

                // Usamos una expresión regular para extraer extraer la primera fecha DD/MM/YYYY de la columna visible 1
                // (Debido a que DataTables puede vaciar data[0] si tiene searchable: false)
                let rowDateStr = "";
                let match = (data[1] || "").match(/(\d{2})\/(\d{2})\/(\d{4})/);
                if (match) {
                    rowDateStr = match[3] + "-" + match[2] + "-" + match[1]; // YYYY-MM-DD
                }

                let rowCourse = (data[3] || "").trim();

                let ocultar = false;

                // Filtro por Curso
                if (cursoFiltrado !== "" && rowCourse !== cursoFiltrado) {
                    ocultar = true;
                }

                // Filtro por Fecha (sólo si logramos extraer la fecha de la celda)
                if (rowDateStr) {
                    let rowTs = new Date(rowDateStr + "T00:00:00").getTime();
                    
                    if (fechaInicio !== "") {
                        let inicioTs = new Date(fechaInicio + "T00:00:00").getTime();
                        if (!isNaN(rowTs) && !isNaN(inicioTs) && rowTs < inicioTs) {
                            ocultar = true;
                        }
                    }
                    
                    if (fechaFin !== "") {
                        let finTs = new Date(fechaFin + "T23:59:59").getTime();
                        if (!isNaN(rowTs) && !isNaN(finTs) && rowTs > finTs) {
                            ocultar = true;
                        }
                    }
                }

                return !ocultar;
            }
        );

        // Inicializar Tablas
        var tables = $('.tabla-pagos').DataTable({
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
                select: {
                    rows: {
                        _: "%d filas seleccionadas",
                        0: "Haga clic en una fila para seleccionarla",
                        1: "1 fila seleccionada"
                    }
                }
            },
            select: {
                style: 'multi',
                selector: 'td' // click on any cell selects the row
            },
            // l = length changing, B = Buttons, f = filtering, tr = table, i = info, p = pagination
            dom: '<"row"<"col-sm-12 col-md-4"l><"col-sm-12 col-md-4 text-center"B><"col-sm-12 col-md-4"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
            pageLength: 25,
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success btn-sm mb-2 mr-1',
                    title: function () {
                        let tabName = $('.nav-tabs .active').text().replace(/[0-9]/g, '').trim();
                        return 'Reporte de Pagos - ' + tabName;
                    },
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5]
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-danger btn-sm mb-2 mr-1',
                    title: function () {
                        let tabName = $('.nav-tabs .active').text().replace(/[0-9]/g, '').trim();
                        return 'Reporte de Pagos - ' + tabName;
                    },
                    orientation: 'landscape',
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5]
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Imprimir',
                    className: 'btn btn-secondary btn-sm mb-2',
                    title: function () {
                        let tabName = $('.nav-tabs .active').text().replace(/[0-9]/g, '').trim();
                        return 'Reporte de Pagos - ' + tabName;
                    },
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5]
                    }
                }
            ],
            columnDefs: [
                { target: 0, visible: false, searchable: false }
            ]
        });

        // Evento dinámico previo a la exportación para verificar si usamos "todas las filtradas" o "solo las seleccionadas"
        tables.on('buttons-action', function (e, buttonApi, dataTable, node, config) {
            let countSelected = dataTable.rows({ selected: true }).count();
            if (countSelected > 0) {
                config.exportOptions.modifier = { selected: true }; // Exportar solo los checks
            } else {
                config.exportOptions.modifier = { search: 'applied' }; // Exportar todo el filtro actual
            }
        });

        // Refrescar filtro al cambiar input
        $('#fechaDesde, #fechaHasta, #cursoFiltro').on('change', function () {
            tables.draw();
            tables.rows().deselect(); // limpiar selección al filtrar diferente
        });

        // Botón Limpiar
        $('#btnLimpiarFiltros').on('click', function () {
            $('#fechaDesde').val('');
            $('#fechaHasta').val('');
            $('#cursoFiltro').val('');
            tables.rows().deselect();
            tables.search('').columns().search('').draw();
        });

        // Limpieza del Modal al cerrar para evitar solapamientos visuales
        $('#modalVisorComprobante').on('hidden.bs.modal', function () {
            let btnImp = document.getElementById('btnImprimirComprobante');
            if (btnImp) { btnImp.onclick = null; }
        });
    });

    /**
     * Actualiza el estado del pago mediante AJAX
         */
    function actualizarEstadoPago(idComprobante, nuevoEstado) {
        let accionFuerte = (nuevoEstado === 'Comprobado') ? 'APROBAR' : (nuevoEstado === 'Rechazado' ? 'RECHAZAR' : 'REVERTIR');

        if (!confirm(`¿Estás seguro de que deseas ${accionFuerte} este comprobante?`)) {
            return;
        }

        let observacion = null;
        if (nuevoEstado === 'Rechazado' || nuevoEstado === 'Comprobado') {
            observacion = prompt(`(Opcional) Introduce una observación o motivo para ${accionFuerte} el pago:`);
        }

        $.ajax({
            url: '../controllers/pagos_controlador.php',
            type: 'POST',
            data: {
                action: 'actualizar_estado_comprobante',
                id_comprobante: idComprobante,
                estado: nuevoEstado,
                observacion: observacion
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    alert(response.message);
                    loadPage('../views/gestion_pagos.php');
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert('Error de conexión con el servidor.');
            }
        });
    }

    function eliminarComprobanteAdmin(idComprobante) {
        if (!confirm('CAUCIÓN: ¿Seguro que deseas ELIMINAR permanentemente este comprobante? Se borrará el archivo y los datos del usuario. Esto no se puede deshacer.')) {
            return;
        }

        $.ajax({
            url: '../controllers/pagos_controlador.php',
            type: 'POST',
            data: { action: 'eliminar_comprobante', id_comprobante: idComprobante },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    alert('Comprobante eliminado exitosamente.');
                    loadPage('../views/gestion_pagos.php');
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert('Error al intentar eliminar el comprobante.');
            }
        });
    }

    function descargarBackupComprobantes() {
        if (!confirm('Esta operación preparará un archivo TAR con todos los comprobantes. Puede tomar varios segundos dependiendo de la cantidad de archivos. ¿Deseas continuar?')) {
            return;
        }
        // Se llama directamente ya que devolverá un archivo descargable
        window.location.href = '../controllers/pagos_controlador.php?action=backup_comprobantes';
    }

    function limpiarTodosLosComprobantes() {
        if (!confirm('PELIGRO INMINENTE: ¿Estás totalmente seguro de borrar TODOS los comprobantes almacenados en el servidor? Perderás todo el historial físico en PDF y fotos. ¡Procede bajo tu propio riesgo!')) {
            return;
        }

        let confirmacion2 = prompt('Por seguridad, escribe ELIMINAR TODO para confirmar la acción:');
        if (confirmacion2 !== 'ELIMINAR TODO') {
            alert('Limpieza cancelada.');
            return;
        }

        $.ajax({
            url: '../controllers/pagos_controlador.php',
            type: 'POST',
            data: { action: 'limpiar_comprobantes' },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    alert('Limpieza ejecutada: ' + response.message);
                    loadPage('../views/gestion_pagos.php');
                } else {
                    alert('Error en limpieza: ' + response.message);
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert('Error al intentar vaciar el servidor.');
            }
        });
    }

    /**
     * Muestra el comprobante en el modal, detectando si es imagen o PDF
     */
    function verComprobante(rutaParcial) {
        const urlCompleta = '../public/' + rutaParcial;
        const extension = rutaParcial.split('.').pop().toLowerCase();
        const contenedor = document.getElementById('visorContenido');
        let html = '';

        if (['jpg', 'jpeg', 'png'].includes(extension)) {
            html = `<img src="${urlCompleta}" class="img-fluid rounded shadow-sm" style="max-height: 70vh; object-fit: contain;" alt="Comprobante de Pago">`;
        } else if (extension === 'pdf') {
            html = `<iframe src="${urlCompleta}" width="100%" height="500px" style="border: none;" class="shadow-sm rounded"></iframe>`;
        } else {
            html = `<div class="alert alert-warning">No se puede previsualizar este tipo de archivo.</div>`;
        }

        // Configurar botón Descargar
        let btnDescargar = document.getElementById('btnDescargarComprobante');
        if (btnDescargar) {
            btnDescargar.href = urlCompleta;
        }

        // Configurar botón Imprimir
        let btnImprimir = document.getElementById('btnImprimirComprobante');
        if (btnImprimir) {
            btnImprimir.onclick = function () {
                imprimirArchivoComprobante(urlCompleta, extension);
            };
        }

        contenedor.innerHTML = html;

        const modalEl = $('#modalVisorComprobante');
        if (modalEl.parent().prop('tagName') !== 'BODY') {
            modalEl.appendTo('body');
        }

        modalEl.modal('show');
    }

    /**
     * Abre una ventana/iframe e imprime el comprobante directamente
     */
    function imprimirArchivoComprobante(url, ext) {
        if (['jpg', 'jpeg', 'png'].includes(ext)) {
            let win = window.open('');
            win.document.write('<html><head><title>Imprimir Comprobante</title></head><body style="text-align:center; padding: 20px;">');
            win.document.write('<img src="' + url + '" style="max-width:100%; height:auto;" onload="window.print();window.close();" />');
            win.document.write('</body></html>');
            win.document.close();
        } else if (ext === 'pdf') {
            // El visor nativo de PDF en el navegador manejará la impresión
            window.open(url, '_blank').print();
        } else {
            alert('Formato no soportado para impresión directa. Descarga el archivo para imprimirlo.');
        }
    }

    function abrirModalEditComprobanteAdmin(id, banco, operacion, monto, fecha, moneda, btnObj) {
        window.filaEditadaAdmin = $(btnObj).closest('tr');
        document.getElementById('formEditarPagoAdmin').reset();
        document.getElementById('admin_edit_id_comprobante').value = id;
        document.getElementById('admin_edit_banco_origen').value = banco;
        document.getElementById('admin_edit_numero_operacion').value = operacion;
        document.getElementById('admin_edit_monto').value = monto;
        document.getElementById('admin_edit_fecha_pago').value = fecha;
        
        var editMoneda = document.getElementById('admin_edit_moneda');
        if(editMoneda) {
            editMoneda.value = moneda || 'Bs';
            toggleReferenciaAdmin();
        }

        $('#admin_edit_moneda').val(moneda || 'Bs').trigger('change');

        $('#modalEditarComprobanteAdmin').modal('show');
    }

    function toggleReferenciaAdmin() {
        var moneda = document.getElementById('admin_edit_moneda').value;
        var grupo = document.getElementById('admin_grupo_referencia');
        var refer = document.getElementById('admin_edit_numero_operacion');
        var simbolo = document.getElementById('admin_edit_monto_simbolo');

        if(moneda === 'Divisas') {
            grupo.style.display = 'none';
            refer.removeAttribute('required');
            refer.value = '';
            if (simbolo) simbolo.innerText = '$';
        } else {
            grupo.style.display = 'block';
            refer.setAttribute('required', 'required');
            if (simbolo) simbolo.innerText = 'Bs.';
        }
    }

    function guardarEdicionComprobanteAdmin() {
        var form = document.getElementById('formEditarPagoAdmin');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        if (!confirm("¿Guardar cambios en este comprobante? El estado actual no se verá afectado (Pendiente, Aprobado o Rechazado).")) {
            return;
        }

        var formData = new FormData(form);
        var btnSubmit = document.querySelector('#modalEditarComprobanteAdmin .btn-success');
        var originalText = btnSubmit.innerHTML;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Guardando...';

        $.ajax({
            url: '../controllers/pagos_controlador.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    $('#modalEditarComprobanteAdmin').modal('hide');
                    alert(response.message);

                    if (window.filaEditadaAdmin) {
                        let tr = window.filaEditadaAdmin;
                        let table = tr.closest('table').DataTable();
                        let row = table.row(tr);
                        let data = row.data();

                        let nuevoBanco = formData.get('banco_origen');
                        let nuevaRef = formData.get('numero_operacion') || '';
                        let nuevoMonto = parseFloat(formData.get('monto')).toFixed(2);
                        let nuevaFecha = formData.get('fecha_pago');
                        let partes = nuevaFecha.split('-');
                        let fechaFormat = `${partes[2]}/${partes[1]}/${partes[0]}`;

                        let nuevoMoneda = formData.get('moneda');
                        let simbolo = (nuevoMoneda === 'Divisas') ? '$' : 'Bs. ';
                        
                        let oldSubido = data[1].includes('Subido:') ? data[1].split('Subido:')[1].split('<')[0] : '';
                        data[1] = `${fechaFormat}<br><small class="text-muted">Subido:${oldSubido}</small>`;
                        data[0] = nuevaFecha;

                        data[4] = (nuevoMoneda === 'Divisas') ? '<span class="badge badge-success">Divisas</span>' : '<span class="badge badge-primary">Bs.</span>';

                        let oldObs = '';
                        if (data[5] && data[5].includes('<b>Obs:</b>')) {
                            oldObs = data[5].substring(data[5].indexOf('<hr class="m-1">'));
                        }
                        
                        let refMostrar = nuevaRef ? nuevaRef : '';
                        data[5] = `<strong>${refMostrar}</strong><br><small class="text-muted">${nuevoBanco}</small>${oldObs}`;
                        data[6] = `<span class="text-success font-weight-bold" style="font-size: 1.1rem;">${simbolo}${nuevoMonto}</span>`;

                        row.data(data).draw(false);

                        let editBtn = tr.find('button[title="Modificar datos del pago"]');
                        if (editBtn.length) {
                            editBtn.attr('onclick', `abrirModalEditComprobanteAdmin(${formData.get('id_comprobante')}, '${nuevoBanco}', '${nuevaRef}', ${nuevoMonto}, '${nuevaFecha}', '${nuevoMoneda}', this)`);
                        }
                    }
                } else {
                    alert('Error: ' + response.message);
                }
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalText;
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert('Ocurrió un error al guardar. Por favor, intenta de nuevo.');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalText;
            }
        });
    }
</script>