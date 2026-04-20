<?php
/**
 * FPDF Template: Certificado Base
 * ---------------------------------------------------------
 * Reglas Estrictas:
 * 1. Usar utf8_decode() en todo el texto.
 * 2. Usar fuentes Core (Times, Arial).
 */

// PAGINA 1: FRONTAL
$pdf->AddPage();

// 1. CARGAMOS LA FUENTE EDWARDIAN
$pdf->AddFont('Edwardian', '', 'edwardianscriptitc.php');

// 1. Fondo (Ajustado a Letter Landscape: 279.4 x 215.9 mm)
if (file_exists($data['fondoPath'])) {
    $pdf->Image($data['fondoPath'], 0, 0, 279.4, 215.9);
}

// 2. Encabezados (Y = 38mm, Interlineado reducido)
$pdf->SetFont('Times', 'B', 11);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(0, 38);
$pdf->Cell(279.4, 4, utf8_decode('REPÚBLICA BOLIVARIANA DE VENEZUELA'), 0, 1, 'C');
$pdf->Cell(279.4, 4, utf8_decode('MINISTERIO DEL PODER POPULAR PARA LA EDUCACIÓN UNIVERSITARIA'), 0, 1, 'C');
$pdf->Cell(279.4, 4, utf8_decode('UNIVERSIDAD POLITÉCNICA TERRITORIAL AGROINDUSTRIAL DEL ESTADO TÁCHIRA'), 0, 1, 'C');

// 3. Texto de Otorga (Y = 60mm)
$pdf->SetFont('Arial', '', 14);
$pdf->SetXY(0, 60);
$pdf->Cell(279.4, 8, utf8_decode('Otorga el presente certificado al ciudadano (a):'), 0, 1, 'C');

// 4. Nombre del Estudiante (Y = 75mm)
$pdf->SetFont('Edwardian', '', 45); // FUENTE ELEGANTE
$pdf->SetTextColor(173, 4, 4); // Rojo oscuro
$pdf->SetXY(0, 75);
$pdf->Cell(279.4, 20, utf8_decode($data['nombreEstudiante']), 0, 1, 'C');

// 5. Cédula (Y = 100mm)
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(0, 100);
$cedulaLimpia = str_replace('V-', '', $data['cedula']);
$pdf->Cell(279.4, 6, utf8_decode('C.I. V- ' . trim($cedulaLimpia)), 0, 1, 'C');

// 6. Párrafo de aprobación (Y = 112mm) - ARQUITECTURA ROBUSTA (MultiCell)
$pdf->SetTextColor(0, 0, 0);

$esParticipacion = ($data['paso'] === "aprobado" && (empty($data['nota']) || $data['nota'] == 0));
$textoAntesPaso = $esParticipacion ? "Por su " : "Por haber ";
$pasoTexto = mb_strtoupper($esParticipacion ? "PARTICIPACION" : $data['paso'], 'UTF-8');
$textoIntermedio = " en {$data['articulo_tipo_curso']} {$data['tipo_curso']} de:";
$cursoTexto = mb_strtoupper($data['nombre_curso'], 'UTF-8');

// --- LÍNEA 1: Texto introductorio (Centrado matemático puro) ---
$pdf->SetFont('Arial', '', 14);
$w1 = $pdf->GetStringWidth(utf8_decode($textoAntesPaso));
$pdf->SetFont('Arial', 'B', 14);
$w2 = $pdf->GetStringWidth(utf8_decode($pasoTexto));
$pdf->SetFont('Arial', '', 14);
$w3 = $pdf->GetStringWidth(utf8_decode($textoIntermedio));
$wLinea1 = $w1 + $w2 + $w3;

$pdf->SetXY((279.4 - $wLinea1) / 2, 112); 
$pdf->SetFont('Arial', '', 14);
$pdf->Write(8, utf8_decode($textoAntesPaso));
$pdf->SetFont('Arial', 'B', 14);
$pdf->Write(8, utf8_decode($pasoTexto));
$pdf->SetFont('Arial', '', 14);
$pdf->Write(8, utf8_decode($textoIntermedio));

