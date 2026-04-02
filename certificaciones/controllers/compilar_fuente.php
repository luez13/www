<?php
// Cargar la utilidad MakeFont nativa de FPDF
require_once __DIR__ . '/../vendor/setasign/fpdf/makefont/makefont.php';

// Ruta absoluta a tu archivo TTF
$rutaFuenteTTF = realpath(__DIR__ . '/../public/assets/vendor/edwardianscriptitc.ttf');

if (!$rutaFuenteTTF || !file_exists($rutaFuenteTTF)) {
    die("Error: No se encuentra el archivo TTF en la ruta especificada.");
}

// Compilar la fuente con codificación cp1252 (Estándar para América Latina / Español)
MakeFont($rutaFuenteTTF, 'cp1252');

echo "COMPILACIÓN EXITOSA. Revisa la carpeta public/assets/vendor/. Deberías ver dos archivos nuevos: edwardianscriptitc.php y edwardianscriptitc.z";
?>