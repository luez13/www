<?php
session_start();

require_once('fpdf/fpdf.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['selected'] = $_POST['selected'];
    $_SESSION['nombre'] = $_POST['nombre'];
    $_SESSION['apellido'] = $_POST['apellido'];
    $_SESSION['correo'] = $_POST['correo'];

    $selected = $_SESSION['selected'];
    $nombre = $_SESSION['nombre'];
    $apellido = $_SESSION['apellido'];
    $correo = $_SESSION['correo'];

    // Generar un token único
    $token = hash('sha256', $nombre . $apellido . $correo . implode(',', $selected));

    // Aquí deberías almacenar el token en la base de datos

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);

    $pdf->Cell(40,10,'Nombre: ' . $nombre);
    $pdf->Ln();
    $pdf->Cell(40,10,'Apellido: ' . $apellido);
    $pdf->Ln();
    $pdf->Cell(40,10,'Correo: ' . $correo);
    $pdf->Ln();

    foreach ($selected as $item) {
        $pdf->Cell(40,10,$item);
        $pdf->Ln();
    }

    if (ob_get_length()) {
        ob_end_clean();
    } // Limpiar cualquier salida en el búfer

    // Enviar el PDF al navegador
    $pdf->Output('F', 'documentos/' . $token . '.pdf');

    // Devolver el token como respuesta
    echo $token;
}
?>
