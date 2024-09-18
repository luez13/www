<?php
require_once('../config/model.php');
require_once('../models/curso.php');

// Crear una instancia de la clase DB
$db = new DB();

// Crear una instancia de la clase Curso
$curso = new Curso($db);

// Obtener el valor único del curso de la URL
$valor_unico = $_GET['valor_unico'];

// Usar el método de la clase Curso para obtener el contenido del curso usando el valor único
$curso_contenido = $curso->obtener_curso_por_valor_unico($valor_unico);

// Obtener el nombre del estudiante desde la sesión
$nombreEstudiante = $_SESSION['nombre'];

// Ruta a tu imagen
$imagePath = '../public/assets/img/IUT.jpg';

// Codificar la imagen a base64
$imageContent = file_get_contents($imagePath);
$base64Image = base64_encode($imageContent);

// Devolver los datos en formato JSON
echo json_encode([
    'nombre_curso' => $curso_contenido['nombre_curso'],
    'nombre_estudiante' => $nombreEstudiante,
    'base64Image' => $base64Image
]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar Certificado</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body>
    <script>
        function drawTextWithBorder(doc, text, x, y) {
            var offset = 0.02; // Ajustar el grosor del borde
            doc.setTextColor(255, 255, 255); // Color del borde (blanco)
            for(var i = -offset; i <= offset; i += offset) {
                for(var j = -offset; j <= offset; j += offset) {
                    doc.text(text, x + i, y + j, { align: 'center' }); // Centra el texto
                }
            }
            doc.setTextColor(0, 0, 0); // Color del texto (negro)
            doc.text(text, x, y, { align: 'center' }); // Centra el texto
        }

        function generarCertificado() {
            const { jsPDF } = window.jspdf;

            // Crear una nueva instancia de jsPDF en orientación horizontal con tamaño carta
            var doc = new jsPDF({
                orientation: 'landscape',
                unit: 'in',
                format: 'letter'
            });

            // Agregar imagen de fondo
            var imgData = 'data:image/jpeg;base64,<?php echo $base64Image; ?>';
            doc.addImage(imgData, 'JPEG', 0, 0, doc.internal.pageSize.getWidth(), doc.internal.pageSize.getHeight());

            // Agregar texto al documento con los datos correspondientes
            doc.setFontSize(20);
            doc.setFont('helvetica');
            drawTextWithBorder(doc, 'Certificado de Formación', doc.internal.pageSize.getWidth() / 2, 2);

            // Agregar el nombre del curso y el estudiante
            doc.setFontSize(16);
            drawTextWithBorder(doc, 'Nombre del Curso: ' + '<?php echo $curso_contenido['nombre_curso']; ?>', doc.internal.pageSize.getWidth() / 2, 4);
            drawTextWithBorder(doc, 'Nombre del Estudiante: ' + '<?php echo $nombreEstudiante; ?>', doc.internal.pageSize.getWidth() / 2, 5);

            // Agregar el resto de los datos necesarios
            doc.setFontSize(12);
            drawTextWithBorder(doc, 'Certificamos que el estudiante ha completado el curso.', doc.internal.pageSize.getWidth() / 2, 6);

            // Agregar información adicional según las especificaciones del certificado
            drawTextWithBorder(doc, 'Republica Bolivariana de Venezuela', doc.internal.pageSize.getWidth() / 2, 7);
            drawTextWithBorder(doc, 'Ministerio del Poder Popular para la Educacion Universitaria', doc.internal.pageSize.getWidth() / 2, 7.5);
            drawTextWithBorder(doc, 'Universidad Politecnica Territorial Agroindustrial del Estado Tachira', doc.internal.pageSize.getWidth() / 2, 8);

            // Agregar el nombre y fecha de expedición
            drawTextWithBorder(doc, '(nombre del promotor)', doc.internal.pageSize.getWidth() / 2, 9);
            drawTextWithBorder(doc, 'Certificado Expediado en la Ciudad de San Cristobal, a las ' + new Date().toLocaleString(), doc.internal.pageSize.getWidth() / 2, 9.5);

            // Abrir el PDF en una nueva pestaña
            window.open(doc.output('bloburl'), '_blank');
        }

        document.addEventListener('DOMContentLoaded', (event) => {
            generarCertificado();
        });
    </script>
</body>
</html>