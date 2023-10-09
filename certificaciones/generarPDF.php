<?php
session_start();

require_once('fpdf/fpdf.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['selected'])) {
        $_SESSION['selected'] = $_POST['selected'];
    }
    if (isset($_POST['nombre'])) {
        $_SESSION['nombre'] = $_POST['nombre'];
    }
    if (isset($_POST['apellido'])) {
        $_SESSION['apellido'] = $_POST['apellido'];
    }
    if (isset($_POST['correo'])) {
        $_SESSION['correo'] = $_POST['correo'];
    }
    if (isset($_POST['cedula'])) {
        $_SESSION['cedula'] = $_POST['cedula'];
    }

    $selected = isset($_SESSION['selected']) ? $_SESSION['selected'] : null;
    $nombre = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : null;
    $apellido = isset($_SESSION['apellido']) ? $_SESSION['apellido'] : null;
    $correo = isset($_SESSION['correo']) ? $_SESSION['correo'] : null;
    $cedula = isset($_SESSION['cedula']) ? $_SESSION['cedula'] : null;

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
        if ($cedula) {
            $stmt = $conn->prepare("SELECT id FROM usuario WHERE cedula = :cedula");
            $stmt->execute([':cedula' => $cedula]);
            $user_id = $stmt->fetchColumn();
        }

        // Obtener el id del documento a partir del nombre del documento seleccionado
        // Asegúrate de que 'selected[0]' es el nombre del documento seleccionado
        if ($selected) {
            $stmt = $conn->prepare("SELECT id_doc FROM documentos WHERE nombre_doc = :nombre_doc");
            $stmt->execute([':nombre_doc' => $selected[0]]);
            $document_id = $stmt->fetchColumn();
        }

        if (isset($user_id) && isset($document_id)) {
            // Preparar la consulta SQL para obtener el token de la tabla doc_ref
            $stmt = $conn->prepare("SELECT token FROM doc_ref WHERE user_id = :user_id AND document_id = :document_id");

            // Ejecutar la consulta SQL con los valores correctos
            $stmt->execute([':user_id' => $user_id, ':document_id' => $document_id]);

            // Obtener el token
            $token = $stmt->fetchColumn();
        } else {
            echo "No se encontró un usuario o documento con esos datos.";
            exit;
        }

        // Preparar la consulta SQL para obtener los datos asociados al token
        if ($token) {
            $stmt = $conn->prepare("SELECT usuario.nombre, usuario.apellido, usuario.correo, documentos.nombre_doc FROM doc_ref INNER JOIN usuario ON doc_ref.user_id=usuario.id INNER JOIN documentos ON doc_ref.document_id=documentos.id_doc WHERE doc_ref.token = :token");

            // Ejecutar la consulta SQL con el valor correcto
            $stmt->execute([':token' => $token]);

            // Obtener los datos asociados al token
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if (isset($data)) {
            // Aquí deberías generar el PDF con los datos obtenidos
        
            $pdf = new FPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial','B',16);
        
            // Aquí deberías usar los datos obtenidos para generar el PDF
            // Por ejemplo:
            $pdf->Cell(40,10,'Nombre: ' . $data['nombre']);
            $pdf->Ln();  // Nueva línea
            $pdf->Cell(40,10,'Apellido: ' . $data['apellido']);
            $pdf->Ln();  // Nueva línea
            $pdf->Cell(40,10,'Correo: ' . $data['correo']);
            $pdf->Ln();  // Nueva línea
            $pdf->Cell(40,10,'Cedula: ' . utf8_decode($cedula));
            $pdf->Ln();  // Nueva línea
            $pdf->Cell(40,10,'Documento seleccionado: ' . $selected[0]);
        
            if (ob_get_length()) {
                ob_end_clean();
            } // Limpiar cualquier salida en el búfer
        
            // Enviar el PDF como respuesta
            header('Content-type: application/pdf');
            $pdf->Output();  // Asegúrate de que esta es la última línea de código que se ejecuta
        } else {
            echo "No se encontró un token con esos datos.";
        }
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>