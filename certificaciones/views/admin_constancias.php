<?php
// views/admin_constancias.php

require_once __DIR__ . '/../controllers/init.php';
require_once __DIR__ . '/../config/model.php';

// Verificar permisos (Coordinador = 3, Admin = 4)
if (!in_array($_SESSION['id_rol'], [3, 4])) {
    die('<div class="alert alert-danger m-3">Acceso denegado.</div>');
}

$db = new DB();
$conn = $db->getConn();

// 1. Obtener todos los cursos para el selector
$stmtCursos = $conn->prepare("SELECT id_curso, nombre_curso, tipo_curso FROM cursos.cursos ORDER BY id_curso DESC");
$stmtCursos->execute();
$todos_cursos = $stmtCursos->fetchAll(PDO::FETCH_ASSOC);

$id_curso_sel = isset($_GET['id_curso']) ? (int)$_GET['id_curso'] : 0;

$facilitadores = [];
$estudiantes = [];
$curso_info = null;

if ($id_curso_sel > 0) {
    // Info del curso
    foreach ($todos_cursos as $c) {
        if ($c['id_curso'] == $id_curso_sel) {
            $curso_info = $c;
            break;
        }
    }

    // 2. Obtener Facilitadores
    // - El promotor principal
    $sql_promotor = "SELECT u.id, u.nombre, u.apellido, u.cedula, u.correo, u.firma_digital, 'Promotor/Organizador' as rol_en_curso
                     FROM cursos.cursos c
                     JOIN cursos.usuarios u ON c.promotor = u.id
                     WHERE c.id_curso = :id";
    $stmtP = $conn->prepare($sql_promotor);
    $stmtP->execute(['id' => $id_curso_sel]);
    $facilitadores = $stmtP->fetchAll(PDO::FETCH_ASSOC);

    // - Docentes de materias
    $sql_docentes = "SELECT DISTINCT u.id, u.nombre, u.apellido, u.cedula, u.correo, u.firma_digital, m.nombre_materia, m.id_materia_bimestre
                     FROM cursos.materias_bimestre m
                     JOIN cursos.usuarios u ON m.docente_id = u.id
                     WHERE m.id_curso = :id";
    $stmtD = $conn->prepare($sql_docentes);
    $stmtD->execute(['id' => $id_curso_sel]);
    $docentes_mat = $stmtD->fetchAll(PDO::FETCH_ASSOC);
    
    // Unificar (evitar duplicados si el promotor también dicta materia, aunque mostramos la materia)
    foreach ($docentes_mat as $dm) {
        $dm['rol_en_curso'] = "Facilitador: " . $dm['nombre_materia'];
        $facilitadores[] = $dm;
    }

    // --- AGREGAR AUTORIDADES PARA VISUALIZAR FIRMAS ---
    $autoridades = [];
    
    // Encargado del Área (Vicerrectorado)
    $stmt_enc = $conn->prepare("SELECT valor_config FROM cursos.config_sistema WHERE clave_config = 'ID_CARGO_VICERRECTORADO_POR_DEFECTO'");
    $stmt_enc->execute();
    if ($id_enc = $stmt_enc->fetchColumn()) {
        $stmt_c = $conn->prepare("SELECT nombre, apellido, nombre_cargo as rol_en_curso, firma_digital FROM cursos.cargos WHERE id_cargo = :id");
        $stmt_c->execute(['id' => $id_enc]);
        if ($c = $stmt_c->fetch(PDO::FETCH_ASSOC)) {
            $c['cedula'] = 'N/A';
            $c['id'] = 0;
            $autoridades[] = $c;
        }
    }
    
    // Coordinador
    $stmt_coord = $conn->prepare("SELECT valor_config FROM cursos.config_sistema WHERE clave_config = 'ID_CARGO_COORD_FP_POR_DEFECTO'");
    $stmt_coord->execute();
    if ($id_coord = $stmt_coord->fetchColumn()) {
        $stmt_c = $conn->prepare("SELECT nombre, apellido, nombre_cargo as rol_en_curso, firma_digital FROM cursos.cargos WHERE id_cargo = :id");
        $stmt_c->execute(['id' => $id_coord]);
        if ($c = $stmt_c->fetch(PDO::FETCH_ASSOC)) {
            $c['cedula'] = 'N/A';
            $c['id'] = 0;
            $autoridades[] = $c;
        }
    }

    $facilitadores = array_merge($autoridades, $facilitadores);

    // 3. Obtener Estudiantes (Certificados/Inscritos)
    $sql_estudiantes = "SELECT u.id, u.nombre, u.apellido, u.cedula, u.correo, cert.tomo, cert.folio
                        FROM cursos.certificaciones cert
                        JOIN cursos.usuarios u ON cert.id_usuario = u.id
                        WHERE cert.curso_id = :id
                        ORDER BY u.apellido ASC";
    $stmtE = $conn->prepare($sql_estudiantes);
    $stmtE->execute(['id' => $id_curso_sel]);
    $estudiantes = $stmtE->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Administración de Constancias y Firmas</h1>
    </div>

    <!-- Selector de Curso -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form onsubmit="event.preventDefault(); loadPage('../views/admin_constancias.php', { id_curso: this.id_curso.value });" class="row align-items-end">
                <div class="col-md-9">
                    <label class="form-label">Seleccione el Curso / Diplomado:</label>
                    <select name="id_curso" id="id_curso" class="form-control select2-busqueda">
                        <option value="">-- Seleccione --</option>
                        <?php foreach ($todos_cursos as $cur): ?>
                            <option value="<?= $cur['id_curso'] ?>" <?= ($id_curso_sel == $cur['id_curso']) ? 'selected' : '' ?>>
                                [<?= strtoupper($cur['tipo_curso']) ?>] <?= $cur['nombre_curso'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Cargar Datos</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($id_curso_sel > 0): ?>
        
        <!-- Panel de Facilitadores -->
        <div class="card shadow mb-4 border-left-info">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-info"><i class="fas fa-chalkboard-teacher"></i> Facilitadores y Firmas Digitales</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered" width="100%" cellspacing="0">
                        <thead class="bg-light">
                            <tr>
                                <th>Nombre Completo</th>
                                <th>Cédula</th>
                                <th>Rol / Materia</th>
                                <th>Firma Digital</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($facilitadores)): foreach ($facilitadores as $f): ?>
                                <tr>
                                    <td><?= htmlspecialchars($f['nombre'].' '.$f['apellido']) ?></td>
                                    <td><?= htmlspecialchars($f['cedula']) ?></td>
                                    <td><span class="badge badge-secondary"><?= htmlspecialchars($f['rol_en_curso']) ?></span></td>
                                    <td class="text-center">
                                        <?php if (!empty($f['firma_digital'])): ?>
                                            <button class="btn btn-sm btn-outline-success" onclick="verFirma('<?= $f['firma_digital'] ?>', '<?= htmlspecialchars($f['nombre'].' '.$f['apellido']) ?>')">
                                                <i class="fas fa-eye"></i> Ver Firma
                                            </button>
                                        <?php else: ?>
                                            <span class="text-danger small"><i class="fas fa-times-circle"></i> No registrada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (isset($f['id_materia_bimestre'])): ?>
                                            <a href="../controllers/generar_constancia_facilitador.php?id_materia=<?= $f['id_materia_bimestre'] ?>" target="_blank" class="btn btn-info btn-sm">
                                                <i class="fas fa-certificate"></i> Constancia Docente
                                            </a>
                                        <?php else: ?>
                                            <a href="../controllers/generar_constancia.php?id_curso=<?= $id_curso_sel ?>&id_usuario=<?= $f['id'] ?>" target="_blank" class="btn btn-primary btn-sm">
                                                <i class="fas fa-certificate"></i> Constancia Genérica
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Panel de Estudiantes -->
        <div class="card shadow mb-4 border-left-primary">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-graduate"></i> Estudiantes Participantes (Con Certificación)</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered datatable-constancias" width="100%" cellspacing="0">
                        <thead class="bg-light">
                            <tr>
                                <th>Nombre Completo</th>
                                <th>Cédula</th>
                                <th>Correo</th>
                                <th>Tomo / Folio</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($estudiantes)): foreach ($estudiantes as $e): ?>
                                <tr>
                                    <td><?= htmlspecialchars($e['nombre'].' '.$e['apellido']) ?></td>
                                    <td><?= htmlspecialchars($e['cedula']) ?></td>
                                    <td><?= htmlspecialchars($e['correo']) ?></td>
                                    <td>T: <?= $e['tomo'] ?> / F: <?= $e['folio'] ?></td>
                                    <td>
                                        <a href="../controllers/generar_constancia.php?id_curso=<?= $id_curso_sel ?>&id_usuario=<?= $e['id'] ?>" target="_blank" class="btn btn-primary btn-sm">
                                            <i class="fas fa-print"></i> Generar Constancia
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php else: ?>
        <div class="alert alert-info shadow-sm">
            <i class="fas fa-info-circle"></i> Seleccione un curso de la lista superior para gestionar sus constancias y firmas.
        </div>
    <?php endif; ?>
