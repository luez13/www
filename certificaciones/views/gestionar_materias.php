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
                                    <th>Facilitador</th>
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
                                        <td class="text-center">
                                            <!-- Desktop View Actions -->
                                            <div class="d-none d-md-block">
                                                <button class="btn btn-warning btn-sm"
                                                    onclick="editarMateria(<?= $mat['id_materia_bimestre'] ?>)" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-primary btn-sm text-white" 
                                                    onclick="abrirRecuperativo(<?= $mat['id_materia_bimestre'] ?>, '<?= addslashes(htmlspecialchars($mat['nombre_materia'])) ?>')" 
                                                    style="background-color: #fd7e14; border-color: #fd7e14;" title="Evaluación Recuperativa">
                                                    <i class="fas fa-life-ring"></i>
                                                </button>
                                                <button class="btn btn-info btn-sm" title="Acta de Cierre (Regular)"
                                                    onclick="abrirModalActaMateria(<?= $mat['id_materia_bimestre'] ?>, 'Regular')">
                                                    <i class="fas fa-file-contract"></i>
                                                </button>
                                                <button class="btn btn-secondary btn-sm" style="background-color: #e83e8c; border-color: #e83e8c;" title="Acta de Recuperativo"
                                                    onclick="abrirModalActaMateria(<?= $mat['id_materia_bimestre'] ?>, 'Recuperativo')">
                                                    <i class="fas fa-file-invoice"></i>
                                                </button>
                                                <a href="../controllers/generar_constancia_facilitador.php?id_materia=<?= $mat['id_materia_bimestre'] ?>"
                                                    target="_blank" class="btn btn-success btn-sm" title="Constancia de Docencia">
                                                    <i class="fas fa-certificate"></i>
                                                </a>
                                                <button class="btn btn-danger btn-sm"
                                                    onclick="eliminarMateria(<?= $mat['id_materia_bimestre'] ?>)" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>

                                            <!-- Mobile View Actions (Dropdown) -->
                                            <div class="dropdown d-md-none">
                                                <button class="btn btn-primary btn-sm dropdown-toggle shadow-sm" type="button" 
                                                    id="dropdownMenu<?= $mat['id_materia_bimestre'] ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-cog"></i> Opciones
                                                </button>
                                                <ul class="dropdown-menu shadow border-0" aria-labelledby="dropdownMenu<?= $mat['id_materia_bimestre'] ?>" style="z-index: 1050;">
                                                    <li><a class="dropdown-item py-2" href="#" onclick="editarMateria(<?= $mat['id_materia_bimestre'] ?>)"><i class="fas fa-edit me-2 text-warning"></i> Editar</a></li>
                                                    <li><a class="dropdown-item py-2" href="#" onclick="abrirRecuperativo(<?= $mat['id_materia_bimestre'] ?>, '<?= addslashes(htmlspecialchars($mat['nombre_materia'])) ?>')"><i class="fas fa-life-ring me-2" style="color:#fd7e14;"></i> Ev. Recuperativa</a></li>
                                                    <li><a class="dropdown-item py-2" href="#" onclick="abrirModalActaMateria(<?= $mat['id_materia_bimestre'] ?>, 'Regular')"><i class="fas fa-file-contract me-2 text-info"></i> Acta Regular</a></li>
                                                    <li><a class="dropdown-item py-2" href="#" onclick="abrirModalActaMateria(<?= $mat['id_materia_bimestre'] ?>, 'Recuperativo')"><i class="fas fa-file-invoice me-2" style="color:#e83e8c;"></i> Acta Recuperativa</a></li>
                                                    <li><a class="dropdown-item py-2" target="_blank" href="../controllers/generar_constancia_facilitador.php?id_materia=<?= $mat['id_materia_bimestre'] ?>"><i class="fas fa-certificate me-2 text-success"></i> Constancia</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item py-2 text-danger" href="#" onclick="eliminarMateria(<?= $mat['id_materia_bimestre'] ?>)"><i class="fas fa-trash me-2"></i> Eliminar</a></li>
                                                </ul>
                                            </div>
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
                        <input type="text" class="form-control" name="nombre_materia" id="nombre_materia" list="materias-list" required>
                        <?php
                        $stmtSugerencias = $db->getConn()->prepare("SELECT DISTINCT UPPER(TRIM(nombre_materia)) AS nombre_materia FROM cursos.materias_bimestre ORDER BY nombre_materia");
                        $stmtSugerencias->execute();
                        $sugerencias = $stmtSugerencias->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <datalist id="materias-list">
                            <?php foreach ($sugerencias as $sug): ?>
                                <?php if (!empty($sug['nombre_materia'])): ?>
                                    <option value="<?= htmlspecialchars($sug['nombre_materia']) ?>"></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </datalist>
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
                        <label>Facilitador</label>
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

<div class="modal fade" id="modalBuscarDocente" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-search me-2"></i>Buscar Facilitador</h5><button type="button" class="btn-close btn-close-white"
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

