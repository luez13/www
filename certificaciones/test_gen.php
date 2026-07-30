<?php
$_GET['valor_unico'] = 'F48CB4CC139D97FB';
ob_start();
require __DIR__ . '/controllers/generar_certificado_materia.php';
$pdf_content = ob_get_clean();

echo "PDF size: " . strlen($pdf_content) . "\n";
echo "Firmantes log:\n";
echo file_get_contents(sys_get_temp_dir() . '/debug_firmantes.txt');
