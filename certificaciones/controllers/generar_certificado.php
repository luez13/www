<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/model.php';
require_once __DIR__ . '/../models/curso.php';

use Endroid\QrCode\QrCode;

$certificateData = null;

if (isset($_GET['valor_unico'])) {
    $valor_unico = $_GET['valor_unico'];
    $db = new DB();
    $curso = new Curso($db);

    $datos_completos = $curso->obtenerDatosCompletosCertificado($valor_unico);

    if ($datos_completos) {
        $horas_cronologicas = isset($datos_completos['horas_cronologicas']) ? $datos_completos['horas_cronologicas'] : 0;
        if ($datos_completos['tipo_curso'] === "masterclass" && $horas_cronologicas == 0) {
            $horas_cronologicas = 4;
        }

        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}" . explode('/controllers/', $_SERVER['PHP_SELF'])[0];

        // Lógica de Plantilla Dinámica
        $nombreVistaBD = isset($datos_completos['archivo_vista']) && !empty($datos_completos['archivo_vista']) ? $datos_completos['archivo_vista'] : 'certificado_base.php';
        $nombreFondo = str_replace('.php', '.jpg', $nombreVistaBD);
        
        // El fondo específico por curso (.jpg)
        $rutaFondoAbsoluta = realpath(__DIR__ . '/../public/assets/img/' . $nombreFondo);
        if (!$rutaFondoAbsoluta || !file_exists($rutaFondoAbsoluta)) {
            $rutaFondoAbsoluta = realpath(__DIR__ . '/../public/assets/img/certificado_base.jpg');
        }

        $certificateData = [
            'nombreEstudiante' => mb_convert_case($datos_completos['nombre_estudiante'] . ' ' . $datos_completos['apellido_estudiante'], MB_CASE_TITLE, "UTF-8"),
            'cedula' => $datos_completos['cedula'],
            'paso' => $datos_completos['completado'] ? "aprobado" : "no aprobado",
            'fechaInscripcion' => $datos_completos['fecha_inscripcion'],
            'inicioMesCurso' => $datos_completos['inicio_mes'],
            'fechaFinalizacionCurso' => $datos_completos['fecha_finalizacion'],
            'tomo' => $datos_completos['tomo'],
            'folio' => $datos_completos['folio'],
            'nota' => $datos_completos['nota'],
            'tipo_curso' => str_replace('_rectoria', '', $datos_completos['tipo_curso']),
            'nombre_curso' => mb_strtoupper($datos_completos['nombre_curso'], 'UTF-8'),
            'horas_cronologicas' => $horas_cronologicas,
            'modulos' => isset($datos_completos['modulos']) ? $datos_completos['modulos'] : array(),
            'articulo_tipo_curso' => ($datos_completos['tipo_curso'] === "charla" || $datos_completos['tipo_curso'] === "masterclass") ? "la" : "el",
            'certificadoUrl' => $baseUrl . "/controllers/generar_certificado.php?valor_unico={$valor_unico}",
            'fondoPath' => $rutaFondoAbsoluta,
            'firmantes' => isset($datos_completos['firmantes']) ? $datos_completos['firmantes'] : array(),
            'archivo_vista' => $nombreVistaBD,
            'mostrar_firmas' => isset($datos_completos['firma_digital']) ? $datos_completos['firma_digital'] : false,
        ];
    } else {
        die("No se encontraron datos de certificación.");
    }
} else {
    die("No se proporcionó el valor único.");
}

$data = $certificateData;

// 1. Generar QR en archivo temporal
$qrCode = new QrCode($data['certificadoUrl']);
$qrCode->setSize(150);
$qrCode->setMargin(0);
$qrCode->setWriterByName('png');
$qrTempPath = sys_get_temp_dir() . '/qr_' . uniqid() . '.png';
file_put_contents($qrTempPath, $qrCode->writeString());


// ==========================================
// INICIA FPDF (El motor a prueba de fallos)
// ==========================================

// Le decimos a FPDF dónde buscar las tipografías personalizadas
define('FPDF_FONTPATH', realpath(__DIR__ . '/../public/assets/vendor/'));

$pdf = new \FPDF('L', 'mm', 'Letter');
$pdf->SetAutoPageBreak(false);

// Cargar la Vista (Ahora contiene Comandos FPDF)
$rutaVista = realpath(__DIR__ . '/../views/certificados/' . $data['archivo_vista']);
if ($rutaVista && file_exists($rutaVista)) {
    require $rutaVista;
} else {
    // Si no existe la plantilla específica, usamos certificado_base.php como fallback
    require __DIR__ . '/../views/certificados/certificado_base.php';
}

// Limpiar archivo temporal del QR
@unlink($qrTempPath);

// Descargar/Mostrar PDF
$nombreArchivo = "Certificado_" . str_replace(' ', '_', $data['nombreEstudiante']) . ".pdf";
$pdf->Output('I', $nombreArchivo);
?>