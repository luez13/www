<?php
// views/mis_pagos.php

require_once '../controllers/init.php';
// require_once '../controllers/autenticacion.php'; // Descomenta si usas este archivo para validar la sesión
require_once '../config/model.php';
require_once '../models/Pago.php';

if (!isset($_SESSION['user_id'])) {
    die('Acceso denegado.');
}

$user_id = $_SESSION['user_id'];
$db = new DB();
$pagoModel = new Pago($db);

// Obtener los cursos del usuario (Certificaciones)
$sqlCursos = "SELECT c.id_curso, c.nombre_curso, c.costo, cert.pago 
              FROM cursos.certificaciones cert
              JOIN cursos.cursos c ON cert.curso_id = c.id_curso
              WHERE cert.id_usuario = :user_id";
$stmtCursos = $db->getConn()->prepare($sqlCursos);
$stmtCursos->execute(['user_id' => $user_id]);
$misCursos = $stmtCursos->fetchAll(PDO::FETCH_ASSOC);

// Obtener información de pagos
$cuentasActivas = $pagoModel->obtenerCuentasActivas();
$historialPagos = $pagoModel->obtenerComprobantesPorUsuario($user_id);

// Función auxiliar para sanitizar salidas HTML
function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-wallet me-2"></i> Mis Pagos y Aranceles</h1>
    </div>

    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-dark text-white">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-university me-2"></i> Cuentas Destino (Dónde pagar)</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($cuentasActivas)): ?>
                        <div class="alert alert-warning">No hay cuentas bancarias registradas en este momento.</div>
                    <?php else: ?>
                        <div class="accordion" id="accordionCuentas">
                            <?php foreach ($cuentasActivas as $index => $cuenta): ?>
                                <div class="card mb-2 border-left-primary shadow-sm">
                                    <div class="card-header p-0" id="heading<?= $index ?>">
                                        <h2 class="mb-0">
                                            <button class="btn btn-link btn-block text-left text-dark text-decoration-none font-weight-bold" type="button" data-toggle="collapse" data-target="#collapse<?= $index ?>" aria-expanded="true" aria-controls="collapse<?= $index ?>">
                                                <?= h($cuenta['banco']) ?> <span class="badge badge-info float-right"><?= h($cuenta['tipo_cuenta']) ?></span>
                                            </button>
                                        </h2>
                                    </div>
                                    <div id="collapse<?= $index ?>" class="collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="heading<?= $index ?>" data-parent="#accordionCuentas">
                                        <div class="card-body text-sm">
                                            <p class="mb-1"><strong>Titular:</strong> <?= h($cuenta['titular']) ?></p>
                                            <p class="mb-1"><strong>Cédula/RIF:</strong> <?= h($cuenta['cedula_rif']) ?></p>
                                            
                                            <?php if (!empty($cuenta['telefono'])): ?>
                                                <p class="mb-1"><strong>Teléfono:</strong> <?= h($cuenta['telefono']) ?></p>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($cuenta['correo'])): ?>
                                                <p class="mb-1"><strong>Correo:</strong> <?= h($cuenta['correo']) ?></p>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($cuenta['numero_cuenta'])): ?>
                                                <p class="mb-1"><strong>N° de Cuenta:</strong> <?= h($cuenta['numero_cuenta']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-7 mb-4">
            <div class="card shadow mb-4 border-bottom-success">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-file-upload me-2"></i> Reportar Pago</h6>
                </div>
                <div class="card-body">
                    <form id="formSubirPago" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="subir_comprobante">
                        
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Seleccione el Curso / Diplomado:</label>
                            <select name="id_curso" class="form-control" required>
                                <option value="">-- Seleccione --</option>
                                <?php foreach ($misCursos as $c): ?>
                                    <?php 
                                        $estadoPago = $c['pago'] ? '(Pagado)' : '(Pendiente)';
                                        $costoTexto = $c['costo'] > 0 ? '$' . number_format($c['costo'], 2) : 'Gratis';
                                    ?>
                                    <option value="<?= $c['id_curso'] ?>">
                                        <?= h($c['nombre_curso']) ?> - <?= $costoTexto ?> <?= $estadoPago ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label>Banco de Origen:</label>
                                <input type="text" name="banco_origen" class="form-control" placeholder="Ej: Banesco, Mercantil, Zelle" required>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label>N° de Referencia / Operación:</label>
                                <input type="text" name="numero_operacion" class="form-control" placeholder="Ej: 12345678" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label>Monto Pagado:</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                    <input type="number" step="0.01" name="monto" class="form-control" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label>Fecha del Pago:</label>
                                <input type="date" name="fecha_pago" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label>Adjuntar Comprobante (PDF, JPG, PNG):</label>
                            <input type="file" name="comprobante_archivo" class="form-control-file border p-2 rounded" accept=".pdf,.jpg,.jpeg,.png" required>
                        </div>

                        <div class="text-right">
                            <button type="button" class="btn btn-success btn-lg shadow-sm" onclick="subirComprobantePago()">
                                <i class="fas fa-paper-plane me-2"></i> Enviar Comprobante
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-history me-2"></i> Mi Historial de Pagos</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover w-100 text-center align-middle">
                    <thead class="thead-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Curso / Diplomado</th>
                            <th>Referencia</th>
                            <th>Monto</th>
                            <th>Estado</th>
                            <th>Comprobante</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($historialPagos)): ?>
                            <tr><td colspan="6" class="text-muted py-4">Aún no has reportado ningún pago.</td></tr>
                        <?php else: ?>
                            <?php foreach ($historialPagos as $pago): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($pago['fecha_pago'])) ?></td>
                                    <td class="text-left font-weight-bold"><?= h($pago['nombre_curso']) ?></td>
                                    <td><?= h($pago['numero_operacion']) ?><br><small class="text-muted"><?= h($pago['banco_origen']) ?></small></td>
                                    <td>$<?= number_format($pago['monto'], 2) ?></td>
                                    <td>
                                        <?php 
                                            $badgeClass = 'badge-warning';
                                            if ($pago['estado'] === 'Comprobado') $badgeClass = 'badge-success';
                                            if ($pago['estado'] === 'Rechazado') $badgeClass = 'badge-danger';
                                        ?>
                                        <span class="badge <?= $badgeClass ?> p-2 px-3"><?= h($pago['estado']) ?></span>
                                    </td>
                                    <td>
                                        <a href="../public/<?= h($pago['archivo_ruta']) ?>" target="_blank" class="btn btn-sm btn-info shadow-sm">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
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

<script>
function subirComprobantePago() {
    // Validar HTML5 nativo antes de enviar
    var form = document.getElementById('formSubirPago');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    var formData = new FormData(form);
    
    // Deshabilitar el botón para evitar dobles envíos
    var btnSubmit = form.querySelector('button');
    var btnOriginalText = btnSubmit.innerHTML;
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Procesando...';

    $.ajax({
        url: '../controllers/pagos_controlador.php',
        type: 'POST',
        data: formData,
        processData: false, // Fundamental para enviar archivos
        contentType: false, // Fundamental para enviar archivos
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert(response.message);
                // Recargar la vista actual para reflejar la tabla actualizada
                loadPage('../views/mis_pagos.php'); 
            } else {
                alert('Error: ' + response.message);
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = btnOriginalText;
            }
        },
        error: function(xhr, status, error) {
            console.error(xhr.responseText);
            alert('Ocurrió un error al comunicarse con el servidor. Por favor, intenta de nuevo.');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = btnOriginalText;
        }
    });
}
</script>