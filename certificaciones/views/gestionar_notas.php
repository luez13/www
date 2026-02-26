<?php
// views/gestionar_notas.php

include '../controllers/init.php';
require_once('../config/model.php');
require_once('../models/Materia.php');
require_once('../models/Nota.php');

if (!isset($_SESSION['user_id'])) { die('Acceso denegado.'); }

$db = new DB(); 
$materiaModel = new Materia($db->getConn());
$notaModel = new Nota($db->getConn());

$id_curso = isset($_REQUEST['id_curso']) ? (int)$_REQUEST['id_curso'] : 0;
if ($id_curso === 0) { echo '<div class="alert alert-danger">Error ID.</div>'; exit; }

// --- FUNCIÓN HELPER PARA PHP 8 (Evita errores con NULL) ---
function h($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Datos generales
$stmt_c = $db->getConn()->prepare("SELECT nombre_curso FROM cursos.cursos WHERE id_curso = :id");
$stmt_c->execute(['id' => $id_curso]);
$nombre_curso = $stmt_c->fetchColumn() ?: 'Desconocido';

$materias = $materiaModel->getMateriasByCurso($id_curso);
$promedios = $notaModel->getPromediosMaterias($id_curso);

// Obtener lista simple de alumnos
$stmt_alum = $db->getConn()->prepare("SELECT u.id, u.nombre, u.apellido, u.cedula FROM cursos.usuarios u JOIN cursos.certificaciones c ON u.id = c.id_usuario WHERE c.curso_id = :id ORDER BY u.apellido");
$stmt_alum->execute(['id' => $id_curso]);
$alumnos_lista = $stmt_alum->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .modal-xl-custom { max-width: 95%; }
    .input-nota { border: 1px solid #ddd; text-align: center; width: 100%; }
    .input-nota:focus { background: #e8f0fe; border-color: #4e73df; }
    .bg-promedio { background-color: #eaecf4; font-weight: bold; color: #4e73df; }
</style>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Control de Estudios</h1>
        <button class="btn btn-secondary btn-sm" onclick="goBack()">
            <i class="fas fa-arrow-left"></i> Volver
        </button>
    </div>
    <p>Diplomado: <strong><?= h($nombre_curso) ?></strong></p>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-dark text-white">
            <h6 class="m-0 font-weight-bold">Resumen Académico Consolidado</h6>
        </div>
        <div class="card-body p-0">
            <div class="alert alert-light border-bottom mb-0 py-2 small text-muted">
                <i class="fas fa-info-circle text-info"></i> 
                Instrucciones: Ingrese calificaciones del <strong>0 al 20</strong>. 
                Para inasistencias, escriba <strong>NP</strong> (No Presentó), el sistema lo calculará como 0.
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="bg-light text-center">
                        <tr>
                            <th>Estudiante</th>
                            <?php foreach ($materias as $mat): ?>
                                <th style="min-width: 150px;">
                                    <?= h($mat['nombre_materia']) ?>
                                    <button class="btn btn-primary btn-sm btn-block mt-1" onclick="abrirEvaluacionMateria(<?= $mat['id_materia_bimestre'] ?>, '<?= h($mat['nombre_materia']) ?>')">
                                        <i class="fas fa-edit"></i> Evaluar
                                    </button>
                                </th>
                            <?php endforeach; ?>
                            <th>Promedio Global</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alumnos_lista as $al): ?>
                        <tr>
                            <td>
                                <?= h($al['apellido'] . ' ' . $al['nombre']) ?><br>
                                <small class="text-muted"><?= h($al['cedula']) ?></small>
                            </td>
                            <?php 
                            $suma_global = 0; $count_mat = 0;
                            foreach ($materias as $mat): 
                                $nota = isset($promedios[$al['id']][$mat['id_materia_bimestre']]) ? $promedios[$al['id']][$mat['id_materia_bimestre']] : 0;
                                $suma_global += $nota;
                                $count_mat++;
                            ?>
                                <td class="text-center align-middle font-weight-bold text-dark">
                                    <?= $nota > 0 ? $nota : '<span class="text-muted">-</span>' ?>
                                </td>
                            <?php endforeach; ?>
                            <td class="text-center align-middle bg-promedio">
                                <?= $count_mat > 0 ? number_format($suma_global/$count_mat, 2) : '0.00' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
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
                            <strong>Atención:</strong> Modificar el plan borrará las notas cargadas si cambia la estructura. Asegúrese de que la suma sea 100%.
                        </div>
                        <form id="formPlan" onsubmit="return false;">
                            <input type="hidden" name="action" value="guardar_plan">
                            <input type="hidden" id="plan_id_materia" name="id_materia">

                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Actividad Evaluativa</th>
                                        <th style="width: 150px;">Ponderación (%)</th>
                                        <th style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="bodyPlan"></tbody>
                                <tfoot>
                                    <tr>
                                        <th class="text-end">Total:</th>
                                        <th id="totalPorcentaje" class="text-center text-danger">0%</th>
                                        <th><button class="btn btn-success btn-sm" onclick="agregarFilaPlan()"><i class="fas fa-plus"></i></button></th>
                                    </tr>
                                </tfoot>
                            </table>
                            <button class="btn btn-danger" onclick="guardarPlan()">Guardar Plan de Evaluación</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var ID_CURSO = <?= $id_curso ?>;

// 1. ABRIR MODAL Y CARGAR DATOS
function abrirEvaluacionMateria(idMateria, nombreMateria) {
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

// 2. RENDERIZAR PLAN
function renderizarPlan(plan) {
    var html = '';
    if(plan.length === 0) {
        for(var i=1; i<=4; i++) html += crearFilaPlanHTML('', '', ''); 
    } else {
        // Pasamos el ID real de la base de datos
        plan.forEach(p => html += crearFilaPlanHTML(p.nombre_actividad, p.ponderacion_porcentaje, p.id_actividad_config));
    }
    $('#bodyPlan').html(html);
    calcularTotalPlan();
}

function crearFilaPlanHTML(nom, porc, id = '') { // <--- Agrega el parámetro id
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

// 3. RENDERIZAR TABLA DE NOTAS
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
            
            // Lógica NP
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
            
            // Cálculo
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
                loadPage('../views/gestionar_notas.php', { id_curso: ID_CURSO });
            } else { alert(res.message); }
        }
    });
}

