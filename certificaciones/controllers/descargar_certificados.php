<?php
require_once('../config/model.php');
$db = new DB();

$curso_id = isset($_POST['curso_id']) ? $_POST['curso_id'] : null;
if (!$curso_id) {
    die("Curso inválido");
}

// Obtener los certificados
$stmt = $db->prepare("
    SELECT valor_unico FROM cursos.certificaciones 
    WHERE curso_id = :curso_id
");
$stmt->execute(['curso_id' => $curso_id]);
$certificados = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($certificados)) {
    die("No hay certificados disponibles.");
}

// Crear ZIP en memoria con extensión correcta
$zip = new ZipArchive();
$tempZip = tempnam(sys_get_temp_dir(), "certificados_") . ".zip";
$zip->open($tempZip, ZipArchive::CREATE);

foreach ($certificados as $certificado) {
    // Usar una URL absoluta en lugar de una ruta relativa
    $certificadoUrl = "http://localhost/certificaciones/controllers/generar_certificado.php?valor_unico=" . $certificado['valor_unico'];

    // Obtener contenido del PDF
    $pdfContent = file_get_contents($certificadoUrl);

    // Validar contenido antes de agregar al ZIP
    if ($pdfContent && strpos($pdfContent, "%PDF") === 0) { 
        $zip->addFromString("certificado_" . $certificado['valor_unico'] . ".pdf", $pdfContent);
    } else {
        echo "Error: El archivo no es un PDF válido - " . $certificado['valor_unico'] . "<br>";
    }
}

// Verificar si se agregaron archivos antes de cerrar el ZIP
if ($zip->numFiles == 0) {
    die("Error: No se agregaron archivos al ZIP.");
}

$zip->close();

// Descargar el ZIP correctamente
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="certificados_curso_' . $curso_id . '.zip"');
header('Content-Length: ' . filesize($tempZip));
readfile($tempZip);

// Eliminar archivo temporal
unlink($tempZip);
exit;
