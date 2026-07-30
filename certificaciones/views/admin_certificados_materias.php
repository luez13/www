<?php
// views/admin_certificados_materias.php
require_once __DIR__ . '/../controllers/init.php';
require_once __DIR__ . '/../config/model.php';

if (!in_array($_SESSION['id_rol'], [3, 4])) {
    die('<div class="alert alert-danger m-3">Acceso denegado.</div>');
}

$db = new DB();
$conn = $db->getConn();

// 1. Obtener cursos
$stmtCursos = $conn->prepare("SELECT id_curso, nombre_curso FROM cursos.cursos ORDER BY id_curso DESC");
$stmtCursos->execute();
$cursos = $stmtCursos->fetchAll(PDO::FETCH_ASSOC);

$id_curso_sel = isset($_GET['id_curso']) ? (int)$_GET['id_curso'] : 0;
$id_materia_sel = isset($_GET['id_materia']) ? (int)$_GET['id_materia'] : 0;

$materias = [];
$estudiantes = [];

if ($id_curso_sel > 0) {
    $stmtMaterias = $conn->prepare("SELECT id_materia_bimestre, nombre_materia FROM cursos.materias_bimestre WHERE id_curso = :id");
    $stmtMaterias->execute(['id' => $id_curso_sel]);
    $materias = $stmtMaterias->fetchAll(PDO::FETCH_ASSOC);

    if ($id_materia_sel > 0) {
        $sql = "SELECT 
                    u.id, u.nombre, u.apellido, u.cedula, u.correo,
                    cm.tomo, cm.folio
                FROM cursos.usuario_materias um
                JOIN cursos.usuarios u ON um.id_usuario = u.id
                LEFT JOIN cursos.certificaciones_materias cm ON um.id_usuario = cm.id_usuario AND um.id_materia_bimestre = cm.id_materia_bimestre
                WHERE um.id_materia_bimestre = :materia AND um.estado LIKE 'Aprobado%'
                ORDER BY u.apellido ASC";
        $stmtEst = $conn->prepare($sql);
        $stmtEst->execute(['materia' => $id_materia_sel]);
        $estudiantes = $stmtEst->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<div class="container-fluid mt-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-layer-group"></i> Asignación Tomo y Folio (Materias)</h1>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Seleccione Programa y Materia</h6>
        </div>
        <div class="card-body">
            <form id="formFiltroMat">
                <div class="row align-items-end">
                    <div class="col-md-5">
                        <label>Programa / Diplomado:</label>
                        <select name="id_curso" id="id_curso_mat" class="form-control" style="width: 100%;">
                            <option value="0">Seleccione un Programa</option>
                            <?php foreach ($cursos as $c): ?>
                                <option value="<?= $c['id_curso'] ?>" <?= $id_curso_sel == $c['id_curso'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['nombre_curso']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label>Materia / Unidad Curricular:</label>
                        <select name="id_materia" id="id_materia_mat" class="form-control" style="width: 100%;" <?= $id_curso_sel == 0 ? 'disabled' : '' ?>>
                            <option value="0">Seleccione una Materia</option>
                            <?php foreach ($materias as $m): ?>
                                <option value="<?= $m['id_materia_bimestre'] ?>" <?= $id_materia_sel == $m['id_materia_bimestre'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['nombre_materia']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if ($id_materia_sel > 0): ?>
        <!-- Asignación Masiva -->
        <div class="card shadow mb-4 border-left-success">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-bolt"></i> Asignación Masiva</h6>
            </div>
            <div class="card-body bg-light">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label>Tomo Masivo</label>
                        <input type="number" id="tomo_masivo" class="form-control" placeholder="Ej. 1">
                    </div>
                    <div class="col-md-3">
                        <label>Folio Masivo</label>
                        <input type="number" id="folio_masivo" class="form-control" placeholder="Ej. 42">
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-success" onclick="aplicarMasivo()">
                            <i class="fas fa-check-double"></i> Aplicar a Todos
                        </button>
                    </div>
                    <div class="col-md-3 text-right">
                        <small class="text-muted">Aplica a los <?= count($estudiantes) ?> estudiantes de la lista.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla Estudiantes -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="tablaEstudiantesMat">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Estudiante</th>
                                <th>Cédula</th>
                                <th>Tomo</th>
                                <th>Folio</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($estudiantes)): ?>
                                <tr><td colspan="5" class="text-center">No hay alumnos aprobados en esta materia.</td></tr>
                            <?php else: ?>
                                <?php foreach ($estudiantes as $e): ?>
                                    <tr id="row_<?= $e['id'] ?>">
                                        <td><?= htmlspecialchars($e['nombre'] . ' ' . $e['apellido']) ?></td>
                                        <td><?= htmlspecialchars($e['cedula']) ?></td>
                                        <td>
                                            <input type="number" class="form-control input-tomo" data-id="<?= $e['id'] ?>" value="<?= htmlspecialchars(isset($e['tomo']) ? $e['tomo'] : '') ?>">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control input-folio" data-id="<?= $e['id'] ?>" value="<?= htmlspecialchars(isset($e['folio']) ? $e['folio'] : '') ?>">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm btn-guardar" data-id="<?= $e['id'] ?>">
                                                <i class="fas fa-save"></i> Guardar
                                            </button>
                                            <a href="../controllers/generar_certificado_materia.php?id_materia=<?= $id_materia_sel ?>&cedula=<?= $e['cedula'] ?>" target="_blank" class="btn btn-info btn-sm">
                                                <i class="fas fa-print"></i> Ver PDF
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
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    $('#id_curso_mat').select2({ theme: 'bootstrap-5' });
    $('#id_materia_mat').select2({ theme: 'bootstrap-5' });

    $('#id_curso_mat').on('select2:select', function (e) {
        loadPage('../views/admin_certificados_materias.php', { id_curso: $(this).val() });
    });
    
    $('#id_materia_mat').on('select2:select', function (e) {
        loadPage('../views/admin_certificados_materias.php', { 
            id_curso: $('#id_curso_mat').val(), 
            id_materia: $(this).val() 
        });
    });

    $('.btn-guardar').click(function() {
        let id_usuario = $(this).data('id');
        guardarIndividual(id_usuario);
    });
});

function guardarIndividual(id_usuario) {
    let id_materia = <?= $id_materia_sel ?>;
    let tomo = $('.input-tomo[data-id="'+id_usuario+'"]').val();
    let folio = $('.input-folio[data-id="'+id_usuario+'"]').val();

    if (!tomo || !folio) {
        Swal.fire('Atención', 'Debe llenar el Tomo y Folio antes de guardar.', 'warning');
        return;
    }

    $.post('../controllers/update_tomo_folio_materia.php', {
        id_usuario: id_usuario,
        id_materia: id_materia,
        tomo: tomo,
        folio: folio
    }, function(res) {
        if (res.success) {
            Swal.fire('Éxito', 'Guardado con éxito', 'success');
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    }, 'json').fail(function() {
        Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
    });
}

function aplicarMasivo() {
    let t_m = $('#tomo_masivo').val();
    let f_m = $('#folio_masivo').val();

    if (!t_m || !f_m) {
        Swal.fire('Atención', 'Ingrese el Tomo y Folio masivo.', 'warning');
        return;
    }

    let estudiantes = [];
    $('.input-tomo').each(function() {
        let id = $(this).data('id');
        $(this).val(t_m);
        $('.input-folio[data-id="'+id+'"]').val(f_m);
        estudiantes.push(id);
    });

    if (estudiantes.length === 0) return;

    Swal.fire({
        title: 'Guardando de forma masiva...',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    $.post('../controllers/update_tomo_folio_materia.php', {
        bulk: true,
        id_materia: <?= $id_materia_sel ?>,
        tomo: t_m,
        folio: f_m,
        ids: estudiantes
    }, function(res) {
        if(res.success) {
            Swal.fire('Éxito', res.message, 'success');
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    }, 'json').fail(function(){
        Swal.fire('Error', 'Error de red', 'error');
    });
}
</script>