// --- LÍNEA 2: Nombre del Curso (MultiCell se encarga del auto-wrap y centrado) ---
$pdf->SetXY(30, 120); // Bajamos 8mm para la siguiente línea
$pdf->SetFont('Arial', 'B', 14);
$pdf->MultiCell(219.4, 8, utf8_decode($cursoTexto), 0, 'C');

// 7. Fecha (Y = 140mm)
function f_Fecha($f) {
    if (!$f) return "no proporcionada";
    $m = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $ts = strtotime($f);
    return "los " . date('d', $ts) . " días del mes de " . $m[date('n', $ts) - 1] . " de " . date('Y', $ts);
}
$pdf->SetFont('Arial', '', 12);
$pdf->SetXY(0, 140);
$pdf->Cell(279.4, 6, utf8_decode('Certificación expedida en la Ciudad de San Cristóbal, ' . f_Fecha($data['fechaInscripcion'])), 0, 1, 'C');

// 8. Firmas Página 1 (Y = 180mm)
$firmasP1 = array_filter($data['firmantes'], function ($f) { return $f['pagina'] == 1; });
if (count($firmasP1) > 0) {
    $pdf->SetDrawColor(0, 0, 0); // Líneas de firma siempre NEGRAS
    foreach ($firmasP1 as $f) {
        // --- Cálculo de Posición Horizontal (Grilla de 4 Columnas) ---
        $posX = 105; // Default: Centro
        $pCod = strtolower($f['posicion_codigo']);
        
        if (strpos($pCod, 'izq') !== false) $posX = 35;
        else if (strpos($pCod, 'der') !== false) $posX = 245;
        else if (strpos($pCod, 'cen') !== false) $posX = 105;
        else if (strpos($pCod, 'cuarta') !== false || strpos($pCod, 'firme4') !== false || strpos($pCod, 'extra') !== false) $posX = 175;
        
        $w_box = 55; // Bloque de firma estándar
        $y_firmas = 180; 
        
        $pdf->SetXY($posX - ($w_box / 2), $y_firmas);
        $actualX = $pdf->GetX();
        $actualY = $pdf->GetY();
        
        // --- IMAGEN DE FIRMA (Solo si está habilitado y existe el dato) ---
        // Se valida que la imagen no esté vacía. FPDF colapsa si recibe data inválida.
        if ($data['mostrar_firmas'] && !empty($f['firma_base64'])) {
            $ext = (strpos($f['firma_base64'], 'image/jpeg') !== false) ? 'jpg' : 'png';
            $tempFirma = sys_get_temp_dir() . '/f_' . uniqid() . '.' . $ext;
            $fData = explode(',', $f['firma_base64']);
            if (isset($fData[1])) {
                file_put_contents($tempFirma, base64_decode($fData[1]));
                if (file_exists($tempFirma)) {
                    // Posicionamos la firma 18mm arriba de la línea
                    $pdf->Image($tempFirma, $actualX + ($w_box / 2) - 15, $actualY - 18, 30); 
                    @unlink($tempFirma);
                }
            }
        }
        
        // --- LÍNEA, NOMBRE Y CARGO (Siempre visibles) ---
        $pdf->SetXY($actualX, $actualY);
        $pdf->SetFont('Arial', 'B', 9);
        $nombreF = mb_convert_case($f['titulo'] . ' ' . $f['nombre'], MB_CASE_TITLE, "UTF-8");
        // El borde 'T' (Top) crea la línea de firma sobre el nombre
        $pdf->MultiCell($w_box, 4, utf8_decode($nombreF), 'T', 'C');
        
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetX($actualX);
        $pdf->MultiCell($w_box, 3.5, utf8_decode(mb_convert_case($f['cargo'], MB_CASE_TITLE, "UTF-8")), 0, 'C');
    }
}

// ==========================================================

// PAGINA 2: REVERSO (CONTENIDO)
$pdf->AddPage();

// 1. Fondo (Eliminado en P2 según solicitud del usuario)
/*
if (file_exists($data['fondoPath'])) {
    $pdf->Image($data['fondoPath'], 0, 0, 279.4, 215.9);
}
*/