</div>

<!-- Modal Ver Firma -->
<div class="modal fade" id="modalFirma" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Firma Digital: <span id="nombreFirma"></span></h5>
                <button type="button" class="close text-white" onclick="$('#modalFirma').modal('hide');" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center p-4">
                <div id="contenedorFirma" class="border rounded p-3 bg-white shadow-sm">
                    <img id="imgFirma" src="" class="img-fluid" style="max-height: 250px;" alt="Firma Digital">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="$('#modalFirma').modal('hide');">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        if ($.fn.DataTable.isDataTable('.datatable-constancias')) {
            $('.datatable-constancias').DataTable().destroy();
        }
        $('.datatable-constancias').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
            },
            "pageLength": 25
        });
        
        // Inicializar Select2 para búsqueda de cursos
        $('#id_curso').select2({
            theme: 'bootstrap-5',
            placeholder: "Seleccione un curso...",
            allowClear: true,
            width: '100%'
        });
        
        // Como Select2 cambia la forma de disparar el evento onchange, tenemos que enlazar el evento así:
        $('#id_curso').on('select2:select', function (e) {
            var data = e.params.data;
            loadPage('../views/admin_constancias.php', { id_curso: data.id });
        });
        $('#id_curso').on('select2:unselect', function (e) {
            loadPage('../views/admin_constancias.php', { id_curso: 0 });
        });
    });

    function verFirma(filename, nombre) {
        const url = '../public/assets/firmas/' + filename;
        $('#nombreFirma').text(nombre);
        $('#imgFirma').attr('src', url);
        $('#modalFirma').modal('show');
    }
</script>
