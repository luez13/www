<?php
/**
 * FPDF Template: Acta de Cierre (General)
 * Expects $data with: nombre_diplomado, nombre_materia, duracion, total_horas, modalidad, docente_responsable,
 *                     inscritos, aprobados, no_aprobaron, firma_vicerrector, cargo_vicerrector, firma_coord, cargo_coord,
 *                     dia_cierre, mes_cierre, anio_cierre, hora_cierre, img_encabezado, img_pie
 */

$marginX = 25;
$pdf->SetMargins($marginX, 25, $marginX);
$pdf->SetAutoPageBreak(true, 25);
$pdf->AddPage('P', 'Letter');
$pdf->SetFont('Times', '', 12);
$pageWidth = 215.9;
$contentWidth = $pageWidth - ($marginX * 2);

// 1. ENCABEZADO
if (isset($data['img_encabezado']) && file_exists($data['img_encabezado'])) {
    $pdf->Image($data['img_encabezado'], 0, 0, $pageWidth, 25);
}

$pdf->SetY(35);
$pdf->SetFont('Times', 'B', 15);
$pdf->Cell($contentWidth, 8, utf8_decode("ACTA DE CIERRE DEL SEGUNDO BIMESTRE"), 0, 1, 'C');
$pdf->SetFont('Times', 'B', 13);
$pdf->Cell($contentWidth, 8, utf8_decode("DEL " . mb_strtoupper($data['nombre_diplomado'], 'UTF-8')), 0, 1, 'C');
$pdf->Ln(4);

// 3. CUERPO DEL ACTA
$pdf->SetFont('Times', '', 12);
$sangria = "     ";

$fechaActa = $data['dia_cierre'] . " días del mes de " . $data['mes_cierre'] . " del año " . $data['anio_cierre'];

// Texto Intro
$textoIntro = $sangria . "En la ciudad de San Cristóbal, Estado Táchira, a los " . $fechaActa . ", siendo las " . $data['hora_cierre'] . ", se procede a realizar el cierre del segundo bimestre de la cohorte 2 correspondiente al " . $data['nombre_diplomado'] . ", impartiendo en la Universidad Politécnica Territorial Agroindustrial del Estado Táchira. Teniendo como unidad curricular:";
$pdf->MultiCell($contentWidth, 5, utf8_decode($textoIntro), 0, 'J');
$pdf->Ln(2);

// Detalles
$pdf->SetFont('Times', 'B', 12);
$detalles = [
    "Nombre de la materia: " . $data['nombre_materia'],
    "Duración: " . $data['duracion'],
    "Total de horas: " . $data['total_horas'],
    "Modalidad: " . $data['modalidad'],
    "Docente responsable: " . $data['docente_responsable']
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

$pdf->SetFont('Times', '', 12);

$parrafos = [
    "En cumplimiento con los lineamientos establecidos por la institución y con el objetivo de evaluar el desarrollo académico de los participantes, así como el cumplimiento de los objetivos planteados al inicio del diplomado, se procede a dar cierre formal a la materia antes mencionada.",
    "Durante el transcurso de este bimestre, se llevaron a cabo diversas actividades académicas, que incluyeron clases teóricas, talleres prácticos y evaluación, las cuales permitieron a los participantes adquirir competencias y habilidades en el área de estudio. Por otra parte, se registró una inscripción de un total " . $data['inscritos'] . " de participantes, con la culminación de " . $data['aprobados'] . " participantes aprobados y " . $data['no_aprobaron'] . " que no aprobaron.",
    "En cuanto a los resultados los estudiantes fueron evaluados mediante una combinación de trabajos prácticos, foros, talleres y participación continua en la plataforma. La calificación definitiva se ha registrado de acuerdo a los criterios establecidos en el programa del diplomado. Agradecemos a todos los participantes por su dedicación y esfuerzo, así como al cuerpo docente y administrativo de la Universidad Politécnica Territorial Agroindustrial del Estado Táchira por su apoyo y colaboración durante este proceso formativo.",
    "Sin más asuntos que tratar, se levanta la presente acta, que será firmada por los presentes como constancia del cierre de la materia."
];

foreach ($parrafos as $p) {
    $pdf->MultiCell($contentWidth, 5, utf8_decode($sangria . $p), 0, 'J');
    $pdf->Ln(2);
}

// 4. FIRMAS (3 COLUMNAS)
$pdf->SetY(max($pdf->GetY() + 20, 230));
if ($pdf->GetY() > 250) { $pdf->AddPage(); $pdf->SetY(40); }

$yFirmas = $pdf->GetY();
$firmantes = isset($data['firmantes']) && !empty($data['firmantes']) ? $data['firmantes'] : [];

if (count($firmantes) > 0) {
    $numFirmas = count($firmantes);
    $colW = $contentWidth / $numFirmas;
    
    for ($i = 0; $i < $numFirmas; $i++) {
        $x = $marginX + ($i * $colW);
        $lineMarg = 5;
        $f = $firmantes[$i];
        
        if (!empty($f['firma_digital'])) {
            $path_firma = __DIR__ . '/../../public/assets/firmas/' . $f['firma_digital'];
            if (file_exists($path_firma)) {
                $imgInfo = @getimagesize($path_firma);
                if ($imgInfo !== false) {
                    $imgW = $imgInfo[0];
                    $imgH = $imgInfo[1];
                    $maxW = $colW - 10;
                    $maxH = 20;
                    $ratio = min($maxW / $imgW, $maxH / $imgH);
                    $finalW = $imgW * $ratio;
                    $finalH = $imgH * $ratio;
                    
                    $imgX = $x + ($colW - $finalW) / 2;
                    $imgY = $yFirmas - $finalH - 1; // Justo encima de la línea
                    
                    $pdf->Image($path_firma, $imgX, $imgY, $finalW, $finalH);
                }
            }
        }

        // Línea para la firma (centrada en la columna)
        $pdf->Line($x + $lineMarg, $yFirmas, $x + $colW - $lineMarg, $yFirmas);
        
        $nombreF = mb_convert_case($f['titulo'] . ' ' . $f['nombre'], MB_CASE_TITLE, "UTF-8");
        
        $pdf->SetXY($x, $yFirmas + 2);
        $pdf->SetFont('Times', 'B', 9);
        $pdf->MultiCell($colW, 4, utf8_decode($nombreF), 0, 'C');
        
        $pdf->SetX($x);
        $pdf->SetFont('Times', '', 8);
        $pdf->MultiCell($colW, 3.5, utf8_decode($f['cargo']), 0, 'C');
    }
}

// 5. PIE DE PÁGINA
if (isset($data['img_pie']) && file_exists($data['img_pie'])) {
    $pdf->Image($data['img_pie'], 10, 260, $pageWidth - 20, 15);
}
