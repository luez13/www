<?php
/**
 * FPDF Template: Acta de Cierre (Dynamic & General)
 * Expects $data with: nombre_diplomado, tipo_curso, duracion, total_horas, modalidad, docente_responsable,
 *                     inscritos, aprobados, no_aprobaron, firmantes,
 *                     dia_cierre, mes_cierre, anio_cierre, hora_cierre, img_encabezado, img_pie, alumnos
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

// 2. TÍTULOS
$tipo_curso_str = isset($data['tipo_curso']) ? strtolower($data['tipo_curso']) : 'curso';
$es_diplomado = in_array($tipo_curso_str, ['diplomado', 'diplomado_rectoria']);

$pdf->SetY(27);
$pdf->SetFont('Times', 'B', 13);

if ($es_diplomado) {
    $pdf->MultiCell($contentWidth, 6, utf8_decode("ACTA DE CIERRE FINAL DE DIPLOMADO"), 0, 'C');
} else {
    $pdf->MultiCell($contentWidth, 6, utf8_decode("ACTA DE CIERRE FINAL DE " . mb_strtoupper(str_replace('_', ' ', $tipo_curso_str), 'UTF-8')), 0, 'C');
}
$pdf->Ln(1);
$pdf->SetFont('Times', 'B', 11);
$pdf->MultiCell($contentWidth, 5, utf8_decode("DEL PROGRAMA: " . mb_strtoupper($data['nombre_diplomado'], 'UTF-8')), 0, 'C');
$pdf->Ln(3);

// 3. CUERPO DEL ACTA
$pdf->SetFont('Times', '', 12);
$sangria = "     ";

$fechaActa = $data['dia_cierre'] . " días del mes de " . $data['mes_cierre'] . " del año " . $data['anio_cierre'];

if ($es_diplomado) {
    $textoIntro = $sangria . "En la ciudad de San Cristóbal, Estado Táchira, a los " . $fechaActa . ", siendo las " . $data['hora_cierre'] . ", se procede a realizar el cierre del diplomado correspondiente al " . $data['nombre_diplomado'] . ", impartido en la Universidad Politécnica Territorial Agroindustrial del Estado Táchira. Teniendo las siguientes especificaciones curriculares:";
} else {
    $textoIntro = $sangria . "En la ciudad de San Cristóbal, Estado Táchira, a los " . $fechaActa . ", siendo las " . $data['hora_cierre'] . ", se procede a realizar el cierre del " . str_replace('_', ' ', $tipo_curso_str) . " correspondiente al " . $data['nombre_diplomado'] . ", impartido en la Universidad Politécnica Territorial Agroindustrial del Estado Táchira. Teniendo las siguientes especificaciones curriculares:";
}

$pdf->MultiCell($contentWidth, 5, utf8_decode($textoIntro), 0, 'J');
$pdf->Ln(2);

// Detalles del Programa
$pdf->SetFont('Times', 'B', 12);
$detalles = [
    "Nombre del Programa: " . $data['nombre_diplomado'],
    "Duración: " . $data['duracion'],
    "Total de horas: " . $data['total_horas'],
    "Modalidad: " . $data['modalidad'],
    "Facilitador(a) responsable: " . $data['docente_responsable']
];

foreach ($detalles as $d) {
    $pdf->SetX($marginX + 10);
    // Usamos ZapfDingbats para el punto negro (chr 108)
    $pdf->SetFont('ZapfDingbats', '', 8);
    $pdf->Cell(5, 5, chr(108), 0, 0, 'L');
    $pdf->SetFont('Times', 'B', 11);
    
    // Cambiar margen izquierdo temporalmente para que las líneas siguientes se alineen con la primera
    $pdf->SetLeftMargin($marginX + 15);
    $pdf->MultiCell($contentWidth - 15, 5, utf8_decode($d), 0, 'L');
    // Restaurar el margen izquierdo original
    $pdf->SetLeftMargin($marginX);
}
$pdf->Ln(2);

$pdf->SetFont('Times', '', 12);
$tiene_notas = isset($data['tiene_notas']) ? $data['tiene_notas'] : false;
$participantes_count = isset($data['participantes']) ? $data['participantes'] : 0;
$aprobados_count = isset($data['aprobados']) ? $data['aprobados'] : 0;
$reprobados_count = isset($data['no_aprobaron']) ? $data['no_aprobaron'] : 0;

if ($es_diplomado) {
    $p1 = "En cumplimiento con los lineamientos establecidos por la institución y con el objetivo de evaluar el desarrollo académico de los participantes, así como el cumplimiento de los objetivos planteados al inicio del diplomado, se procede a dar cierre formal al mismo.";
    if ($tiene_notas) {
        $p2 = "Durante el transcurso de este diplomado, se llevaron a cabo diversas actividades académicas, que incluyeron clases teóricas, talleres prácticos y evaluaciones continuas, las cuales permitieron a los participantes adquirir competencias y habilidades en el área de estudio. Por otra parte, se registró una inscripción de un total de " . $data['inscritos'] . " participantes, con la culminación de " . $aprobados_count . " participantes aprobados y " . $reprobados_count . " que no aprobaron.";
        $p3 = "En cuanto a los resultados, los estudiantes fueron evaluados mediante una combinación de trabajos prácticos, foros, talleres y participación continua. La calificación definitiva se ha registrado de acuerdo a los criterios establecidos en el programa del diplomado. Agradecemos a todos los participantes por su dedicación y esfuerzo, así como al cuerpo docente y administrativo de la Universidad Politécnica Territorial Agroindustrial del Estado Táchira por su apoyo y colaboración durante este proceso formativo.";
    } else {
        $p2 = "Durante el transcurso de este diplomado, se llevaron a cabo diversas actividades académicas, que incluyeron clases teóricas, talleres prácticos y foros de discusión, las cuales permitieron a los participantes adquirir competencias y habilidades en el área de estudio. Por otra parte, se registró una inscripción de un total de " . $data['inscritos'] . " participantes, con la culminación de " . $participantes_count . " participantes que completaron satisfactoriamente la actividad.";
        $p3 = "En cuanto a los resultados, los participantes fueron evaluados y acreditados bajo la modalidad de asistencia y participación continua, según las pautas establecidas. Agradecemos a todos los participantes por su dedicación y esfuerzo, así como al cuerpo docente y administrativo de la Universidad Politécnica Territorial Agroindustrial del Estado Táchira por su apoyo y colaboración durante este proceso formativo.";
    }
} else {
    $p1 = "En cumplimiento con los lineamientos establecidos por la institución y con el objetivo de evaluar el desarrollo académico de los participantes, así como el cumplimiento de los objetivos planteados al inicio del " . str_replace('_', ' ', $tipo_curso_str) . ", se procede a dar cierre formal al mismo.";
    if ($tiene_notas) {
        $p2 = "Durante el transcurso de este " . str_replace('_', ' ', $tipo_curso_str) . ", se llevaron a cabo diversas actividades académicas, que incluyeron clases teóricas, talleres prácticos y evaluaciones, las cuales permitieron a los participantes adquirir competencias y habilidades en el área de estudio. Por otra parte, se registró una inscripción de un total de " . $data['inscritos'] . " participantes, con la culminación de " . $aprobados_count . " participantes aprobados y " . $reprobados_count . " que no aprobaron.";
        $p3 = "En cuanto a los resultados, los estudiantes fueron evaluados mediante evaluaciones y participación continua en el transcurso del programa. La calificación definitiva se ha registrado de acuerdo a los criterios establecidos. Agradecemos a todos los participantes por su dedicación y esfuerzo, así como al cuerpo docente y administrativo de la Universidad Politécnica Territorial Agroindustrial del Estado Táchira por su apoyo y colaboración durante este proceso formativo.";
    } else {
        $p2 = "Durante el transcurso de este " . str_replace('_', ' ', $tipo_curso_str) . ", se llevaron a cabo diversas actividades académicas y de interacción, las cuales permitieron a los participantes adquirir competencias y habilidades en el área de estudio. Por otra parte, se registró una inscripción de un total de " . $data['inscritos'] . " participantes, con la culminación de " . $participantes_count . " participantes que completaron satisfactoriamente la actividad.";
        $p3 = "En cuanto a los resultados, los participantes fueron evaluados y acreditados bajo la modalidad de asistencia y participación activa en el transcurso del programa. Agradecemos a todos los participantes por su dedicación y esfuerzo, así como al cuerpo docente y administrativo de la Universidad Politécnica Territorial Agroindustrial del Estado Táchira por su apoyo y colaboración durante este proceso formativo.";
    }
}
$p4 = "Sin más asuntos que tratar, se levanta la presente acta, que será firmada por los presentes como constancia del cierre del mismo.";

$parrafos = [$p1, $p2, $p3, $p4];

foreach ($parrafos as $p) {
    $pdf->MultiCell($contentWidth, 5, utf8_decode($sangria . $p), 0, 'J');
    $pdf->Ln(2);
}

// 4. FIRMAS (3 COLUMNAS MÁXIMO)
$pdf->SetY(max($pdf->GetY() + 15, 230));
if ($pdf->GetY() > 250) { 
    $pdf->AddPage(); 
    $pdf->SetY(40); 
}

$yFirmas = $pdf->GetY();
$firmantes = isset($data['firmantes']) && !empty($data['firmantes']) ? $data['firmantes'] : [];

if (count($firmantes) > 0) {
    $numFirmas = count($firmantes);
    $colW = $contentWidth / $numFirmas;
    
    for ($i = 0; $i < $numFirmas; $i++) {
        $x = $marginX + ($i * $colW);
        $lineMarg = 5;
        $f = $firmantes[$i];
        
        // Línea para la firma (centrada en la columna)
        $pdf->Line($x + $lineMarg, $yFirmas, $x + $colW - $lineMarg, $yFirmas);
        
        $titulo = !empty($f['titulo']) ? trim($f['titulo']) . ' ' : '';
        $nombreF = mb_convert_case($titulo . $f['nombre'], MB_CASE_TITLE, "UTF-8");
        
        $pdf->SetXY($x, $yFirmas + 2);
        $pdf->SetFont('Times', 'B', 9);
        $pdf->MultiCell($colW, 4, utf8_decode($nombreF), 0, 'C');
        
        $pdf->SetX($x);
        $pdf->SetFont('Times', '', 8);
        $pdf->MultiCell($colW, 3.5, utf8_decode($f['cargo']), 0, 'C');
    }
}

// Pie de página de la página 1
if (isset($data['img_pie']) && file_exists($data['img_pie'])) {
    $pdf->Image($data['img_pie'], 10, 260, $pageWidth - 20, 15);
}

// ================= PÁGINA 2: ANEXO DE ESTUDIANTES APROBADOS =================
$pdf->AddPage('L', 'Letter');
$pageWidthL = 279.4; // Landscape width
$contentWidthL = $pageWidthL - ($marginX * 2);

// Encabezado de página 2
if (isset($data['img_encabezado']) && file_exists($data['img_encabezado'])) {
    $pdf->Image($data['img_encabezado'], 0, 0, $pageWidthL, 25);
}

$pdf->SetY(35);
$pdf->SetFont('Times', 'B', 14);
$pdf->Cell($contentWidthL, 7, utf8_decode("ANEXO: LISTADO DE PARTICIPANTES"), 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Times', 'B', 9);
$pdf->SetFillColor(44, 62, 80);
$pdf->SetTextColor(255);

$colNo = 10;
$colCed = 30;

if (!$es_diplomado) {
    // Si NO es diplomado (es curso general), mostramos Tomo y Folio
    $colNom = 110.4;
    $colNota = 22;
    $colEstatus = 25;
    $colTomo = 16;
    $colFolio = 16;
} else {
    // Diplomado: sin Tomo ni Folio
    $colNom = 138.4;
    $colNota = 25;
    $colEstatus = 26;
}

$pdf->SetX($marginX);
$pdf->Cell($colNo, 7, utf8_decode("No."), 1, 0, 'C', true);
$pdf->Cell($colCed, 7, utf8_decode("Cédula"), 1, 0, 'C', true);
$pdf->Cell($colNom, 7, utf8_decode("Participante (Nombre y Apellido)"), 1, 0, 'C', true);
$pdf->Cell($colNota, 7, utf8_decode("Calificación"), 1, 0, 'C', true);
if (!$es_diplomado) {
    $pdf->Cell($colTomo, 7, utf8_decode("Tomo"), 1, 0, 'C', true);
    $pdf->Cell($colFolio, 7, utf8_decode("Folio"), 1, 0, 'C', true);
}
$pdf->Cell($colEstatus, 7, utf8_decode("Estatus"), 1, 1, 'C', true);

$pdf->SetFont('Times', '', 10);
$pdf->SetTextColor(0);

$alumnos = isset($data['alumnos']) ? $data['alumnos'] : [];
foreach ($alumnos as $idx => $al) {
    // Si cabe en la página, continuar, si no, crear nueva página y repetir cabecera
    if ($pdf->GetY() > 190) { // Landscape page is shorter (~215.9 height)
        if (isset($data['img_pie']) && file_exists($data['img_pie'])) {
            $pdf->Image($data['img_pie'], 10, 195, $pageWidthL - 20, 15);
        }
        
        $pdf->AddPage('L', 'Letter');
        if (isset($data['img_encabezado']) && file_exists($data['img_encabezado'])) {
            $pdf->Image($data['img_encabezado'], 0, 0, $pageWidthL, 25);
        }
        
        $pdf->SetY(35);
        $pdf->SetFont('Times', 'B', 9);
        $pdf->SetFillColor(44, 62, 80);
        $pdf->SetTextColor(255);
        $pdf->SetX($marginX);
        $pdf->Cell($colNo, 7, utf8_decode("No."), 1, 0, 'C', true);
        $pdf->Cell($colCed, 7, utf8_decode("Cédula"), 1, 0, 'C', true);
        $pdf->Cell($colNom, 7, utf8_decode("Participante (Nombre y Apellido)"), 1, 0, 'C', true);
        $pdf->Cell($colNota, 7, utf8_decode("Calificación"), 1, 0, 'C', true);
        if (!$es_diplomado) {
            $pdf->Cell($colTomo, 7, utf8_decode("Tomo"), 1, 0, 'C', true);
            $pdf->Cell($colFolio, 7, utf8_decode("Folio"), 1, 0, 'C', true);
        }
        $pdf->Cell($colEstatus, 7, utf8_decode("Estatus"), 1, 1, 'C', true);
        $pdf->SetFont('Times', '', 10);
        $pdf->SetTextColor(0);
    }

    $nota_min = isset($data['nota_minima_aprobatoria']) ? (int)$data['nota_minima_aprobatoria'] : 12;
    $nota_str = "-";
    $estatus_str = "-";
    
    if (isset($al['completado']) && $al['completado'] == false) {
        if ($al['nota'] !== null && $al['nota'] !== '') {
            $nota_str = round((float)$al['nota']);
        }
        $estatus_str = "Reprobado"; // No completó -> Reprueba
    } else {
        if ($al['nota'] !== null && $al['nota'] !== '') {
            $nota_val = round((float)$al['nota']);
            $nota_str = $nota_val;
            if ($nota_val >= $nota_min) {
                $estatus_str = "Aprobado";
            } else {
                $estatus_str = "Participante"; // Completó pero nota < mínima
            }
        } else {
            $estatus_str = "Participante"; // Completó sin nota
        }
    }

    $pdf->SetX($marginX);
    $nombre_completo = mb_convert_case($al['nombre'] . ' ' . $al['apellido'], MB_CASE_TITLE, "UTF-8");
    $pdf->Cell($colNo, 6, $idx + 1, 1, 0, 'C');
    $pdf->Cell($colCed, 6, $al['cedula'], 1, 0, 'C');
    $pdf->Cell($colNom, 6, utf8_decode($nombre_completo), 1, 0, 'L');
    $pdf->Cell($colNota, 6, $nota_str, 1, 0, 'C');
    if (!$es_diplomado) {
        $pdf->Cell($colTomo, 6, isset($al['tomo']) ? $al['tomo'] : '-', 1, 0, 'C');
        $pdf->Cell($colFolio, 6, isset($al['folio']) ? $al['folio'] : '-', 1, 0, 'C');
    }
    $pdf->Cell($colEstatus, 6, utf8_decode($estatus_str), 1, 1, 'C');
}

// Pie de página de la página 2 o posteriores
if (isset($data['img_pie']) && file_exists($data['img_pie'])) {
    $pdf->Image($data['img_pie'], 10, 195, $pageWidthL - 20, 15);
}
