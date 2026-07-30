<?php
require_once 'init.php';
require_once '../config/model.php';
require_once '../vendor/autoload.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['id_rol'], [3,4,6])) {
    http_response_code(403);
    die('Acceso denegado. Privilegios insuficientes.');
}

$id_curso = isset($_GET['id_curso']) ? (int)$_GET['id_curso'] : 0;
$id_usuario = isset($_GET['id_usuario']) ? (int)$_GET['id_usuario'] : 0;

if ($id_curso <= 0 || $id_usuario <= 0) {
    die('Parámetros inválidos.');
}

$db = new DB();
$conn = $db->getConn();

// 1. Obtener Datos del Usuario
$stmtU = $conn->prepare("SELECT nombre, apellido, cedula FROM cursos.usuarios WHERE id = :id");
$stmtU->execute(['id' => $id_usuario]);
$usuario = $stmtU->fetch(PDO::FETCH_ASSOC);

if (!$usuario) die("Estudiante no encontrado.");

// 2. Obtener Datos del Curso
$stmtC = $conn->prepare("SELECT nombre_curso, tipo_curso FROM cursos.cursos WHERE id_curso = :id");
$stmtC->execute(['id' => $id_curso]);
$curso = $stmtC->fetch(PDO::FETCH_ASSOC);

if (!$curso) die("Curso no encontrado.");

// 3. Obtener Histórico de Materias
$stmtM = $conn->prepare("
    SELECT 
        m_pensum.nombre_materia,
        m_pensum.total_horas,
        MAX(um_hist.nota_regular) as nota_regular,
        MAX(um_hist.nota_recuperativa) as nota_recuperativa,
        MAX(COALESCE(NULLIF(um_hist.nota_recuperativa, 0), um_hist.nota_regular)) AS nota_historica,
        MAX(um_hist.estado) as estado
    FROM cursos.materias_bimestre m_pensum
    LEFT JOIN cursos.materias_bimestre mb_hist ON UPPER(TRIM(m_pensum.nombre_materia)) = UPPER(TRIM(mb_hist.nombre_materia))
    LEFT JOIN cursos.usuario_materias um_hist ON mb_hist.id_materia_bimestre = um_hist.id_materia_bimestre 
                                              AND um_hist.id_usuario = :id_usuario
    WHERE m_pensum.id_curso = :id_curso
    GROUP BY m_pensum.id_materia_bimestre, m_pensum.nombre_materia, m_pensum.total_horas
    ORDER BY m_pensum.id_materia_bimestre ASC
");
$stmtM->execute(['id_curso' => $id_curso, 'id_usuario' => $id_usuario]);
$materias = $stmtM->fetchAll(PDO::FETCH_ASSOC);

if (count($materias) == 0) {
    die("El curso no posee pensum registrado.");
}

// 4. Configurar FPDF
class ConstanciaPDF extends \FPDF {
    function Header() {
        $img_encabezado = realpath(__DIR__ . '/../public/assets/img/vector membrete 1-01.png');
        if (file_exists($img_encabezado)) {
            $this->Image($img_encabezado, 4, 3, 207.9, 25);
        }
        $this->SetY(35);
    }

    function Footer() {
        $img_pie = realpath(__DIR__ . '/../public/assets/img/piePagina.jpg');
        if (file_exists($img_pie)) {
            $this->Image($img_pie, 10, 255, 195.9, 15);
        }
    }
}

$pdf = new ConstanciaPDF('P', 'mm', 'Letter');
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

// Datos del Estudiante
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, utf8_decode('CONSTANCIA DE CALIFICACIONES'), 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(195, 8, ' DATOS DEL PARTICIPANTE', 1, 1, 'L', true);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(97.5, 8, utf8_decode(' NOMBRES Y APELLIDOS: ' . mb_strtoupper($usuario['nombre'] . ' ' . $usuario['apellido'])), 1, 0);
$pdf->Cell(97.5, 8, utf8_decode(' CÉDULA DE IDENTIDAD: ' . $usuario['cedula']), 1, 1);
$pdf->Cell(195, 8, utf8_decode(' PROGRAMA ACADÉMICO: ' . mb_strtoupper($curso['nombre_curso'])), 1, 1);
$pdf->Ln(5);

// Tabla de Notas
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(95, 8, 'UNIDAD CURRICULAR', 1, 0, 'C', true);
$pdf->Cell(20, 8, 'HORAS', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'REGULAR', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'RECUP.', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'NOTA FINAL', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 9);
$suma = 0;
$aprobadas = 0;
$total_materias = count($materias);

foreach ($materias as $m) {
    $nr = $m['nota_regular'] !== null ? number_format($m['nota_regular'], 2) : '-';
    $nrec = $m['nota_recuperativa'] !== null ? number_format($m['nota_recuperativa'], 2) : '-';
    $nf = $m['nota_historica'] !== null ? number_format($m['nota_historica'], 2) : '-';
    $horas = $m['total_horas'] ?: '-';

    if ($m['nota_historica'] !== null) {
        $suma += (float)$m['nota_historica'];
        if (strpos($m['estado'], 'Aprobado') !== false) {
            $aprobadas++;
        }
    }

    $nombre_corto = mb_substr($m['nombre_materia'], 0, 50, 'UTF-8');
    if (mb_strlen($m['nombre_materia'], 'UTF-8') > 50) $nombre_corto .= '...';

    $pdf->Cell(95, 8, utf8_decode(' ' . $nombre_corto), 1, 0, 'L');
    $pdf->Cell(20, 8, $horas, 1, 0, 'C');
    $pdf->Cell(25, 8, $nr, 1, 0, 'C');
    $pdf->Cell(25, 8, $nrec, 1, 0, 'C');
    
    // Bold final note
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(30, 8, $nf, 1, 1, 'C');
    $pdf->SetFont('Arial', '', 9);
}

// Resumen
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 10);
$promedio = $total_materias > 0 ? number_format($suma / $total_materias, 2) : '-';
$pdf->Cell(135, 8, 'PROMEDIO ACADEMICO OBTENIDO:', 0, 0, 'R');
$pdf->Cell(30, 8, $promedio . ' PTS', 0, 1, 'R');

$pdf->Ln(20);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, '__________________________________', 0, 1, 'C');
$pdf->Cell(0, 5, 'Firma y Sello Coordinacion', 0, 1, 'C');
$pdf->Cell(0, 5, 'Fecha de Emision: ' . date('d/m/Y'), 0, 1, 'C');

$pdf->Output('I', 'Constancia_Notas_' . $usuario['cedula'] . '.pdf');
