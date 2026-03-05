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
function h($str)
{
    return htmlspecialchars(isset($str) ? $str : '', ENT_QUOTES, 'UTF-8');
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
                                            <button
                                                class="btn btn-link btn-block text-left text-dark text-decoration-none font-weight-bold"
                                                type="button" data-toggle="collapse" data-target="#collapse<?= $index ?>"
                                                aria-expanded="true" aria-controls="collapse<?= $index ?>">
                                                <?= h($cuenta['banco']) ?> <span
                                                    class="badge badge-info float-right"><?= h($cuenta['tipo_cuenta']) ?></span>
                                            </button>
                                        </h2>
                                    </div>
                                    <div id="collapse<?= $index ?>" class="collapse <?= $index === 0 ? 'show' : '' ?>"
                                        aria-labelledby="heading<?= $index ?>" data-parent="#accordionCuentas">
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
                            <select name="id_curso" id="select_curso" class="form-control" required
                                onchange="cargarMaterias(this.value)">
                                <option value="">-- Seleccione --</option>
                                <?php foreach ($misCursos as $c): ?>
                                    <?php
                                    $estadoPago = $c['pago'] ? '(Pagado)' : '(Pendiente)';
                                    $costoTexto = $c['costo'] > 0 ? '$' . number_format($c['costo'], 2) : 'Gratis';
                                    ?>
                                    <option value="<?= $c['id_curso'] ?>">
                                        <?= h($c['nombre_curso']) ?> - <?= $costoTexto ?>    <?= $estadoPago ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group mb-3" id="contenedorMaterias" style="display: none;">
                            <label class="font-weight-bold">Seleccione la Materia (Opcional - Solo para pagos individuales):</label>
                            <select name="id_materia_bimestre" id="select_materia" class="form-control">
                                <option value="">-- Pago general del diplomado --</option>
                            </select>
                            <small class="form-text text-muted">Si va a pagar una materia individual, selecciónela aquí. De lo contrario, déjelo en "Pago general del diplomado".</small>
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group mb-3">
                                <label>Moneda:</label>
<<<<<<< HEAD
                                <select name="moneda" id="select_moneda" class="form-control" required onchange="toggleReferencia()">
                                    <option value="Bs">Bolívares (Bs)</option>
                                    <option value="Divisas">Divisas ($)</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label>Banco de Origen:</label>
                                <input type="text" name="banco_origen" class="form-control" placeholder="Ej: Banesco, Zelle" required>
                            </div>
                            <div class="col-md-4 form-group mb-3" id="grupo_referencia">
                                <label>N° de Referencia / Operación:</label>
                                <input type="text" name="numero_operacion" id="input_numero_operacion" class="form-control" placeholder="Ej: 12345678" required>
=======
                                <select name="moneda" id="select_moneda" class="form-control" required>
                                    <option value="Bs" selected>Bolívares (Bs.)</option>
                                    <option value="Divisas">Divisas ($)</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label>Banco / Origen:</label>
                                <input type="text" name="banco_origen" id="input_banco_origen" class="form-control"
                                    placeholder="Ej: Banesco, Mercantil, Zelle" required>
                            </div>
                            <div class="col-md-4 form-group mb-3" id="div_numero_operacion">
                                <label>N° de Referencia / Operación:</label>
                                <input type="text" name="numero_operacion" id="input_numero_operacion"
                                    class="form-control" placeholder="Ej: 12345678" required>
>>>>>>> a36c9933a7dd692c01d2eebc6c6f456c203d7e0a
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label>Monto Pagado:</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="monto" class="form-control" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label>Fecha del Pago:</label>
                                <input type="date" name="fecha_pago" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group mb-4">
<<<<<<< HEAD
                            <label>Adjuntar Comprobante (PDF, JPG, PNG) <span class="text-muted">(Opcional)</span>:</label>
                            <input type="file" name="comprobante_archivo" class="form-control-file border p-2 rounded" accept=".pdf,.jpg,.jpeg,.png">
=======
                            <label>Adjuntar Comprobante (PDF, JPG, PNG) (Opcional):</label>
                            <input type="file" name="comprobante_archivo" class="form-control-file border p-2 rounded"
                                accept=".pdf,.jpg,.jpeg,.png">
>>>>>>> a36c9933a7dd692c01d2eebc6c6f456c203d7e0a
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
                            <th>Monto</th>
                            <th>Moneda</th>
                            <th>Referencia</th>
                            <th>Estado</th>
                            <th>Comprobante</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($historialPagos)): ?>
                            <tr>
                                <td colspan="6" class="text-muted py-4">Aún no has reportado ningún pago.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($historialPagos as $pago): ?>
                                <tr>
