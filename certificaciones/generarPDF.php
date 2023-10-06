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
    $_SESSION['cedula'] = $_POST['cedula'];

    $selected = $_SESSION['selected'];
    $nombre = $_SESSION['nombre'];
    $apellido = $_SESSION['apellido'];
    $correo = $_SESSION['correo'];
    $cedula = $_SESSION['cedula'];

    // Generar un token único
$token = hash('sha256', $nombre . $apellido . $correo . implode(',', $selected));

// Aquí deberías almacenar el token en la base de datos
try {
    // Asegúrate de cambiar estos valores por los correctos para tu base de datos
    $host = "localhost";
    $db   = "pruebas";
    $user = "root";
    $pass = "";

    // Crear una nueva conexión PDO
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener el id del usuario a partir de la cédula
    $stmt = $conn->prepare("SELECT id FROM usuario WHERE cedula = :cedula");
    $stmt->execute([':cedula' => $_POST['cedula']]);
    $user_id = $stmt->fetchColumn();

    // Obtener el id del documento a partir del nombre del documento seleccionado
    // Asegúrate de que 'selected[0]' es el nombre del documento seleccionado
    $stmt = $conn->prepare("SELECT id_doc FROM documentos WHERE nombre_doc = :nombre_doc");
    $stmt->execute([':nombre_doc' => $_POST['selected'][0]]);
    $document_id = $stmt->fetchColumn();

    if ($user_id && $document_id) {
        // Preparar la consulta SQL para insertar el token en la tabla doc_ref
        $stmt = $conn->prepare("UPDATE doc_ref SET token = :token WHERE user_id = :user_id AND document_id = :document_id");

        // Ejecutar la consulta SQL con los valores correctos
        $stmt->execute([':token' => $token, ':user_id' => $user_id, ':document_id' => $document_id]);
    } else {
        echo "No se encontró un usuario o documento con esos datos.";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

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
    $pdf->Cell(40,10,'Cédula: ' . $cedula);
    $pdf->Ln();

    foreach ($selected as $item) {
        $pdf->Cell(40,10,$item);
        $pdf->Ln();
    }

    if (ob_get_length()) {
        ob_end_clean();
    } // Limpiar cualquier salida en el búfer

    // Enviar el PDF al navegador
    //$pdf->Output('F', 'documentos/' . $token . '.pdf');

    // Devolver el token como respuesta
    echo $token;
}
?>
