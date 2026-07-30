<?php
require_once 'init.php';
require_once '../config/model.php';

// Verificación estricta (Forensis): Solo roles autorizados pueden ver expedientes (Ej. 3,4,6)
if (!isset($_SESSION['id_rol']) || !in_array($_SESSION['id_rol'], [3, 4, 6])) {
    http_response_code(403);
    die('<div class="alert alert-danger">Acceso denegado. Privilegios insuficientes.</div>');
}

$id_usuario = isset($_GET['id_usuario']) ? (int)$_GET['id_usuario'] : 0;
if ($id_usuario <= 0) {
    die('<div class="alert alert-warning">Usuario inválido.</div>');
}

$db = new DB();
$conn = $db->getConn();

// 1. Obtener Cursos Finalizados (completado = true)
$stmt_finalizados = $conn->prepare("
    SELECT c.id_curso, c.nombre_curso, cert.nota, c.fecha_acta_cierre as fecha_emision, 
           (SELECT p.estado FROM cursos.comprobantes_pago p WHERE p.id_curso = c.id_curso AND p.id_usuario = cert.id_usuario ORDER BY p.id_comprobante DESC LIMIT 1) as estado_pago
    FROM cursos.certificaciones cert
    JOIN cursos.cursos c ON cert.curso_id = c.id_curso
    WHERE cert.id_usuario = :id_usuario AND cert.completado = true
");
$stmt_finalizados->execute(['id_usuario' => $id_usuario]);
$finalizados = $stmt_finalizados->fetchAll(PDO::FETCH_ASSOC);

// 2. Obtener Cursos en Progreso (completado = false)
$stmt_progreso = $conn->prepare("
    SELECT c.nombre_curso
    FROM cursos.certificaciones cert
    JOIN cursos.cursos c ON cert.curso_id = c.id_curso
    WHERE cert.id_usuario = :id_usuario AND cert.completado = false
");
$stmt_progreso->execute(['id_usuario' => $id_usuario]);
$progreso = $stmt_progreso->fetchAll(PDO::FETCH_ASSOC);

// 3. Obtener Materias Modulares Aprobadas
$stmt_modulares = $conn->prepare("
    SELECT mb.nombre_materia, ac.fecha_cierre as fecha_emision, um.nota_regular, um.nota_recuperativa, c.nombre_curso
    FROM cursos.usuario_materias um
    JOIN cursos.materias_bimestre mb ON um.id_materia_bimestre = mb.id_materia_bimestre
    JOIN cursos.cursos c ON mb.id_curso = c.id_curso
    LEFT JOIN cursos.actas_cierre ac ON ac.id_materia_bimestre = mb.id_materia_bimestre AND ac.tipo_acta = 'Regular'
    WHERE um.id_usuario = :id_usuario AND um.estado LIKE 'Aprobado%'
");
$stmt_modulares->execute(['id_usuario' => $id_usuario]);
$modulares = $stmt_modulares->fetchAll(PDO::FETCH_ASSOC);

?>
<div class="row">
    <!-- Finalizados -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="fas fa-graduation-cap me-2"></i> Diplomados/Cursos Finalizados</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php if (count($finalizados) > 0): ?>
                        <?php foreach ($finalizados as $f): 
                                $id_curso_f = $f['id_curso']; // Necesitamos traer id_curso en la consulta
                        ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong class="text-dark"><?= htmlspecialchars($f['nombre_curso']) ?></strong>
                                    <div class="small text-muted"><i class="far fa-calendar-alt"></i> Emisión: <?= date('d/m/Y', strtotime($f['fecha_emision'])) ?></div>
                                    <?php if ($f['estado_pago'] === 'aprobado'): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success"><i class="fas fa-check-circle"></i> Pago Solvente</span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <span class="badge bg-primary rounded-pill fs-6 mb-2 d-block">Nota Final: <?= number_format($f['nota'], 2) ?> pts</span>
                                    <button class="btn btn-outline-info btn-sm w-100" onclick="verNotasCurso(<?= $f['id_curso'] ?>, <?= $id_usuario ?>)">
                                        <i class="fas fa-eye"></i> Ver Notas
                                    </button>
                                </div>
                            </li>
                            <!-- Contenedor para AJAX -->
                            <li class="list-group-item bg-light d-none" id="notas_curso_<?= $f['id_curso'] ?>">
                                <div class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item text-center text-muted p-4">No tiene cursos finalizados.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- En Progreso -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="fas fa-spinner fa-spin me-2"></i> En Progreso (Inscrito)</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php if (count($progreso) > 0): ?>
                        <?php foreach ($progreso as $p): ?>
                            <li class="list-group-item">
                                <i class="fas fa-book-reader text-warning me-2"></i> 
                                <strong class="text-dark"><?= htmlspecialchars($p['nombre_curso']) ?></strong>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item text-center text-muted p-4">No cursa nada actualmente.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Materias Modulares -->
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-puzzle-piece me-2"></i> Unidades Curriculares Aprobadas (Modulares)</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php if (count($modulares) > 0): ?>
                        <?php foreach ($modulares as $m): 
                            $nota_final = !empty($m['nota_recuperativa']) ? $m['nota_recuperativa'] : $m['nota_regular'];
                        ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong class="text-dark"><?= htmlspecialchars($m['nombre_materia']) ?></strong>
                                    <div class="small text-muted">Módulo de: <?= htmlspecialchars($m['nombre_curso']) ?></div>
                                </div>
                                <span class="badge bg-secondary rounded-pill fs-6">Nota Materia: <?= number_format($nota_final, 2) ?> pts</span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item text-center text-muted p-4">No tiene certificaciones modulares.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function verNotasCurso(id_curso, id_usuario) {
    const container = document.getElementById('notas_curso_' + id_curso);
    if (container.classList.contains('d-none')) {
        container.classList.remove('d-none');
        fetch(`../controllers/get_notas_curso_ajax.php?id_curso=${id_curso}&id_usuario=${id_usuario}`)
            .then(res => res.text())
            .then(html => {
                container.innerHTML = html;
            })
            .catch(err => {
                container.innerHTML = '<div class="alert alert-danger">Error al cargar las notas.</div>';
            });
    } else {
        container.classList.add('d-none');
    }
}
</script>
