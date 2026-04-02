<?php
/**
 * FPDF Template: Certificado Prueba (Edición Violeta / Mujeres)
 * ---------------------------------------------------------
 * Reglas Estrictas:
 * 1. Usar utf8_decode() en todo el texto.
 * 2. Usar fuentes Core (Times, Arial).
 */

// PAGINA 1: FRONTAL
$pdf->AddPage();

// 1. CARGAMOS LA FUENTE EDWARDIAN
$pdf->AddFont('Edwardian', '', 'edwardianscriptitc.php');

// 1. Fondo Específico (certificado_mujeres.jpeg)
$rutaMujeres = realpath(__DIR__ . '/../../public/assets/img/certificado_mujeres.jpeg');
if ($rutaMujeres && file_exists($rutaMujeres)) {
    $pdf->Image($rutaMujeres, 0, 0, 279.4, 215.9);
} else if (file_exists($data['fondoPath'])) {
    $pdf->Image($data['fondoPath'], 0, 0, 279.4, 215.9);
}

// 2. Encabezados (Y = 38mm, Interlineado reducido)
$pdf->SetFont('Times', 'B', 11);
$pdf->SetTextColor(28, 35, 49); 
$pdf->SetXY(0, 40);
$pdf->Cell(279.4, 4, utf8_decode('REPÚBLICA BOLIVARIANA DE VENEZUELA'), 0, 1, 'C');
$pdf->Cell(279.4, 4, utf8_decode('MINISTERIO DEL PODER POPULAR PARA LA EDUCACIÓN UNIVERSITARIA'), 0, 1, 'C');
$pdf->Cell(279.4, 4, utf8_decode('UNIVERSIDAD POLITÉCNICA TERRITORIAL AGROINDUSTRIAL DEL ESTADO TÁCHIRA'), 0, 1, 'C');

// 3. Texto de Otorga (Y = 75mm)
$pdf->SetFont('Arial', '', 14);
$pdf->SetXY(0, 75);
$pdf->Cell(279.4, 8, utf8_decode('Otorga el presente certificado al ciudadano (a):'), 0, 1, 'C');

// 4. Nombre del Estudiante (Y = 90mm)
$pdf->SetFont('Edwardian', '', 45); // FUENTE ELEGANTE
$pdf->SetTextColor(114, 47, 138); // Púrpura (#722f8a) - Restaurado
$pdf->SetXY(0, 90);
$pdf->Cell(279.4, 20, utf8_decode($data['nombreEstudiante']), 0, 1, 'C');
// Dibujar línea debajo del nombre (MORADA - Y = 110mm)
$pdf->SetDrawColor(114, 47, 138);
$pdf->SetLineWidth(0.5);
$pdf->Line(60, 110, 219.4, 110);

// 5. Cédula (Y = 115mm)
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(0, 115);
// Verificamos si la cédula ya trae el prefijo 'V-'
$cedulaLimpia = str_replace('V-', '', $data['cedula']);
$pdf->Cell(279.4, 6, utf8_decode('C.I. V- ' . trim($cedulaLimpia)), 0, 1, 'C');

// 6. Párrafo de aprobación (Y = 125mm) - ARQUITECTURA ROBUSTA (MultiCell)
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

$pdf->SetXY((279.4 - $wLinea1) / 2, 125); 
$pdf->SetFont('Arial', '', 14);
$pdf->Write(8, utf8_decode($textoAntesPaso));
$pdf->SetFont('Arial', 'B', 14);
$pdf->Write(8, utf8_decode($pasoTexto));
$pdf->SetFont('Arial', '', 14);
$pdf->Write(8, utf8_decode($textoIntermedio));

// --- LÍNEA 2: Nombre del Curso (MultiCell se encarga del auto-wrap y centrado) ---
$pdf->SetXY(30, 133); // Bajamos 8mm para la siguiente línea
$pdf->SetFont('Arial', 'B', 14);
$pdf->MultiCell(219.4, 8, utf8_decode($cursoTexto), 0, 'C');

