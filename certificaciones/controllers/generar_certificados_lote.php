<?php
// Incluir el archivo header.php en views
include '../views/header.php';

// Incluir el archivo model.php en config
include '../config/model.php';

$db = new DB();

$curso_id = isset($_POST['curso_id']) ? $_POST['curso_id'] : (isset($_GET['curso_id']) ? $_GET['curso_id'] : null);

if (!$curso_id) {
    die("Curso inválido");
}

// Obtener los certificados de los inscritos
$stmt = $db->prepare("
    SELECT valor_unico FROM cursos.certificaciones 
    WHERE curso_id = :curso_id
");
$stmt->execute(['curso_id' => $curso_id]);
$certificados = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($certificados)) {
    die("<p>No hay certificados disponibles.</p>");
}

// Si se envió el formulario para descargar los certificados
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['descargar_certificados'])) {
    $zip = new ZipArchive();
    $zipFileName = "certificados_curso_$curso_id.zip";
    $zipPath = "../certificados/$zipFileName";
    
    if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
        foreach ($certificados as $certificado) {
            $archivoCertificado = "../certificados/" . $certificado['valor_unico'] . ".pdf";
            if (file_exists($archivoCertificado)) {
                $zip->addFile($archivoCertificado, $certificado['valor_unico'] . ".pdf");
            }
        }
        
        $zip->close();
    
        header("Content-Type: application/zip");
        header("Content-Disposition: attachment; filename=$zipFileName");
        header("Content-Length: " . filesize($zipPath));
        readfile($zipPath);
    
        // Limpiar archivos después de la descarga
        foreach ($certificados as $certificado) {
            $archivoCertificado = "../certificados/" . $certificado['valor_unico'] . ".pdf";
            if (file_exists($archivoCertificado)) {
                unlink($archivoCertificado);
            }
        }
    
        unlink($zipPath);
        exit;
    }    
}
?>
<div class="container mt-4">
    <h2 class="text-center">Certificados del Curso</h2>

    <div class="row">
        <?php foreach ($certificados as $certificado): ?>
            <div class="col-md-4 mb-3">
                <iframe src="../controllers/generar_certificado.php?valor_unico=<?= htmlspecialchars($certificado['valor_unico']); ?>" width="100%" height="400px"></iframe>
            </div>
        <?php endforeach; ?>
    </div>
</div>