// CALCULOS Y VALIDACIONES JS
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

// Cálculo inteligente (Soporta NP) - CORREGIDO
function calcularPromedioFila(input) {
    // Al usar $(), 'fila' se convierte en un objeto jQuery
    let fila = $(input).closest('tr'); 
    let inputs = fila.find('.nota-input');
    var headers = $('#contenedorTablaNotas th small'); // Porcentajes
    
    let definitiva = 0;

    inputs.each(function(index) {
        // 'this' aquí es el elemento nativo, así que .value funciona
        let rawVal = this.value.trim().toUpperCase();
        let val = 0;

        // Lógica de Negocio: NP vale 0
        if (rawVal === 'NP') {
            val = 0;
        } else if (rawVal !== '') {
            val = parseFloat(rawVal);
        }

        // Obtener porcentaje de la columna
        var porcTexto = $(headers[index]).text().replace('%','');
        var porc = parseFloat(porcTexto) / 100;

        if (!isNaN(val)) { 
            definitiva += val * porc; 
        }
    });

    // --- CORRECCIÓN AQUÍ ---
    // Usamos .find() en lugar de .querySelector() porque 'fila' es jQuery
    fila.find('.celda-definitiva').text(definitiva.toFixed(2));
    
    // Color visual para indicar cambio no guardado
    input.style.backgroundColor = "#fff3cd"; 
}
</script>