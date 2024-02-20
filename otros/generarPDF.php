<?php
require_once('fpdf/fpdf.php');
require_once('db_connect.php');

function generate_pdf($conn, $data) {
    $stmt = $conn->prepare("SELECT usuario.nombre, usuario.apellido, usuario.correo, documentos.nombre_doc FROM usuario INNER JOIN doc_ref ON usuario.id=doc_ref.user_id INNER JOIN documentos ON doc_ref.document_id=documentos.id_doc WHERE usuario.cedula = :cedula AND documentos.nombre_doc = :nombre_doc");
    $stmt->execute([':cedula' => $data['cedula'], ':nombre_doc' => $data['selected'][0]]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['data']) && !empty($_GET['data'])) {
            // Decodificar los datos de la URL
            $jsonData = base64_decode($_GET['data']);
            $data = json_decode($jsonData, true);

            if (!empty($data)) {
                $result = generate_pdf($conn, $data);

                if ($result) {
                    $pdf = new FPDF();
                    $pdf->AddPage();
                    $pdf->SetFont('Arial','B',16);
                    $pdf->Cell(40,10,'Nombre: ' . $result['nombre']);
                    $pdf->Ln();
                    $pdf->Cell(40,10,'Apellido: ' . $result['apellido']);
                    $pdf->Ln();
                    $pdf->Cell(40,10,'Cedula: ' . $data['cedula']);
                    $pdf->Ln();
                    $pdf->Cell(40,10,'Documento seleccionado: ' . $result['nombre_doc']);
                    if (ob_get_length()) {
                        ob_end_clean();
                    }
                    header('Content-type: application/pdf');
                    $pdf->Output();
                } else {
                    echo "No se encontró un token con esos datos.";
                }
            } else {
                echo "Los datos proporcionados no son válidos.";
            }
        }
    }
} catch(PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(["Error" => "Ha ocurrido un error al generar el PDF"]);
}
?>