<<<<<<< HEAD
                                    <td data-sort="<?= date('Y-m-d', strtotime($pago['fecha_subida'] ?? 'now')) ?>">
                                        <?= date('d/m/Y', strtotime($pago['fecha_pago'] ?? 'now')) ?><br>
                                        <small class="text-muted">Subido: <?= date('d/m/Y H:i', strtotime($pago['fecha_subida'] ?? 'now')) ?></small>
                                    </td>
                                    <td class="text-left font-weight-bold"><?= h($pago['nombre_curso']) ?></td>
                                    <td><?= h($pago['numero_operacion']) ?><br><small class="text-muted"><?= h($pago['banco_origen']) ?></small></td>
                                    <td>
                                        <?= (isset($pago['moneda']) && $pago['moneda'] === 'Divisas') ? '$' : 'Bs. ' ?><?= number_format($pago['monto'], 2) ?>
                                    </td>
=======
                                    <td
                                        data-sort="<?= date('Y-m-d', strtotime(isset($pago['fecha_subida']) ? $pago['fecha_subida'] : 'now')) ?>">
                                        <?= date('d/m/Y', strtotime(isset($pago['fecha_pago']) ? $pago['fecha_pago'] : 'now')) ?><br>
                                        <small class="text-muted">Subido:
                                            <?= date('d/m/Y H:i', strtotime(isset($pago['fecha_subida']) ? $pago['fecha_subida'] : 'now')) ?></small>
                                    </td>
                                    <td class="text-left font-weight-bold"><?= h($pago['nombre_curso']) ?></td>
                                    <td>$<?= number_format($pago['monto'], 2) ?></td>
