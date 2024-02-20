<?php/*
require_once('fpdf/fpdf.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];

    // Aquí deberías buscar en la base de datos los datos asociados a ese token
    // Asegúrate de cambiar estos valores por los correctos para tu base de datos
    $host = "localhost";
    $db   = "pruebas";
    $user = "root";
    $pass = "";

    // Crear una nueva conexión PDO
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Preparar la consulta SQL para obtener los datos asociados al token
    $stmt = $conn->prepare("SELECT usuario.nombre, usuario.apellido, usuario.correo, documentos.nombre_doc FROM doc_ref INNER JOIN usuario ON doc_ref.user_id=usuario.id INNER JOIN documentos ON doc_ref.document_id=documentos.id_doc WHERE doc_ref.token = :token");

    // Ejecutar la consulta SQL con el valor correcto
    $stmt->execute([':token' => $token]);

    // Obtener los datos asociados al token
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        // Aquí deberías generar el PDF con los datos obtenidos

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',16);

        // Aquí deberías usar los datos obtenidos para generar el PDF
        // Por ejemplo:
        $pdf->Cell(40,10,'Nombre: ' . $data['nombre']);
        $pdf->Ln();
        // ...

        if (ob_get_length()) {
            ob_end_clean();
        } // Limpiar cualquier salida en el búfer

        // Enviar el PDF al navegador
        $pdf->Output('I', $token . '.pdf');
    } else {
        echo "No se encontró un token con esos datos.";
    }
}*/
?>