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
function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-money-check-alt me-2"></i> Administración de Pagos</h1>
    </div>

    <ul class="nav nav-tabs mb-4" id="pagosTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active font-weight-bold text-warning" id="pendientes-tab" data-toggle="tab" href="#pendientes" role="tab" aria-controls="pendientes" aria-selected="true">
                <i class="fas fa-clock"></i> Pendientes 
                <span class="badge badge-warning ml-1"><?= $conteo['Pendiente'] ?></span>
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link font-weight-bold text-success" id="aprobados-tab" data-toggle="tab" href="#aprobados" role="tab" aria-controls="aprobados" aria-selected="false">
                <i class="fas fa-check-circle"></i> Comprobados 
                <span class="badge badge-success ml-1"><?= $conteo['Comprobado'] ?></span>
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link font-weight-bold text-danger" id="rechazados-tab" data-toggle="tab" href="#rechazados" role="tab" aria-controls="rechazados" aria-selected="false">
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
            <div class="tab-pane fade <?= $sec['activa'] ? 'show active' : '' ?>" id="<?= $sec['id'] ?>" role="tabpanel" aria-labelledby="<?= $sec['id'] ?>-tab">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover w-100 text-center align-middle mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>Usuario</th>
                                <th>Curso / Diplomado</th>
                                <th>Referencia / Banco</th>
                                <th>Monto</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $hayDatos = false;
                            foreach ($todos_comprobantes as $comp): 
                                if ($comp['estado'] !== $sec['estado_filtro']) continue;
                                $hayDatos = true;
                            ?>
                                <tr>
                                    <td>
                                        <?= date('d/m/Y', strtotime($comp['fecha_pago'])) ?><br>
                                        <small class="text-muted">Subido: <?= date('d/m/Y H:i', strtotime($comp['fecha_subida'])) ?></small>
                                    </td>
                                    <td class="text-left">
                                        <strong><?= h($comp['apellido'] . ', ' . $comp['nombre']) ?></strong><br>
                                        <span class="badge badge-secondary"><?= h($comp['cedula']) ?></span>
                                    </td>
                                    <td class="text-left font-weight-bold text-primary">
                                        <?= h($comp['nombre_curso']) ?>
                                    </td>
                                    <td>
                                        <strong><?= h($comp['numero_operacion']) ?></strong><br>
                                        <small class="text-muted"><?= h($comp['banco_origen']) ?></small>
                                    </td>
                                    <td class="align-middle">
                                        <span class="text-success font-weight-bold" style="font-size: 1.1rem;">
                                            $<?= number_format($comp['monto'], 2) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-info shadow-sm" onclick="verComprobante('<?= h($comp['archivo_ruta']) ?>')" title="Ver Comprobante">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            <?php if ($sec['estado_filtro'] === 'Pendiente'): ?>
                                                <button type="button" class="btn btn-sm btn-success shadow-sm" onclick="actualizarEstadoPago(<?= $comp['id_comprobante'] ?>, 'Comprobado')" title="Aprobar">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger shadow-sm" onclick="actualizarEstadoPago(<?= $comp['id_comprobante'] ?>, 'Rechazado')" title="Rechazar">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-warning shadow-sm" onclick="actualizarEstadoPago(<?= $comp['id_comprobante'] ?>, 'Pendiente')" title="Revertir a Pendiente">
                                                    <i class="fas fa-undo text-dark"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (!$hayDatos): ?>
                                <tr>
                                    <td colspan="6" class="text-muted py-4">No hay comprobantes en esta categoría.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
</div>

<div class="modal fade" id="modalVisorComprobante" tabindex="-1" role="dialog" aria-labelledby="visorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="visorModalLabel"><i class="fas fa-file-invoice-dollar me-2"></i> Previsualización de Comprobante</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center bg-light" id="visorContenido">
                </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar Visor</button>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * Actualiza el estado del pago mediante AJAX
 */
function actualizarEstadoPago(idComprobante, nuevoEstado) {
    let accionFuerte = (nuevoEstado === 'Comprobado') ? 'APROBAR' : (nuevoEstado === 'Rechazado' ? 'RECHAZAR' : 'REVERTIR');
    
    if (!confirm(`¿Estás seguro de que deseas ${accionFuerte} este comprobante?`)) {
        return;
    }

    $.ajax({
        url: '../controllers/pagos_controlador.php',
        type: 'POST',
        data: {
            action: 'actualizar_estado_comprobante',
            id_comprobante: idComprobante,
            estado: nuevoEstado
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Notificar éxito y recargar la vista actual en el dashboard
                alert(response.message);
                loadPage('../views/gestion_pagos.php');
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr) {
            console.error(xhr.responseText);
            alert('Error de conexión con el servidor. Revisa la consola para más detalles.');
        }
    });
}

/**
 * Muestra el comprobante en el modal, detectando si es imagen o PDF
 */
function verComprobante(rutaParcial) {
    // La ruta relativa que se guarda en BD es "assets/comprobantes/archivo.ext"
    // Debemos ajustarla para que se lea desde el directorio public actual
    const urlCompleta = '../public/' + rutaParcial;
    
    // Obtener la extensión del archivo
    const extension = rutaParcial.split('.').pop().toLowerCase();
    const contenedor = $('#visorContenido');
    
    let html = '';

    if (['jpg', 'jpeg', 'png'].includes(extension)) {
        // Es una imagen
        html = `<img src="${urlCompleta}" class="img-fluid rounded shadow-sm" style="max-height: 70vh; object-fit: contain;" alt="Comprobante de Pago">`;
    } else if (extension === 'pdf') {
        // Es un PDF
        html = `<iframe src="${urlCompleta}" width="100%" height="500px" style="border: none;" class="shadow-sm rounded"></iframe>`;
    } else {
        // Formato desconocido (fallback)
        html = `<div class="alert alert-warning">No se puede previsualizar este tipo de archivo. <a href="${urlCompleta}" target="_blank" class="font-weight-bold">Haz clic aquí para descargarlo</a>.</div>`;
    }

    // Inyectar contenido y mostrar modal
    contenedor.html(html);
    $('#modalVisorComprobante').modal('show');
}
</script>