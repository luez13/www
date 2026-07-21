<?php
function extract_docx_text($filename) {
    $zip = new ZipArchive;
    $text = "";
    if ($zip->open($filename) === TRUE) {
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        if ($xml) {
            $text = strip_tags($xml);
        }
    }
    return $text;
}

echo "CONTRATO diorela:\n";
echo substr(extract_docx_text('c:/laragon/www/certificaciones/public/plantillas/CONTRATO diorela.docx'), 0, 2000);

echo "\n\nYoselin:\n";
echo substr(extract_docx_text('c:/laragon/www/certificaciones/public/plantillas/Yoselin.docx'), 0, 2000);
?>