<!-- Modal Recuperativo -->
<div class="modal fade" id="modalRecuperativo" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: #fd7e14;">
                <h5 class="modal-title"><i class="fas fa-life-ring me-2"></i>Recuperativo: <span id="tituloMateriaRecuperativo"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formRecuperativo" onsubmit="return false;">
                    <input type="hidden" name="action" value="guardar_notas_recuperativo">
                    <input type="hidden" name="id_materia" id="id_materia_recup" value="0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="bg-light">
                                <tr>
                                    <th>Cédula</th>
                                    <th>Estudiante</th>
                                    <th>Nota Regular</th>
                                    <th>Nota Recuperativo</th>
                                </tr>
                            </thead>
                            <tbody id="tablaAlumnosRecuperativo">
                                <tr><td colspan="4" class="text-center">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn text-white" style="background-color: #fd7e14;" onclick="guardarRecuperativo()">Guardar Notas</button>
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

    function abrirRecuperativo(id_materia, nombre_materia) {
        $('#id_materia_recup').val(id_materia);
        $('#tituloMateriaRecuperativo').text(nombre_materia);
        $('#tablaAlumnosRecuperativo').html('<tr><td colspan="4" class="text-center"><i class="fas fa-spinner fa-spin"></i> Buscando estudiantes reprobados...</td></tr>');
        $('#modalRecuperativo').modal('show');
        
        $.ajax({
            url: '../controllers/gestion_notas.php',
            type: 'POST',
            data: { action: 'obtener_alumnos_reprobados', id_materia: id_materia },
            dataType: 'json',
            success: function(res) {
                if(res.success) {
                    var html = '';
                    if(res.alumnos && res.alumnos.length > 0) {
                        res.alumnos.forEach(function(a) {
                            html += '<tr>';
                            html += '<td>' + a.cedula + '</td>';
                            html += '<td>' + a.apellido + ' ' + a.nombre + '</td>';
                            html += '<td class="text-danger fw-bold">' + parseFloat(a.nota_regular).toFixed(2) + '</td>';
                            html += '<td><input type="number" class="form-control form-control-sm" name="notas_recup[' + a.id_usuario + ']" value="' + (a.nota_recuperativa ? parseFloat(a.nota_recuperativa).toFixed(2) : '') + '" min="1" max="100" step="0.01"></td>';
                            html += '</tr>';
                        });
                    } else {
                        html = '<tr><td colspan="4" class="text-center text-success fw-bold"><i class="fas fa-check-circle"></i> Todos los estudiantes de esta materia están aprobados o no hay datos.</td></tr>';
                    }
                    $('#tablaAlumnosRecuperativo').html(html);
                } else {
                    $('#tablaAlumnosRecuperativo').html('<tr><td colspan="4" class="text-danger text-center">Error: ' + res.message + '</td></tr>');
                }
            },
            error: function() {
                $('#tablaAlumnosRecuperativo').html('<tr><td colspan="4" class="text-danger text-center">Error de conexión</td></tr>');
            }
        });
    }

    function guardarRecuperativo() {
        var datos = $('#formRecuperativo').serialize();
        $.ajax({
            url: '../controllers/gestion_notas.php',
            type: 'POST',
            data: datos,
            dataType: 'json',
            success: function(res) {
                if(res.success) {
                    Swal.fire('Guardado', 'Notas de recuperativo guardadas.', 'success');
                    $('#modalRecuperativo').modal('hide');
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            },
            error: function() { Swal.fire('Error', 'Error de conexión', 'error'); }
        });
    }

    var actaMateriaSeleccionada = 0;
    var actaTipoSeleccionado = '';

    window.abrirModalActaMateria = function(id, tipo) {
        actaMateriaSeleccionada = id;
        actaTipoSeleccionado = tipo;
        $('#modalActaMateria').modal('show');
    };

    window.generarActaFinal = function() {
        var fecha = $('#inputFechaActa').val();
        var url = '../controllers/generar_acta_materia_fpdf.php?id_materia=' + actaMateriaSeleccionada;
        
        if (actaTipoSeleccionado === 'Recuperativo') {
            url += '&tipo=recuperativo';
        }
        if (fecha) {
            url += '&fecha_historica=' + fecha;
        }

        $('#modalActaMateria').modal('hide');
        window.open(url, '_blank');
    };
</script>

<div class="modal fade" id="modalActaMateria" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title"><i class="fas fa-file-signature me-2"></i> Emisión de Acta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <p class="text-muted small">Al emitir el acta, puede fijar una fecha histórica. Esta fecha se registrará de forma inmutable en el sistema y aparecerá en el PDF.</p>
                <div class="mb-3">
                    <label class="form-label fw-bold">Forzar Nueva Fecha Histórica (Opcional):</label>
                    <?php $disabled_acta = ($_SESSION['id_rol'] != 4) ? 'disabled' : ''; ?>
                    <input type="date" id="inputFechaActa" class="form-control" <?= $disabled_acta ?>>
                    <small class="text-muted mt-1 d-block">Déjelo en blanco para mantener la fecha original de emisión (o usar la de hoy si es la primera vez).</small>
                    <?php if ($_SESSION['id_rol'] != 4): ?>
                        <small class="text-danger mt-1 d-block"><i class="fas fa-lock"></i> Solo el Administrador (Rol 4) puede alterar la fecha cronológica.</small>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="window.generarActaFinal()">
                    <i class="fas fa-print me-1"></i> Generar Acta
                </button>
            </div>
        </div>
    </div>
</div>