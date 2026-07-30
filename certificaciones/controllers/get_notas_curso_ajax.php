<?php
require_once 'init.php';
require_once '../config/model.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    die('Acceso denegado.');
}

$id_curso = isset($_GET['id_curso']) ? (int)$_GET['id_curso'] : 0;
// Si es admin (rol 3,4,6) puede ver notas de otros usuarios, si es rol 2 solo las suyas
$id_usuario = isset($_GET['id_usuario']) && in_array($_SESSION['id_rol'], [3,4,6]) ? (int)$_GET['id_usuario'] : $_SESSION['user_id'];

if ($id_curso <= 0 || $id_usuario <= 0) {
    die('Parámetros inválidos.');
}

$db = new DB();
$conn = $db->getConn();

// Extraer el nombre del curso
$stmtC = $conn->prepare("SELECT nombre_curso, tipo_curso FROM cursos.cursos WHERE id_curso = :id");
$stmtC->execute(['id' => $id_curso]);
$curso_info = $stmtC->fetch(PDO::FETCH_ASSOC);

if (!$curso_info) {
    die('Curso no encontrado.');
}

// Extraer materias del curso y las notas del usuario (histórico con arrastre/homologación)
$stmt = $conn->prepare("
    SELECT 
        m_pensum.nombre_materia,
        MAX(um_hist.nota_regular) as nota_regular,
        MAX(um_hist.nota_recuperativa) as nota_recuperativa,
        MAX(COALESCE(NULLIF(um_hist.nota_recuperativa, 0), um_hist.nota_regular)) AS nota_historica,
        MAX(um_hist.estado) as estado
    FROM cursos.materias_bimestre m_pensum
    LEFT JOIN cursos.materias_bimestre mb_hist ON UPPER(TRIM(m_pensum.nombre_materia)) = UPPER(TRIM(mb_hist.nombre_materia))
    LEFT JOIN cursos.usuario_materias um_hist ON mb_hist.id_materia_bimestre = um_hist.id_materia_bimestre 
                                              AND um_hist.id_usuario = :id_usuario
    WHERE m_pensum.id_curso = :id_curso
    GROUP BY m_pensum.id_materia_bimestre, m_pensum.nombre_materia
    ORDER BY m_pensum.id_materia_bimestre ASC
");
$stmt->execute(['id_curso' => $id_curso, 'id_usuario' => $id_usuario]);
$materias = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($materias) === 0) {
    echo '<div class="alert alert-info">Este curso no tiene unidades curriculares registradas en el pensum.</div>';
    exit;
}
?>

<div class="table-responsive">
    <table class="table table-bordered table-hover table-sm text-center align-middle" style="font-size: 0.9rem;">
        <thead class="table-dark">
            <tr>
                <th class="text-start">Unidad Curricular</th>
                <th width="15%">Nota Regular</th>
                <th width="15%">Nota Recup.</th>
                <th width="15%">Nota Final</th>
                <th width="20%">Estatus</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $aprobadas = 0;
            $suma = 0;
            foreach ($materias as $m): 
                $nr = $m['nota_regular'] !== null ? number_format($m['nota_regular'], 2) : '-';
                $nrec = $m['nota_recuperativa'] !== null ? number_format($m['nota_recuperativa'], 2) : '-';
                $nf = $m['nota_historica'] !== null ? number_format($m['nota_historica'], 2) : '-';
                
                $estatus = '-';
                $badge_class = 'bg-secondary';
                if ($m['estado'] !== null) {
                    if (strpos($m['estado'], 'Aprobado') !== false) {
                        $estatus = 'Aprobado';
                        $badge_class = 'bg-success';
                        $aprobadas++;
                        $suma += (float)$m['nota_historica'];
                    } else {
                        $estatus = 'Reprobado';
                        $badge_class = 'bg-danger';
                    }
                }
            ?>
            <tr>
                <td class="text-start fw-bold"><?= htmlspecialchars($m['nombre_materia']) ?></td>
                <td><?= $nr ?></td>
                <td><?= $nrec ?></td>
                <td class="fw-bold bg-light"><?= $nf ?></td>
                <td><span class="badge <?= $badge_class ?> w-100"><?= $estatus ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <?php if ($aprobadas > 0): ?>
        <tfoot class="table-light fw-bold">
            <tr>
                <td colspan="3" class="text-end">Promedio Académico:</td>
                <td class="text-primary fs-6"><?= number_format($suma / count($materias), 2) ?></td>
                <td></td>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>
</div>

<?php if (in_array($_SESSION['id_rol'], [3,4,6])): ?>
<div class="text-end mt-3 border-top pt-2">
    <a href="../controllers/generar_constancia_notas_fpdf.php?id_curso=<?= $id_curso ?>&id_usuario=<?= $id_usuario ?>" target="_blank" class="btn btn-primary btn-sm">
        <i class="fas fa-print"></i> Descargar Constancia PDF
    </a>
</div>
<?php endif; ?>