// 7. Fecha (Y = 155mm)
if (!function_exists('f_FechaC')) {
    function f_FechaC($f) {
        if (!$f) return "no proporcionada";
        $m = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        $ts = strtotime($f);
        return "los " . date('d', $ts) . " días del mes de " . $m[date('n', $ts) - 1] . " de " . date('Y', $ts);
    }
}
$pdf->SetFont('Arial', '', 12);
$pdf->SetXY(0, 155);
$pdf->Cell(279.4, 6, utf8_decode('Certificación expedida en la Ciudad de San Cristóbal, ' . f_FechaC($data['fechaInscripcion'])), 0, 1, 'C');

// 8. Firmas Página 1 (Y = 165mm)
// 8. Firmas Página 1 (Y = 185mm)
if ($data['mostrar_firmas']) {
    $firmasP1 = array_filter($data['firmantes'], function ($f) { return $f['pagina'] == 1; });
    if (count($firmasP1) > 0) {
        $pdf->SetDrawColor(0, 0, 0); // Líneas de firma siempre NEGRAS
        foreach ($firmasP1 as $f) {
            // Posicionamiento basado en código: izq, cen, cuarta, der
            $posX = 139.7;
            $pCod = strtolower($f['posicion_codigo']);
            
            if (strpos($pCod, 'izq') !== false) $posX = 35;
            else if (strpos($pCod, 'der') !== false) $posX = 245;
            else if (strpos($pCod, 'cen') !== false) $posX = 105;
            else if (strpos($pCod, 'cuarta') !== false || strpos($pCod, 'firme4') !== false || strpos($pCod, 'extra') !== false || strpos($pCod, '4') !== false) $posX = 175;

            $w_box = 50; 
            $y_firmas = 185; 
            $pdf->SetXY($posX - ($w_box/2), $y_firmas);
            $actualX = $pdf->GetX();
            $actualY = $pdf->GetY();
            
            if (!empty($f['firma_base64'])) {
                $ext = 'png';
                if (strpos($f['firma_base64'], 'image/jpeg') !== false || strpos($f['firma_base64'], 'image/jpg') !== false) $ext = 'jpg';
                $tempFirma = sys_get_temp_dir() . '/fv1_' . uniqid() . '.' . $ext;
                $fData = explode(',', $f['firma_base64']);
                file_put_contents($tempFirma, base64_decode(end($fData)));
                $pdf->Image($tempFirma, $actualX + ($w_box/2) - 15, $actualY - 18, 30);
                @unlink($tempFirma);
            }
            
            $pdf->SetXY($actualX, $actualY);
            $pdf->SetFont('Arial', 'B', 9);
            $nombreF = mb_convert_case($f['titulo'] . ' ' . $f['nombre'], MB_CASE_TITLE, "UTF-8");
            // MultiCell permite que el nombre salte de línea si es muy largo
            $pdf->MultiCell($w_box, 4, utf8_decode($nombreF), 'T', 'C');
            
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetX($actualX);
            $pdf->MultiCell($w_box, 3.5, utf8_decode(mb_convert_case($f['cargo'], MB_CASE_TITLE, "UTF-8")), 0, 'C');
        }
    }
}

// ===================================

$pdf->AddPage();

// 1. Marca de Agua / Fondo (Eliminado en P2 según solicitud del usuario)
/*
$rutaMarcaAgua = realpath(__DIR__ . '/../../public/assets/img/marca_agua.png');
if ($rutaMarcaAgua && file_exists($rutaMarcaAgua)) {
    $pdf->Image($rutaMarcaAgua, 279.4/2 - 50, 60, 100); 
}
*/

// 2. Título Contenido y QR (Y = 40mm)
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(114, 47, 138); // Mantener morado
$pdf->SetXY(30, 40);
$pdf->Cell(100, 10, utf8_decode('CONTENIDO:'), 0, 0, 'L');

