<?php
// controllers/generar_constancia_facilitador.php
session_start();
if (!isset($_SESSION['user_id'])) { die("Acceso denegado."); }

require_once('../config/model.php');

$id_materia = isset($_GET['id_materia']) ? intval($_GET['id_materia']) : 0;
if (!$id_materia) die("Falta ID de la materia");

$db = new DB();

$sql = "SELECT m.nombre_materia, m.duracion_bimestres, m.total_horas, m.modalidad, 
               c.nombre_curso, c.tipo_curso, c.inicio_mes, c.fecha_finalizacion,
               u.nombre, u.apellido, u.cedula 
        FROM cursos.materias_bimestre m
        JOIN cursos.cursos c ON m.id_curso = c.id_curso
        JOIN cursos.usuarios u ON m.docente_id = u.id
        WHERE m.id_materia_bimestre = :id_materia";

$stmt = $db->getConn()->prepare($sql);
$stmt->execute(['id_materia' => $id_materia]);
$datos = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$datos) die("Datos no encontrados.");

// Formatear fechas si es necesario
$fecha_emision = date('d/m/Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generando Constancia...</title>
    <!-- jsPDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        body { font-family: sans-serif; text-align: center; padding: 50px; background-color: #f8f9fc; }
        .loader { border: 4px solid #f3f3f3; border-top: 4px solid #4e73df; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 20px auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div id="loading">
        <div class="loader"></div>
        <h2>Generando Constancia de Docencia...</h2>
        <p>Por favor, espere un momento.</p>
    </div>

    <script>
    const DATOS = <?= json_encode($datos) ?>;
    const FECHA_EMISION = "<?= $fecha_emision ?>";

    window.onload = function() {
        if (!window.jspdf) {
            alert("Error al cargar la librería PDF.");
            return;
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({
            orientation: 'landscape',
            unit: 'mm',
            format: 'letter'
        });

        const width = doc.internal.pageSize.getWidth();
        const height = doc.internal.pageSize.getHeight();

        // Fondo y borde
        doc.setFillColor(245, 245, 245);
        doc.rect(0, 0, width, height, "F");
        
        doc.setDrawColor(20, 50, 100);
        doc.setLineWidth(2);
        doc.rect(10, 10, width - 20, height - 20);
        doc.setLineWidth(0.5);
        doc.rect(12, 12, width - 24, height - 24);

        // Título
        doc.setFont("helvetica", "bold");
        doc.setFontSize(24);
        doc.setTextColor(20, 50, 100);
        doc.text("CONSTANCIA DE FACILITADOR", width / 2, 40, { align: "center" });

        // Texto principal
        doc.setFontSize(14);
        doc.setTextColor(50, 50, 50);
        doc.setFont("helvetica", "normal");
        
        const nombreCompleto = DATOS.nombre.toUpperCase() + " " + DATOS.apellido.toUpperCase();
        
        let texto = "Hace constar que el/la ciudadano(a);\n\n";
        texto += nombreCompleto + " titular de la Cédula de Identidad Nro. " + DATOS.cedula + "\n\n";
        texto += "Ha participado en calidad de FACILITADOR de la materia/módulo:\n\n";
        
        const lineasTexto = doc.splitTextToSize(texto, width - 60);
        doc.text(lineasTexto, width / 2, 70, { align: "center" });

        doc.setFont("helvetica", "bold");
        doc.setFontSize(16);
        doc.setTextColor(20, 50, 100);
        doc.text(DATOS.nombre_materia.toUpperCase(), width / 2, 110, { align: "center" });

        doc.setFont("helvetica", "normal");
        doc.setFontSize(14);
        doc.setTextColor(50, 50, 50);
        
        const lineaCurso = "Correspondiente al programa formativo: " + DATOS.nombre_curso.toUpperCase();
        const lineasCurso = doc.splitTextToSize(lineaCurso, width - 60);
        doc.text(lineasCurso, width / 2, 130, { align: "center" });

        const detalleTexto = "Modalidad: " + (DATOS.modalidad || 'Presencial/Virtual') + "     Horas Académicas: " + (DATOS.total_horas || '-');
        doc.text(detalleTexto, width / 2, 150, { align: "center" });

        // Fecha de emisión
        doc.setFont("helvetica", "italic");
        doc.setFontSize(11);
        doc.text("Constancia emitida el " + FECHA_EMISION + ".", width / 2, 170, { align: "center" });

        // Línea de firma
        doc.setDrawColor(50, 50, 50);
        doc.setLineWidth(0.5);
        doc.line((width / 2) - 40, 195, (width / 2) + 40, 195);
        doc.setFont("helvetica", "bold");
        doc.setFontSize(12);
        doc.text("Coordinación Académica", width / 2, 203, { align: "center" });

        // Guardar/Mostrar
        const pdfOutput = doc.output('blob');
        const blobUrl = URL.createObjectURL(pdfOutput);
        const nombreArchivo = `Constancia_Docente_${DATOS.cedula}.pdf`;

        // Mostrar iframe
        document.body.innerHTML = `
            <div style="position: fixed; top: 10px; right: 20px; z-index: 9999;">
                <a href="${blobUrl}" download="${nombreArchivo}" style="padding: 10px 20px; color: white; background-color: #198754; text-decoration: none; border-radius: 5px; font-family: sans-serif; font-weight: bold;">
                    Descargar PDF
                </a>
            </div>
            <iframe src="${blobUrl}" style="width: 100vw; height: 100vh; border: none; margin: 0; padding: 0; position: absolute; top: 0; left: 0;"></iframe>
        `;
    };
    </script>
</body>
</html>
