<?php
// Autoloader de Composer para cargar Dompdf y php-qrcode
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/model.php';
require_once __DIR__ . '/../models/curso.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\QrCode;

$certificateData = null;
$error_message = null;

if (isset($_GET['valor_unico'])) {
    $valor_unico = $_GET['valor_unico'];
    $db = new DB();
    $curso = new Curso($db);

    // Usamos la función que devuelve TODOS los datos, incluyendo los firmantes
    $datos_completos = $curso->obtenerDatosCompletosCertificado($valor_unico);

    if ($datos_completos) {
        $horas_cronologicas = isset($datos_completos['horas_cronologicas']) ? $datos_completos['horas_cronologicas'] : 0;
        if ($datos_completos['tipo_curso'] === "masterclass" && $horas_cronologicas == 0) {
            $horas_cronologicas = 4;
        }

        $certificateData = [
            'nombreEstudiante' => $datos_completos['nombre_estudiante'],
            'apellidoEstudiante' => $datos_completos['apellido_estudiante'],
            'cedula' => $datos_completos['cedula'],
            'paso' => $datos_completos['completado'] ? "aprobado" : "no aprobado",
            'fechaInscripcion' => $datos_completos['fecha_inscripcion'],
            'inicioMesCurso' => $datos_completos['inicio_mes'],
            'fechaFinalizacionCurso' => $datos_completos['fecha_finalizacion'],
            'tomo' => $datos_completos['tomo'],
            'folio' => $datos_completos['folio'],
            'nota' => $datos_completos['nota'],
            'tipo_curso' => $datos_completos['tipo_curso'],
            'nombre_curso' => $datos_completos['nombre_curso'],
            'horas_cronologicas' => $horas_cronologicas,
            'modulos' => isset($datos_completos['modulos']) ? $datos_completos['modulos'] : array(),
            'articulo_tipo_curso' => ($datos_completos['tipo_curso'] === "charla" || $datos_completos['tipo_curso'] === "masterclass") ? "la" : "el",
            'certificadoUrl' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}" . explode('/controllers/', $_SERVER['PHP_SELF'])[0] . "/controllers/generar_certificado.php?valor_unico={$valor_unico}",
            // Rutas absolutas del servidor para que Dompdf las lea nativamente
            'imagePath' => realpath(__DIR__ . '/../public/assets/img/marca_agua.png'),
            'bannerPath' => realpath(__DIR__ . '/../public/assets/img/banner_certificado.jpg'),
            'footerPath' => realpath(__DIR__ . '/../public/assets/img/footer.jpg'),
            'firmantes' => isset($datos_completos['firmantes']) ? $datos_completos['firmantes'] : array(),
            'mostrar_firmas' => isset($datos_completos['firma_digital']) ? $datos_completos['firma_digital'] : false,
            // Archivo de vista a cargar (viene de la BD, fallback: certificado_base.php)
            'archivo_vista' => isset($datos_completos['archivo_vista']) && !empty($datos_completos['archivo_vista']) ? $datos_completos['archivo_vista'] : 'certificado_base.php'
        ];
    } else {
        $error_message = "No se encontraron datos de certificación para el valor único proporcionado.";
    }
} else {
    $error_message = "No se proporcionó un valor único para generar el certificado.";
}

if ($error_message) {
    die($error_message);
}
if (!$certificateData) {
    die("Error crítico: No se pudieron cargar los datos del certificado.");
}

// 1. Generación de QR en Base64 para inyectarlo en la vista con endroid/qr-code v2.5
$qrCode = new QrCode($certificateData['certificadoUrl']);
$qrCode->setSize(150);
$qrCode->setMargin(5);
$qrCode->setWriterByName('png');
$qrCodeBase64 = 'data:image/png;base64,' . base64_encode($qrCode->writeString());

// Variables globales para la vista
$data = $certificateData;
$qr = $qrCodeBase64;

// 2. Capturamos el HTML de la vista
ob_start();
$rutaVista = realpath(__DIR__ . '/../views/certificados/' . $data['archivo_vista']);
if (!$rutaVista || !file_exists($rutaVista)) {
    die("La plantilla de certificado no existe: " . $data['archivo_vista']);
}
require $rutaVista;
$html = ob_get_clean();

// 3. Configuración de Dompdf (Adaptado para 0.8.3)
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->set('chroot', realpath(__DIR__ . '/../')); // Permite leer de todo el proyecto local

// Configurar directorio de fuentes temporal para métricas (.ufm y .php de las tipografías)
$fontDir = realpath(__DIR__ . '/../public/') . '/temp_fonts';
if (!file_exists($fontDir)) {
    if(!mkdir($fontDir, 0777, true)) {
        die("No se pudo crear la carpeta de caché de fuentes en public/temp_fonts");
    }
}
$options->set('fontDir', $fontDir);
$options->set('fontCache', $fontDir);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('letter', 'landscape');
$dompdf->render();

// 4. Forzar la descarga o visualización en navegador
$nombreArchivo = "Certificado_" . str_replace(' ', '_', $data['nombreEstudiante']) . "_" . str_replace(' ', '_', $data['apellidoEstudiante']) . ".pdf";
$dompdf->stream($nombreArchivo, array("Attachment" => false)); // Attachment=>false lo muestra en el navegador primero
?>