>>>>>>> a36c9933a7dd692c01d2eebc6c6f456c203d7e0a
                                    <td>
                                        <?php if (isset($pago['moneda']) && $pago['moneda'] === 'Divisas'): ?>
                                            <span class="badge badge-success">Divisas</span>
                                        <?php else: ?>
                                            <span class="badge badge-primary">Bs.</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= h($pago['numero_operacion'] ? $pago['numero_operacion'] : 'N/A') ?><br><small
                                            class="text-muted"><?= h($pago['banco_origen']) ?></small></td>
                                    <td>
                                        <?php
                                        $badgeClass = 'badge-warning';
                                        if ($pago['estado'] === 'Comprobado') $badgeClass = 'badge-success';
                                        if ($pago['estado'] === 'Rechazado') $badgeClass = 'badge-danger';
                                        ?>
                                        <span class="badge <?= $badgeClass ?> p-2 px-3"><?= h($pago['estado']) ?></span>
                                        <?php if (!empty($pago['observacion'])): ?>
                                            <div class="mt-2 small text-muted font-italic text-wrap text-break" style="max-width: 150px; margin: 0 auto; line-height: 1.1;">
                                                <?= h($pago['observacion']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <a href="../public/<?= h($pago['archivo_ruta']) ?>" target="_blank" class="btn btn-sm btn-info shadow-sm mb-1">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                            <?php if ($pago['estado'] === 'Pendiente' || $pago['estado'] === 'Rechazado'): ?>
                                                <button type="button" class="btn btn-sm btn-warning shadow-sm mb-1 text-dark"
<<<<<<< HEAD
                                                    onclick="abrirModalEditComprobante(<?= $pago['id_comprobante'] ?>, '<?= h($pago['banco_origen']) ?>', '<?= h($pago['numero_operacion']) ?>', <?= $pago['monto'] ?>, '<?= date('Y-m-d', strtotime($pago['fecha_pago'])) ?>', '<?= h($pago['moneda'] ?? 'Bs') ?>')">
=======
                                                    onclick="abrirModalEditComprobante(<?= $pago['id_comprobante'] ?>, '<?= h($pago['banco_origen']) ?>', '<?= h($pago['numero_operacion'] ? $pago['numero_operacion'] : '') ?>', <?= $pago['monto'] ?>, '<?= date('Y-m-d', strtotime($pago['fecha_pago'])) ?>', '<?= isset($pago['moneda']) ? $pago['moneda'] : 'Bs' ?>')">
>>>>>>> a36c9933a7dd692c01d2eebc6c6f456c203d7e0a
                                                    <i class="fas fa-edit"></i> Editar
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger shadow-sm mb-1" onclick="eliminarMiComprobante(<?= $pago['id_comprobante'] ?>)">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </button>
                                            <?php endif; ?>
                                        </div>
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

<div class="modal fade" id="modalEditarComprobante" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-dark"><i class="fas fa-edit me-2"></i> Editar Pago</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEditarPago" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="editar_comprobante">
                    <input type="hidden" name="origen" value="usuario">
                    <input type="hidden" name="id_comprobante" id="edit_id_comprobante">

                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label>Moneda:</label>
<<<<<<< HEAD
                            <select name="moneda" id="edit_moneda" class="form-control" required onchange="toggleReferenciaEdit()">
                                <option value="Bs">Bolívares (Bs)</option>
=======
                            <select name="moneda" id="edit_moneda" class="form-control" required>
                                <option value="Bs">Bolívares (Bs.)</option>
>>>>>>> a36c9933a7dd692c01d2eebc6c6f456c203d7e0a
                                <option value="Divisas">Divisas ($)</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label>Banco de Origen:</label>
                            <input type="text" name="banco_origen" id="edit_banco_origen" class="form-control" required>
                        </div>
<<<<<<< HEAD
                        <div class="col-md-4 form-group mb-3" id="edit_grupo_referencia">
=======
                        <div class="col-md-4 form-group mb-3" id="edit_div_numero_operacion">
>>>>>>> a36c9933a7dd692c01d2eebc6c6f456c203d7e0a
                            <label>N° de Referencia:</label>
                            <input type="text" name="numero_operacion" id="edit_numero_operacion" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label>Monto Pagado:</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="monto" id="edit_monto" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label>Fecha del Pago:</label>
                            <input type="date" name="fecha_pago" id="edit_fecha_pago" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group mb-2">
                        <label>Actualizar Archivo <span class="text-muted">(Opcional)</span>:</label>
                        <input type="file" name="comprobante_archivo" class="form-control-file border p-2 rounded" accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted">Si no seleccionas un archivo, se mantendrá el comprobante actual.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="guardarEdicionComprobante()">
                    <i class="fas fa-save me-2"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Ocultar al reportar nuevo
    function toggleReferencia() {
        var moneda = document.getElementById('select_moneda').value;
        var grupo = document.getElementById('grupo_referencia');
        var refer = document.getElementById('input_numero_operacion');
        if(moneda === 'Divisas') {
            grupo.style.display = 'none';
            refer.removeAttribute('required');
            refer.value = '';
        } else {
            grupo.style.display = 'block';
            refer.setAttribute('required', 'required');
        }
    }

    // Ocultar al editar
    function toggleReferenciaEdit() {
        var moneda = document.getElementById('edit_moneda').value;
        var grupo = document.getElementById('edit_grupo_referencia');
        var refer = document.getElementById('edit_numero_operacion');
        if(moneda === 'Divisas') {
            grupo.style.display = 'none';
            refer.removeAttribute('required');
            refer.value = '';
        } else {
            grupo.style.display = 'block';
            refer.setAttribute('required', 'required');
        }
    }

    function eliminarMiComprobante(idComprobante) {
        if (!confirm('¿Seguro que deseas eliminar este comprobante? Tendrás que reportar el pago de nuevo.')) {
            return;
        }

        $.ajax({
            url: '../controllers/pagos_controlador.php',
            type: 'POST',
            data: { action: 'eliminar_comprobante', id_comprobante: idComprobante },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    alert('Comprobante eliminado.');
                    loadPage('../views/mis_pagos.php');
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert('Error al intentar eliminar.');
            }
        });
    }

