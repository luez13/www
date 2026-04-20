<?php
/**
 * FPDF Template: Acta de Materia
 * Expects $data with: curso, materia, duracion, periodo_titulo, horas, modalidad, docente, coordinador, cargo_coordinador,
 *                     fecha_actual, mes_actual, anio_actual, hora_actual, total_participantes, aprobados, reprobados,
 *                     columnas_evaluacion, alumnos, img_encabezado, img_pie
 */

// PAGINA 1: Acta
$pdf->AddPage('P', 'Letter');
$pdf->SetFont('Times', '', 12);
$marginX = 20;
$pageWidth = 215.9;
$contentWidth = $pageWidth - ($marginX * 2);

// 1. ENCABEZADO
if (isset($data['img_encabezado']) && file_exists($data['img_encabezado'])) {
    $pdf->Image($data['img_encabezado'], 0, 0, $pageWidth, 25);
}

$pdf->SetY(35);
$pdf->SetFont('Times', 'B', 13);
$tituloActa = "ACTA DE CIERRE DEL " . $data['periodo_titulo'] . " DEL DIPLOMADO:";
$pdf->MultiCell($contentWidth, 6, utf8_decode($tituloActa), 0, 'C');
$pdf->Ln(1);
$pdf->MultiCell($contentWidth, 6, utf8_decode($data['curso']), 0, 'C');
$pdf->Ln(3);

// 3. CUERPO DEL ACTA
$pdf->SetFont('Times', '', 12);
$mesNombre = $data['mes_nombre'];
$sangria = "     ";

$textoIntro = $sangria . "En la ciudad de San Cristóbal, Estado Táchira, a los " . $data['fecha_actual'] . " días del mes de " . $mesNombre . " del año " . $data['anio_actual'] . ", siendo las " . $data['hora_actual'] . ", se procede a realizar el cierre académico correspondiente a la materia del Diplomado impartido en la Universidad Politécnica Territorial Agroindustrial del Estado Táchira.";

$pdf->MultiCell($contentWidth, 5, utf8_decode($textoIntro), 0, 'J');
$pdf->Ln(2);

$pdf->Cell($contentWidth, 5, utf8_decode("Teniendo como unidad curricular:"), 0, 1, 'L');
$pdf->Ln(1);

// Detalles con Bullet points
$pdf->SetFont('Times', 'B', 12);
$detalles = [
    "Nombre de la materia: " . $data['materia'],
    "Duración: " . $data['duracion'],
    "Total de horas: " . $data['horas'] . " horas",
    "Modalidad: " . $data['modalidad'],
    "Facilitador responsable: " . $data['docente']
];

foreach ($detalles as $d) {
    $pdf->SetX($marginX + 10);
    // Usamos ZapfDingbats para el punto negro (chr 108)
    $pdf->SetFont('ZapfDingbats', '', 8);
    $pdf->Cell(5, 5, chr(108), 0, 0, 'L');
    $pdf->SetFont('Times', 'B', 11);
    $pdf->Cell($contentWidth - 15, 5, utf8_decode($d), 0, 1, 'L');
}
$pdf->Ln(2);

// Párrafos Legales
$pdf->SetFont('Times', '', 12);
$parrafos = [
    "En cumplimiento con los lineamientos establecidos por la institución y con el objetivo de evaluar el desarrollo académico de los participantes, así como el cumplimiento de los objetivos planteados, se procede a dar cierre formal a la materia antes mencionada. Durante el transcurso de este periodo, se llevaron a cabo diversas actividades académicas, que incluyeron clases teóricas, talleres prácticos y evaluaciones, las cuales permitieron a los participantes adquirir competencias y habilidades en el área de estudio.",
    "Por otra parte, se registró la participación de un total de " . $data['total_participantes'] . " estudiantes, con la aprobación de " . $data['aprobados'] . " participantes, quienes demostraron un compromiso notable a lo largo del curso. En cuanto a los resultados, los estudiantes fueron evaluados mediante una combinación de trabajos prácticos, foros, talleres y participación continua. La calificación definitiva se ha registrado de acuerdo a los criterios establecidos en el programa del diplomado.",
    "Agradecemos a todos los participantes por su dedicación y esfuerzo, así como al cuerpo docente y administrativo de la UPTAIET por su apoyo y colaboración durante este proceso formativo. Sin más asuntos que tratar, se levanta la presente acta, que será firmada por los presentes como constancia del cierre de la materia."
];

foreach ($parrafos as $p) {
    $pdf->MultiCell($contentWidth, 5, utf8_decode($sangria . $p), 0, 'J');
    $pdf->Ln(2);
}