if (isset($qrTempPath) && file_exists($qrTempPath)) {
    $pdf->Image($qrTempPath, 210, 35, 35, 35);
}

// 3. Lista de Módulos (Y = 60mm)
$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(30, 60);
foreach ($data['modulos'] as $i => $mod) {
    $pdf->SetX(30);
    // MultiCell a 170mm para no chocar con el QR (X=210)
    $pdf->MultiCell(170, 6, utf8_decode(($i + 1) . '. ' . $mod['nombre_modulo']), 0, 'L');
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

// 5. Firmas Página 2 (Lógica Rectoria vs Normal)
if ($data['mostrar_firmas']) {
    // Lógica Rectoria detectada en el original
    $esRectoria = (strpos($data['tipo_curso'], 'rectoria') !== false);
    
    if ($esRectoria) {
        $firmasP2 = [];
        // Facilitador dinámico
        foreach ($data['firmantes'] as $f) {
            if (strtolower($f['cargo']) === 'facilitador') {
                $firmasP2[] = $f;
                break;
            }
        }
        // Director Fijo
        $firmasP2[] = [
            'nombre' => 'Msc. Emilio Losada',
            'cargo' => 'Director de PNF en Electrónica',
            'firma_base64' => isset($data['firma_director_rectoria_b64']) ? $data['firma_director_rectoria_b64'] : '', // Se asume que viene en el array $data
            'titulo' => ''
        ];
    } else {
        $firmasP2 = array_filter($data['firmantes'], function ($f) { return $f['pagina'] == 2; });
    }

    if (count($firmasP2) > 0) {
        $pdf->SetDrawColor(0, 0, 0);
        foreach ($firmasP2 as $f) {
            $pCod2 = strtolower($f['posicion_codigo']);
            $posX2 = 175; // NUEVO DEFAULT: Evita colisión con centro (105) si no hay código reconocido
            
            if (strpos($pCod2, 'izq') !== false) $posX2 = 35;
            else if (strpos($pCod2, 'der') !== false) $posX2 = 245;
            else if (strpos($pCod2, 'cen') !== false) $posX2 = 105;
            else if (strpos($pCod2, 'cuarta') !== false || strpos($pCod2, 'firme4') !== false || strpos($pCod2, 'extra') !== false || strpos($pCod2, '4') !== false) $posX2 = 175;

            $w_box2 = 50; 
            $y_firmas2 = 182;
            $pdf->SetXY($posX2 - ($w_box2/2), $y_firmas2);
            $actualX2 = $pdf->GetX();
            $actualY2 = $pdf->GetY();
            
            if (!empty($f['firma_base64'])) {
                $ext = 'png';
                if (strpos($f['firma_base64'], 'image/jpeg') !== false || strpos($f['firma_base64'], 'image/jpg') !== false) $ext = 'jpg';
                $tempFirma = sys_get_temp_dir() . '/fv2_' . uniqid() . '.' . $ext;
                $fData = explode(',', $f['firma_base64']);
                file_put_contents($tempFirma, base64_decode(end($fData)));
                $pdf->Image($tempFirma, $actualX2 + ($w_box2/2) - 15, $actualY2 - 18, 30);
                @unlink($tempFirma);
            }
            
            $pdf->SetXY($actualX2, $actualY2);
            $pdf->SetFont('Arial', 'B', 9);
            $nombreF = mb_convert_case($f['titulo'] . ' ' . $f['nombre'], MB_CASE_TITLE, "UTF-8");
            $pdf->MultiCell($w_box2, 4, utf8_decode($nombreF), 'T', 'C');
            
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetX($actualX2);
            $pdf->MultiCell($w_box2, 3.5, utf8_decode(mb_convert_case($f['cargo'], MB_CASE_TITLE, "UTF-8")), 0, 'C');
        }
    }
}
?>