<<<<<<< HEAD
    function abrirModalEditComprobante(id, banco, operacion, monto, fecha, moneda) {
=======
    function abrirModalEditComprobante(id, banco, operacion, monto, fecha, moneda = 'Bs') {
>>>>>>> a36c9933a7dd692c01d2eebc6c6f456c203d7e0a
        document.getElementById('formEditarPago').reset();
        
        document.getElementById('edit_id_comprobante').value = id;
        document.getElementById('edit_banco_origen').value = banco;
        document.getElementById('edit_numero_operacion').value = operacion;
        document.getElementById('edit_monto').value = monto;
        document.getElementById('edit_fecha_pago').value = fecha;
        document.getElementById('edit_moneda').value = moneda;

<<<<<<< HEAD
        toggleReferenciaEdit(); // Ejecuta la lógica visual de ocultar/mostrar dependiendo de la moneda
=======
        // Trigger change to resolve visibility of fields
        $('#edit_moneda').trigger('change');
>>>>>>> a36c9933a7dd692c01d2eebc6c6f456c203d7e0a

        $('#modalEditarComprobante').modal('show');
    }

    function guardarEdicionComprobante() {
        var form = document.getElementById('formEditarPago');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        if (!confirm("¿Guardar cambios? Al editar, el pago volverá a estado Pendiente para revisión.")) {
            return;
        }

        var formData = new FormData(form);
        var btnSubmit = document.querySelector('#modalEditarComprobante .btn-success');
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
                    $('#modalEditarComprobante').modal('hide');
                    alert(response.message);
                    loadPage('../views/mis_pagos.php');
                } else {
                    alert('Error: ' + response.message);
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = originalText;
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert('Ocurrió un error al guardar. Por favor, intenta de nuevo.');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalText;
            }
        });
    }

    function cargarMaterias(idCurso) {
        var selectMateria = document.getElementById('select_materia');
        var contenedorMaterias = document.getElementById('contenedorMaterias');

        // Reiniciar y ocultar
        selectMateria.innerHTML = '<option value="">-- Pago general del diplomado --</option>';
        contenedorMaterias.style.display = 'none';

        if (!idCurso) return;

        $.ajax({
            url: '../controllers/pagos_controlador.php',
            type: 'POST',
            data: { action: 'obtener_materias', id_curso: idCurso },
            dataType: 'json',
            success: function (response) {
                if (response.success && response.data && response.data.length > 0) {
                    response.data.forEach(function (materia) {
                        var option = document.createElement('option');
                        option.value = materia.id_materia_bimestre;
                        option.text = 'Bimestre ' + materia.lapso_academico + ' - ' + materia.nombre_materia;
                        selectMateria.appendChild(option);
                    });
                    contenedorMaterias.style.display = 'block';
                }
            },
            error: function (xhr) {
                console.error('Error al cargar materias:', xhr.responseText);
            }
        });
    }

    function subirComprobantePago() {
        var form = document.getElementById('formSubirPago');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        if (!confirm("¿Estás seguro de que quieres subir este comprobante de pago con estos datos?")) {
            return;
        }

        var formData = new FormData(form);
        var btnSubmit = form.querySelector('button');
        var btnOriginalText = btnSubmit.innerHTML;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Procesando...';

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
                    loadPage('../views/mis_pagos.php');
                } else {
                    alert('Error: ' + response.message);
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = btnOriginalText;
                }
            },
            error: function (xhr, status, error) {
                console.error(xhr.responseText);
                alert('Ocurrió un error al comunicarse con el servidor. Por favor, intenta de nuevo.');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = btnOriginalText;
            }
        });
    }

    function alternarCamposMoneda(selectId, bancoId, numOpDivId, numOpInputId) {
        const moneda = $('#' + selectId).val();
        if (moneda === 'Divisas') {
            $('#' + numOpDivId).hide();
            $('#' + numOpInputId).removeAttr('required');
            $('#' + bancoId).val('Taquilla de la Universidad').prop('readonly', true);
        } else {
            $('#' + numOpDivId).show();
            $('#' + numOpInputId).attr('required', true);
            // Solo limpia si estaba fijado en Taquilla
            if ($('#' + bancoId).val() === 'Taquilla de la Universidad') {
                $('#' + bancoId).val('');
            }
            $('#' + bancoId).prop('readonly', false);
        }
    }

    $(document).ready(function () {
        // Ejecutar trigger si ya existe dentro del DOM principal
        if ($('#select_moneda').length) {
            $('#select_moneda').trigger('change');
        }
    });

    // Delegación de eventos para páginas cargadas por AJAX
    $(document).off('change', '#select_moneda').on('change', '#select_moneda', function () {
        alternarCamposMoneda('select_moneda', 'input_banco_origen', 'div_numero_operacion', 'input_numero_operacion');
    });

    $(document).off('change', '#edit_moneda').on('change', '#edit_moneda', function () {
        alternarCamposMoneda('edit_moneda', 'edit_banco_origen', 'edit_div_numero_operacion', 'edit_numero_operacion');
    });

</script>