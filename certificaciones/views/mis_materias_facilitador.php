<?php
// views/mis_materias_facilitador.php

include '../controllers/init.php';
require_once('../config/model.php');

if (!isset($_SESSION['user_id'])) { die('Acceso denegado.'); }

$db = new DB(); 
$user_id = $_SESSION['user_id'];

// Obtener materias asignadas al facilitador actual
$sql = "SELECT m.id_materia_bimestre, m.id_curso, m.nombre_materia, c.nombre_curso, c.tipo_curso
        FROM cursos.materias_bimestre m
        JOIN cursos.cursos c ON m.id_curso = c.id_curso
        WHERE m.docente_id = :user_id
        ORDER BY c.nombre_curso, m.id_materia_bimestre";
$stmt = $db->getConn()->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$materias = $stmt->fetchAll(PDO::FETCH_ASSOC);

function h($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<style>
    .modal-xl-custom { max-width: 95%; }
    .input-nota { border: 1px solid #ddd; text-align: center; width: 100%; }
    .input-nota:focus { background: #e8f0fe; border-color: #4e73df; }
    .bg-promedio { background-color: #eaecf4; font-weight: bold; color: #4e73df; }
</style>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Carga de Calificaciones - Facilitador</h1>
        <button class="btn btn-secondary btn-sm" onclick="goBack()">
            <i class="fas fa-arrow-left"></i> Volver a inicio
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-primary text-white">
            <h6 class="m-0 font-weight-bold">Mis Materias Asignadas</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="bg-light text-center">
                        <tr>
                            <th>Diplomado / Curso</th>
                            <th>Materia</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($materias)): ?>
                            <tr><td colspan="3" class="text-center">No tienes materias asignadas actualmente.</td></tr>
                        <?php else: foreach ($materias as $mat): ?>
                        <tr>
                            <td class="align-middle"><?= h($mat['nombre_curso']) ?> <br><span class="badge badge-info"><?= h(ucfirst($mat['tipo_curso'])) ?></span></td>
                            <td class="align-middle fw-bold"><?= h($mat['nombre_materia']) ?></td>
                            <td class="text-center align-middle">
                                <button class="btn btn-success btn-sm mb-1" onclick="abrirEvaluacionMateria(<?= $mat['id_materia_bimestre'] ?>, '<?= h($mat['nombre_materia']) ?>', <?= $mat['id_curso'] ?>)">
                                    <i class="fas fa-edit"></i> Evaluar e Ingresar Notas
                                </button>
                                <a href="../controllers/generar_constancia_facilitador.php?id_materia=<?= $mat['id_materia_bimestre'] ?>" target="_blank" class="btn btn-info btn-sm mb-1">
                                    <i class="fas fa-certificate"></i> Descargar Constancia
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEvaluacion" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl-custom">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-book-open"></i> Evaluación: <span id="lblMateriaNombre"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                
                <ul class="nav nav-tabs mb-3" id="tabsEvaluacion" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="tab-notas-btn" data-bs-toggle="tab" data-bs-target="#tab-notas" type="button">
                            <i class="fas fa-user-graduate"></i> Cargar Calificaciones
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link text-danger" id="tab-plan-btn" data-bs-toggle="tab" data-bs-target="#tab-plan" type="button">
                            <i class="fas fa-cog"></i> Configurar Plan de Evaluación
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="contentEvaluacion">
                    <div class="tab-pane fade show active" id="tab-notas">
                        <form id="formNotasDetalle" onsubmit="return false;">
                            <input type="hidden" name="action" value="guardar_notas_detalle">
                            <input type="hidden" id="notas_id_materia" name="id_materia">
                            
                            <div id="contenedorTablaNotas" class="table-responsive">
                                <div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>
                            </div>
                            
                            <div class="mt-3 text-end">
                                <button class="btn btn-primary btn-lg" onclick="guardarNotasDetalle()">
                                    <i class="fas fa-save"></i> Guardar Todo
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="tab-plan">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <strong>Atención:</strong> Modificar el plan borrará las notas cargadas...
                        </div>
                        <form id="formPlan" onsubmit="return false;">
                            <input type="hidden" name="action" value="guardar_plan">
                            <input type="hidden" id="plan_id_materia" name="id_materia">

                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Actividad Evaluativa</th>
                                        <th>Ponderación (%)</th>
                                        <th style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="bodyPlan"></tbody>
                                <tfoot>
                                    <tr>
                                        <td class="text-end fw-bold">TOTAL:</td>
                                        <td class="fw-bold fs-5" id="totalPorcentaje">0%</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary" onclick="agregarFilaPlan()">
                                    <i class="fas fa-plus"></i> Añadir Actividad
                                </button>
                                <button class="btn btn-success" onclick="guardarPlan()">
                                    <i class="fas fa-save"></i> Guardar Plan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
var CURSO_ACTUAL_ID = 0;

function abrirEvaluacionMateria(idMateria, nombreMateria, idCurso) {
    CURSO_ACTUAL_ID = idCurso;
    $('#lblMateriaNombre').text(nombreMateria);
    $('#plan_id_materia').val(idMateria);
    $('#notas_id_materia').val(idMateria);
    
    var firstTab = new bootstrap.Tab(document.querySelector('#tab-notas-btn'));
    firstTab.show();

    cargarDatosMateria(idMateria);
    $('#modalEvaluacion').modal('show');
}

function cargarDatosMateria(idMateria) {
    $('#contenedorTablaNotas').html('<div class="text-center p-4">Cargando...</div>');
    $.ajax({
        url: '../controllers/gestion_notas.php',
        type: 'POST',
        data: { action: 'obtener_detalle_materia', id_materia: idMateria },
        dataType: 'json',
        success: function(res) {
            if(res.success) {
                renderizarPlan(res.plan);
                renderizarTablaNotas(res.plan, res.alumnos);
            } else { alert(res.message); }
        }
    });
}

function renderizarPlan(plan) {
    var html = '';
    if(plan.length === 0) {
        for(var i=1; i<=4; i++) html += crearFilaPlanHTML('', '', ''); 
    } else {
        plan.forEach(p => html += crearFilaPlanHTML(p.nombre_actividad, p.ponderacion_porcentaje, p.id_actividad_config));
    }
    $('#bodyPlan').html(html);
    calcularTotalPlan();
}

function crearFilaPlanHTML(nom, porc, id = '') {
    return `<tr>
        <input type="hidden" name="id_actividad[]" value="${id}"> <td><input type="text" class="form-control" name="nombre_actividad[]" value="${nom}" placeholder="Nombre de actividad"></td>
        <td><input type="number" class="form-control input-porc" name="porcentaje_actividad[]" value="${porc}" onkeyup="calcularTotalPlan()"></td>
        <td><button class="btn btn-outline-danger btn-sm" onclick="$(this).closest('tr').remove(); calcularTotalPlan();">X</button></td>
    </tr>`;
}

function agregarFilaPlan() { $('#bodyPlan').append(crearFilaPlanHTML('', '')); }

function calcularTotalPlan() {
    var total = 0;
    $('.input-porc').each(function() {
        var val = parseFloat($(this).val()) || 0;
        total += val;
    });
    var el = $('#totalPorcentaje');
    el.text(total + '%');
    if(total === 100) { el.removeClass('text-danger').addClass('text-success'); }
    else { el.removeClass('text-success').addClass('text-danger'); }
    return total;
}

function guardarPlan() {
    if(calcularTotalPlan() !== 100) { alert("El plan debe sumar exactamente 100%"); return; }
    if(!confirm("¿Guardar Plan? Si borró actividades, se perderán las notas asociadas.")) return;

    $.ajax({
        url: '../controllers/gestion_notas.php',
        type: 'POST',
        data: $('#formPlan').serialize(),
        dataType: 'json',
        success: function(res) {
            if(res.success) {
                alert(res.message);
                cargarDatosMateria($('#plan_id_materia').val()); 
            } else { alert(res.message); }
        }
    });
}

function renderizarTablaNotas(plan, alumnos) {
    if(plan.length === 0) {
        $('#contenedorTablaNotas').html('<div class="alert alert-warning">Primero configure el Plan de Evaluación.</div>');
        return;
    }

    var html = '<table class="table table-bordered table-sm table-striped"><thead><tr><th>Estudiante</th>';
    plan.forEach(p => {
        html += `<th class="text-center bg-light">${p.nombre_actividad}<br><small>${p.ponderacion_porcentaje}%</small></th>`;
    });
    html += '<th class="text-center bg-dark text-white" style="width:80px">Def.</th></tr></thead><tbody>';

    alumnos.forEach(al => {
        html += `<tr>
            <td class="align-middle"><strong>${al.apellido} ${al.nombre}</strong><br><small>${al.cedula}</small></td>`;
        var prom = 0;
        
        plan.forEach(p => {
            var nota = (al.notas_actividad && al.notas_actividad[p.id_actividad_config] !== undefined) 
                        ? al.notas_actividad[p.id_actividad_config] : '';
            var valorVisual = (nota === 0 || nota === '0') ? 'NP' : nota;
            html += `<td class="p-0">
                <input type="text" 
                    class="form-control text-center fw-bold nota-input" 
                    name="notas[${al.id}][${p.id_actividad_config}]"
                    value="${valorVisual}" 
                    onblur="validarNotaInput(this)" 
                    onchange="calcularPromedioFila(this)"
                    style="border: none; background: transparent; width: 100%; height: 100%;">
            </td>`;
            var num = (nota === 'NP' || nota === 0) ? 0 : parseFloat(nota);
            if(!isNaN(num)) { prom += num * (p.ponderacion_porcentaje / 100); }
        });

        html += `<td class="text-center align-middle fw-bold h5 text-primary celda-definitiva">${prom.toFixed(2)}</td></tr>`;
    });

    html += '</tbody></table>';
    $('#contenedorTablaNotas').html(html);
}

function guardarNotasDetalle() {
    $.ajax({
        url: '../controllers/gestion_notas.php',
        type: 'POST',
        data: $('#formNotasDetalle').serialize(),
        dataType: 'json',
        success: function(res) {
            if(res.success) {
                alert(res.message);
                $('#modalEvaluacion').modal('hide');
                loadPage('../views/mis_materias_facilitador.php');
            } else { alert(res.message); }
        }
    });
}

function validarNotaInput(input) {
    let val = input.value.trim().toUpperCase();
    if (val === 'NP') { input.value = 'NP'; calcularPromedioFila(input); return; }
    if (val === '') { calcularPromedioFila(input); return; }

    let num = parseFloat(val);
    if (isNaN(num)) {
        alert("Solo números (0-20) o 'NP'");
        input.value = ''; 
    } else {
        if (num < 0) input.value = 0;
        if (num > 20) input.value = 20;
    }
    calcularPromedioFila(input);
}

function calcularPromedioFila(input) {
    let fila = $(input).closest('tr'); 
    let inputs = fila.find('.nota-input');
    var headers = $('#contenedorTablaNotas th small'); 
    let definitiva = 0;

    inputs.each(function(index) {
        let rawVal = this.value.trim().toUpperCase();
        let val = 0;
        if (rawVal === 'NP') val = 0;
        else if (rawVal !== '') val = parseFloat(rawVal);
        
        // --- AQUÍ ESTABA EL ERROR CORREGIDO ---
        var porc = parseFloat($(headers[index]).text().replace('%', '')) / 100;
        // ----------------------------------------
        
        if (!isNaN(val)) definitiva += val * porc; 
    });

    fila.find('.celda-definitiva').text(definitiva.toFixed(2));
    input.style.backgroundColor = "#fff3cd"; 
}
</script>