// 4. FIRMAS
$pdf->SetY(max($pdf->GetY() + 10, 230)); 
if ($pdf->GetY() > 240) { $pdf->AddPage(); $pdf->SetY(40); }

$yFirma = $pdf->GetY();
$pdf->Line($marginX + 5, $yFirma, $marginX + 75, $yFirma);
$pdf->Line($pageWidth - $marginX - 75, $yFirma, $pageWidth - $marginX - 5, $yFirma);

$pdf->SetFont('Times', 'B', 10);
// Izquierda: Coordinador
$pdf->SetXY($marginX + 5, $yFirma + 2);
$pdf->MultiCell(70, 4, utf8_decode($data['coordinador']), 0, 'C');
$pdf->SetX($marginX + 5);
$pdf->MultiCell(70, 4, utf8_decode($data['cargo_coordinador']), 0, 'C');

// Derecha: Facilitador
$pdf->SetXY($pageWidth - $marginX - 75, $yFirma + 2);
$pdf->MultiCell(70, 4, utf8_decode($data['docente']), 0, 'C');
$pdf->SetX($pageWidth - $marginX - 75);
$pdf->SetFont('Times', '', 10);
$pdf->MultiCell(70, 4, utf8_decode("Facilitador(a)"), 0, 'C');

// 5. PIE DE PÁGINA
if (isset($data['img_pie']) && file_exists($data['img_pie'])) {
    $pdf->Image($data['img_pie'], 10, 260, $pageWidth - 20, 15);
}

// ================= PÁGINA 2: ANEXO =================
$pdf->AddPage('L', 'Letter');
$pageLWidth = 279.4;
$marginL = 15;

if (isset($data['img_encabezado']) && file_exists($data['img_encabezado'])) {
    $pdf->Image($data['img_encabezado'], 0, 0, $pageLWidth, 25);
}

$pdf->SetY(35);
$pdf->SetFont('Times', 'B', 14);
$pdf->Cell($pageLWidth, 7, utf8_decode("ANEXO: CALIFICACIONES DETALLADAS"), 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Times', '', 11);
$pdf->SetX($marginL);
$pdf->Cell(0, 6, utf8_decode("Materia: " . $data['materia']), 0, 1, 'L');
$pdf->Ln(2);

// Tabla manual con FPDF
$pdf->SetFont('Times', 'B', 9);
$pdf->SetFillColor(44, 62, 80);
$pdf->SetTextColor(255);

$colNo = 10;
$colCed = 25;
$colNom = 80;
$colDef = 15;
$colAct = ( $pageLWidth - ($marginL * 2) - $colNo - $colCed - $colNom - $colDef ) / count($data['columnas_evaluacion']);

$pdf->SetX($marginL);
$pdf->Cell($colNo, 10, utf8_decode("No."), 1, 0, 'C', true);
$pdf->Cell($colCed, 10, utf8_decode("Cédula"), 1, 0, 'C', true);
$pdf->Cell($colNom, 10, utf8_decode("Participante"), 1, 0, 'C', true);

foreach ($data['columnas_evaluacion'] as $col) {
    // Para las actividades, dividimos la línea si es larga o ajustamos
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->Cell($colAct, 10, '', 1, 0, 'C', true);
    $pdf->SetXY($x, $y);
    $pdf->MultiCell($colAct, 5, utf8_decode($col), 0, 'C');
    $pdf->SetXY($x + $colAct, $y);
}
$pdf->Cell($colDef, 10, utf8_decode("Def."), 1, 1, 'C', true);

$pdf->SetFont('Times', '', 10);
$pdf->SetTextColor(0);

foreach ($data['alumnos'] as $idx => $al) {
    $pdf->SetX($marginL);
    
    // Color de fondo para reprobados
    $fill = ($al['estado'] === 'REPROBADO');
    if ($fill) $pdf->SetFillColor(255, 230, 230);
    else $pdf->SetFillColor(255, 255, 255);

    $pdf->Cell($colNo, 7, $idx + 1, 1, 0, 'C', true);
    $pdf->Cell($colCed, 7, $al['cedula'], 1, 0, 'C', true);
    $pdf->Cell($colNom, 7, utf8_decode($al['nombre']), 1, 0, 'L', true);

    foreach ($al['notas_parciales'] as $nota) {
        $pdf->Cell($colAct, 7, ($nota > 0 ? $nota : 'NP'), 1, 0, 'C', true);
    }
    
    $pdf->SetFont('Times', 'B', 10);
    $pdf->Cell($colDef, 7, $al['definitiva'], 1, 1, 'C', true);
    $pdf->SetFont('Times', '', 10);
}

if (isset($data['img_pie']) && file_exists($data['img_pie'])) {
    $pdf->Image($data['img_pie'], 10, 200, $pageLWidth - 20, 15);
}