// 2. Título Contenido y QR (Y = 40mm)
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetXY(30, 40);
$pdf->Cell(100, 10, utf8_decode('CONTENIDO:'), 0, 0, 'L');

// QR Code (Usando la ruta temporal del controlador)
if (isset($qrTempPath) && file_exists($qrTempPath)) {
    $pdf->Image($qrTempPath, 210, 35, 35, 35);
}

// 3. Lista de Módulos (Y = 60mm)
$pdf->SetFont('Arial', '', 11); // Un punto menos por si son muchos
$pdf->SetXY(30, 60);
foreach ($data['modulos'] as $i => $mod) {
    $pdf->SetX(30);
    // MultiCell a 175mm para dejar espacio libre al QR que empieza en 210
    $pdf->MultiCell(175, 6, utf8_decode(($i + 1) . '. ' . $mod['nombre_modulo']), 0, 'L');
}

// 4. Registro Inferior (Y = 145mm)
$pdf->SetXY(30, 145);
$pdf->SetFont('Arial', '', 12);
$textoReg = "Registrado en formación permanente tomo " . $data['tomo'] . " folio " . $data['folio'] . ".\n";
if (!empty($data['nota']) && $data['nota'] != 0) {
    $textoReg .= "Presentando una calificación final de " . $data['nota'] . " de una nota máxima (20).\n";
}
$textoReg .= "El programa tuvo una duración de " . $data['horas_cronologicas'] . " horas cronológicas.";
$pdf->MultiCell(219.4, 6, utf8_decode($textoReg), 0, 'L');

// 5. Firmas Página 2 (Y = 180mm)
$firmasP2 = array_filter($data['firmantes'], function ($f) { return $f['pagina'] == 2; });
if (count($firmasP2) > 0) {
    $pdf->SetDrawColor(0, 0, 0);
    foreach ($firmasP2 as $f) {
        // --- Cálculo de Posición Horizontal (Grilla de 4 Columnas) ---
        $posX2 = 175; // Default: Cuarta
        $pCod2 = strtolower($f['posicion_codigo']);
        
        if (strpos($pCod2, 'izq') !== false) $posX2 = 35;
        else if (strpos($pCod2, 'der') !== false) $posX2 = 245;
        else if (strpos($pCod2, 'cen') !== false) $posX2 = 105;
        else if (strpos($pCod2, 'cuarta') !== false || strpos($pCod2, 'firme4') !== false || strpos($pCod2, 'extra') !== false) $posX2 = 175;

        $w_box2 = 55; 
        $y_firmas2 = 180; // Misma altura que la primera hoja para consistencia
        
        $pdf->SetXY($posX2 - ($w_box2 / 2), $y_firmas2);
        $actualX2 = $pdf->GetX();
        $actualY2 = $pdf->GetY();
        
        // --- IMAGEN DE FIRMA (Condicional) ---
        if ($data['mostrar_firmas'] && !empty($f['firma_base64'])) {
            $ext = (strpos($f['firma_base64'], 'image/jpeg') !== false) ? 'jpg' : 'png';
            $tempFirma = sys_get_temp_dir() . '/f2_' . uniqid() . '.' . $ext;
            $fData = explode(',', $f['firma_base64']);
            if (isset($fData[1])) {
                file_put_contents($tempFirma, base64_decode($fData[1]));
                if (file_exists($tempFirma)) {
                    $pdf->Image($tempFirma, $actualX2 + ($w_box2 / 2) - 15, $actualY2 - 18, 30);
                    @unlink($tempFirma);
                }
            }
        }
        
        // --- LÍNEA, NOMBRE Y CARGO (Siempre visibles) ---
        $pdf->SetXY($actualX2, $actualY2);
        $pdf->SetFont('Arial', 'B', 9);
        $nombreF = mb_convert_case($f['titulo'] . ' ' . $f['nombre'], MB_CASE_TITLE, "UTF-8");
        $pdf->MultiCell($w_box2, 4, utf8_decode($nombreF), 'T', 'C');
        
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetX($actualX2);
        $pdf->MultiCell($w_box2, 3.5, utf8_decode(mb_convert_case($f['cargo'], MB_CASE_TITLE, "UTF-8")), 0, 'C');
    }
}
?>