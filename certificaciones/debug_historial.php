<?php
session_start();
$_SESSION['user_id'] = 2;
$_GET['action'] = 'inscritos';

// emulate HTTP request
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
ob_start();
include __DIR__ . '/views/historial.php';
$output = ob_get_clean();
echo strpos($output, 'Certificados Modulares:') !== false ? "YES, SE MUESTRA" : "NO, NO SE MUESTRA";
// print the output
echo "\n\n";
echo substr($output, 0, 5000);
?>
