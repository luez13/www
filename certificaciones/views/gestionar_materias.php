<?php
// views/gestionar_materias.php

include '../controllers/init.php';
require_once('../config/model.php');
require_once('../models/Materia.php');

if (!isset($_SESSION['user_id'])) {
    die('Acceso denegado.');
}

$db = new DB();
$materiaModel = new Materia($db->getConn());

$id_curso = isset($_REQUEST['id_curso']) ? (int) $_REQUEST['id_curso'] : 0;

if ($id_curso === 0) {
    echo '<div class="alert alert-danger">Error: ID de curso perdido.</div>';
    exit;
}

$stmt_c = $db->getConn()->prepare("SELECT nombre_curso FROM cursos.cursos WHERE id_curso = :id");
$stmt_c->execute(['id' => $id_curso]);
$curso_info = $stmt_c->fetch(PDO::FETCH_ASSOC);
$nombre_curso = $curso_info ? $curso_info['nombre_curso'] : 'Curso Desconocido';

$materias = $materiaModel->getMateriasByCurso($id_curso);

// Agrupar materias por lapso para visualización
$materiasPorLapso = [];
foreach ($materias as $mat) {
    $lapso = $mat['lapso_academico'];
    $materiasPorLapso[$lapso][] = $mat;
}
ksort($materiasPorLapso); // Ordenar claves (1, 2, 3...)
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Estructura Académica</h1>
        <button class="btn btn-secondary btn-sm" onclick="goBack()">
            <i class="fas fa-arrow-left"></i> Volver
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Materias de: <?= htmlspecialchars($nombre_curso) ?></h6>
            <button class="btn btn-success btn-sm" onclick="abrirModalMateria()"><i class="fas fa-plus"></i> Nueva
                Materia</button>
        </div>
        <div class="card-body">
            <?php if (empty($materias)): ?>
                <div class="text-center py-5">
                    <p class="text-gray-500">No hay materias registradas.</p>
                </div>
            <?php else: ?>

                <?php foreach ($materiasPorLapso as $lapso => $grupoMaterias): ?>
                    <div class="alert alert-secondary mt-3 mb-2 py-1">
                        <strong>
                            <?php
                            // Lógica de etiqueta visual
                            echo "Periodo Académico " . $lapso;
                            ?>
                        </strong>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0" width="100%">
                            <thead class="bg-light">
                                <tr>
                                    <th>Materia</th>
                                    <th>Duración (Texto)</th>
                                    <th>Modalidad</th>
                                    <th>Docente</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($grupoMaterias as $mat): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($mat['nombre_materia']) ?></td>
                                        <td><?= htmlspecialchars($mat['duracion_bimestres']) ?></td>
                                        <td><?= htmlspecialchars($mat['modalidad']) ?></td>
                                        <td><?= htmlspecialchars($mat['nombre_docente'] . ' ' . $mat['apellido_docente']) ?></td>
                                        <td class="text-center" style="width: 180px;">
                                            <button class="btn btn-warning btn-sm"
                                                onclick="editarMateria(<?= $mat['id_materia_bimestre'] ?>)" title="Editar"><i
                                                    class="fas fa-edit"></i></button>

                                            <a href="../views/generar_acta_materia.php?id_materia=<?= $mat['id_materia_bimestre'] ?>"
                                                target="_blank" class="btn btn-info btn-sm" title="Acta de Cierre">
                                                <i class="fas fa-file-contract"></i>
                                            </a>
                                            <a href="../controllers/generar_constancia_facilitador.php?id_materia=<?= $mat['id_materia_bimestre'] ?>"
                                                target="_blank" class="btn btn-success btn-sm" title="Constancia de Docencia">
                                                <i class="fas fa-certificate"></i>
                                            </a>
                                            <button class="btn btn-danger btn-sm"
                                                onclick="eliminarMateria(<?= $mat['id_materia_bimestre'] ?>)" title="Eliminar"><i
                                                    class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>

            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="modalMateria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalMateriaLabel">Gestión de Materia</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formMateria" onsubmit="return false;">
                <div class="modal-body">
                    <input type="hidden" name="action" value="guardar">
                    <input type="hidden" name="id_curso" value="<?= $id_curso ?>">
                    <input type="hidden" name="id_materia" id="id_materia" value="0">

                    <div class="mb-3">
                        <label class="fw-bold">Ubicación Temporal (Lapso)</label>
                        <select class="form-select" name="lapso_academico" id="lapso_academico" required>
                            <option value="1">1er Periodo (Bimestre/Trimestre I)</option>
                            <option value="2">2do Periodo (Bimestre/Trimestre II)</option>
                            <option value="3">3er Periodo (Bimestre/Trimestre III)</option>
                            <option value="4">4to Periodo (Opcional)</option>
                        </select>
                        <small class="text-muted">Agrupará la materia en este bloque.</small>
                    </div>

                    <div class="mb-3">
                        <label>Nombre Materia</label>
                        <input type="text" class="form-control" name="nombre_materia" id="nombre_materia" required>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label>Duración (Texto)</label>
                            <input type="text" class="form-control" name="duracion_bimestres" id="duracion_bimestres"
                                placeholder="Ej: 1 Bimestre" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label>Horas</label>
                            <input type="number" class="form-control" name="total_horas" id="total_horas" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Modalidad</label>
                        <select class="form-select" name="modalidad" id="modalidad">
                            <option value="Virtual">Virtual</option>
                            <option value="Presencial">Presencial</option>
                            <option value="Mixta">Mixta</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Docente</label>
                        <div class="input-group">
                            <input type="hidden" name="docente_id" id="docente_id">
                            <input type="text" class="form-control" id="docente_nombre" readonly placeholder="Buscar..."
                                required>
                            <button class="btn btn-outline-primary" type="button" onclick="abrirBuscadorDocente()"><i
                                    class="fas fa-search"></i></button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarMateriaAJAX()">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalBuscarDocente" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Buscar Docente</h5><button type="button" class="btn-close"
                    data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="inputBusquedaDocente">
                    <button class="btn btn-info" onclick="ejecutarBusquedaDocente()">Buscar</button>
                </div>
                <table class="table table-sm">
                    <tbody id="tablaResultadosDocente"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    var ID_CURSO_ACTUAL = <?= $id_curso ?>;

    function guardarMateriaAJAX() {
        if ($('#nombre_materia').val() == '') { alert('Falta el nombre'); return; }
        if ($('#docente_id').val() == '') { alert('Falta el docente'); return; }

        var datos = $('#formMateria').serialize();

        $.ajax({
            url: '../controllers/gestion_materia.php',
            type: 'POST',
            data: datos,
            dataType: 'text',
            success: function (raw) {
                try {
                    var res = JSON.parse(raw);
                    if (res.success) {
                        alert(res.message);
                        $('#modalMateria').modal('hide');
                        $('.modal-backdrop').remove();
                        loadPage('../views/gestionar_materias.php', { id_curso: ID_CURSO_ACTUAL });
                    } else {
                        alert("Error: " + res.message);
                    }
                } catch (e) { alert("Error crítico:\n" + raw); }
            },
            error: function () { alert("Error de conexión"); }
        });
    }

    function abrirModalMateria() {
        $('#formMateria')[0].reset();
        $('#id_materia').val(0);
        $('#docente_id').val('');
        // Valor por defecto lapso 1
        $('#lapso_academico').val(1);
        $('#modalMateria').modal('show');
    }

    function editarMateria(id) {
        $.ajax({
            url: '../controllers/gestion_materia.php',
            type: 'POST',
            data: { action: 'obtener', id_materia: id },
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    var d = res.data;
                    $('#id_materia').val(d.id_materia_bimestre);
                    $('#nombre_materia').val(d.nombre_materia);
                    $('#duracion_bimestres').val(d.duracion_bimestres);
                    $('#total_horas').val(d.total_horas);
                    $('#modalidad').val(d.modalidad);
                    $('#docente_id').val(d.docente_id);
                    $('#docente_nombre').val(d.nombre_docente);
                    // Cargar el lapso guardado
                    $('#lapso_academico').val(d.lapso_academico || 1);

                    $('#modalMateria').modal('show');
                }
            }
        });
    }

    function eliminarMateria(id) {
        if (confirm('¿Eliminar?')) {
            $.ajax({
                url: '../controllers/gestion_materia.php',
                type: 'POST',
                data: { action: 'eliminar', id_materia: id },
                success: function () {
                    loadPage('../views/gestionar_materias.php', { id_curso: ID_CURSO_ACTUAL });
                }
            });
        }
    }

    function abrirBuscadorDocente() { $('#modalBuscarDocente').modal('show'); }

    function ejecutarBusquedaDocente() {
        var q = $('#inputBusquedaDocente').val();
        $.ajax({
            url: '../controllers/buscar_usuarios_ajax.php',
            data: { q: q },
            dataType: 'json',
            success: function (data) {
                var html = '';
                if (data.length) {
                    data.forEach(function (u) {
                        html += '<tr><td>' + u.nombre + ' ' + u.apellido + '</td><td><button class="btn btn-sm btn-success" onclick="selDocente(' + u.id + ', \'' + u.nombre + ' ' + u.apellido + '\')">✓</button></td></tr>';
                    });
                } else { html = '<tr><td>No encontrado</td></tr>'; }
                $('#tablaResultadosDocente').html(html);
            }
        });
    }

    function selDocente(id, nombre) {
        $('#docente_id').val(id);
        $('#docente_nombre').val(nombre);
        $('#modalBuscarDocente').modal('hide');
    }
</script>