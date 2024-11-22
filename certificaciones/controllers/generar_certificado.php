<?php
require_once('../config/model.php');
require_once('../models/curso.php');

// Crear una instancia de la clase DB
$db = new DB();

// Crear una instancia de la clase Curso
$curso = new Curso($db);

if (isset($_GET['valor_unico'])) {
    $valor_unico = $_GET['valor_unico'];
    $certificadoUrl = "http://{$_SERVER['HTTP_HOST']}/certificaciones/controllers/generar_certificado.php?valor_unico={$valor_unico}";
    // Mostrar los datos de la certificación basados en el valor_unico
    $datos = $curso->obtener_datos_certificacion($valor_unico);
    
    // Asignar los datos obtenidos a variables
    $nombreEstudiante = $datos['nombre_estudiante'];
    $apellido_estudiante = $datos['apellido_estudiante'];
    $cedula = $datos['cedula'];
    $paso = $datos['completado'] ? "aprobado" : "no aprobado";
    $fecha = date('d/m/Y', strtotime($datos['fecha_inscripcion']));
    $tomo = $datos['tomo'];
    $folio = $datos['folio'];
    $nota = $datos['nota'];
    $promotor_id = $datos['promotor'];
    $tipo_curso = $datos['tipo_curso'];
    $nombre_curso = $datos['nombre_curso'];
    
    // Obtener el nombre del promotor y la firma digital
    $stmt = $db->prepare("SELECT nombre, firma_digital FROM cursos.usuarios WHERE id = :id");
    $stmt->bindParam(':id', $promotor_id, PDO::PARAM_INT);
    $stmt->execute();
    $promotor_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $promotor = $promotor_data['nombre'];
    $firma_digital = $promotor_data['firma_digital'];
    
    // Obtener los módulos del curso
    $stmt = $db->prepare("SELECT * FROM cursos.modulos WHERE id_curso = :id_curso ORDER BY numero");
    $stmt->bindParam(':id_curso', $datos['id_curso'], PDO::PARAM_INT);
    $stmt->execute();
    $modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular la duración total del curso
    if ($tipo_curso === "masterclass") {
        $duracionTotal = 4;
    } else {
        $inicio = new DateTime($datos['horario_inicio']);
        $fin = new DateTime($datos['horario_fin']);
        $duracionClase = $fin->diff($inicio)->h + ($fin->diff($inicio)->i / 60); // horas + minutos convertidos a horas
        $diasClaseArray = explode(',', trim($datos['dias_clase'], '{}'));
        $numeroDiasPorSemana = count($diasClaseArray);
        $numeroDeSemanas = $datos['tiempo_asignado'];
        $duracionTotal = $duracionClase * $numeroDiasPorSemana * $numeroDeSemanas;
    }
    
    // Definir el artículo basado en el tipo de curso
    $articulo_tipo_curso = ($tipo_curso === "charla" || $tipo_curso === "masterclass") ? "la" : "el";
}

// Rutas a las imágenes
$imagePath = '../public/assets/img/marca_agua.png';
$bannerPath = '../public/assets/img/banner_certificado.jpg';
$footerPath = '../public/assets/img/footer.jpg';
?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.4.4/build/qrcode.min.js"></script>
    <!-- Incluir los archivos de fuentes convertidas -->
    <script src="../public/assets/vendor/3309-font-normal.js "></script>
    <script src="../public/assets/vendor/cambria-normal.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const { jsPDF } = window.jspdf;
// Registrar fuentes personalizadas
jsPDF.API.events.push(['addFonts', function() {
    try {
        this.addFileToVFS('3309-font.ttf', font3309Normal);
        this.addFont('3309-font.ttf', 'Font3309', 'normal');
        this.addFileToVFS('Cambria.ttf', cambriaNormal);
        this.addFont('Cambria.ttf', 'Cambria', 'normal');
    } catch (error) {
        fontLoadSuccess = false;
        fontLoadError = error.message;
        console.error("Error loading fonts: ", error);
    }
}]);
            // Crear un nuevo documento PDF
            const pdf = new jsPDF('landscape', 'mm', 'a4');
            // Usar la fuente Cambria para el texto general
            pdf.setFont('Cambria', 'normal');
            // Agregar imagen del banner como encabezado
            pdf.addImage('<?php echo $bannerPath; ?>', 'JPEG', 10, 5, pdf.internal.pageSize.width - 20, 0);
            // Agregar imagen de marca de agua en el centro
            const watermarkWidth = pdf.internal.pageSize.width / 2;
            const watermarkHeight = pdf.internal.pageSize.height / 2;
            pdf.addImage('<?php echo $imagePath; ?>', 'PNG', (pdf.internal.pageSize.width - watermarkWidth) / 2, (pdf.internal.pageSize.height - watermarkHeight) / 2, watermarkWidth, watermarkHeight);
            // Agregar texto al documento con los datos correspondientes
            pdf.setFontSize(20); // Tamaño 20
            pdf.text('REPÚBLICA BOLIVARIANA DE VENEZUELA', pdf.internal.pageSize.width / 2, 40, { align: 'center' });
            pdf.text('MINISTERIO DEL PODER POPULAR PARA LA EDUCACIÓN UNIVERSITARIA', pdf.internal.pageSize.width / 2, 50, { align: 'center' });
            pdf.text('UNIVERSIDAD POLITÉCNICA TERRITORIAL AGROINDUSTRIAL DEL ESTADO TÁCHIRA', pdf.internal.pageSize.width / 2, 60, { align: 'center' });
            pdf.setFontSize(18); // Tamaño 18
            pdf.text('Otorga el presente certificado al ciudadano (a):', pdf.internal.pageSize.width / 2, 80, { align: 'center' });
            
            // Usar la fuente Font3309 para el nombre del estudiante en cursiva y rojo
            pdf.setFont('Font3309', 'italic');
            pdf.setFontSize(50);
            pdf.setTextColor(255, 0, 0); // Color rojo
            pdf.text('<?php echo $nombreEstudiante .' ' . $apellido_estudiante; ?>', pdf.internal.pageSize.width / 2, 95, { align: 'center' });

            // Regresar a la fuente Cambria y restablecer el color
            pdf.setFont('Cambria', 'normal');
            pdf.setFontSize(16);
            pdf.setTextColor(0, 0, 0); // Color negro
            pdf.text('C.I. V- <?php echo $cedula; ?>', pdf.internal.pageSize.width / 2, 110, { align: 'center' });
            pdf.text('Por haber <?php echo $paso; ?> en <?php echo $articulo_tipo_curso; ?> <?php echo $tipo_curso; ?> de <?php echo $nombre_curso; ?>', pdf.internal.pageSize.width / 2, 125, { align: 'center' });
            pdf.text('Certificación expedida en la Ciudad de San Cristóbal, <?php echo $fecha; ?>', pdf.internal.pageSize.width / 2, 140, { align: 'center' });

            // Agregar el nombre del promotor al lado derecho arriba del footer con poco interlineado
            const marginRight = pdf.internal.pageSize.width - 20;

            // Ajustar tamaño de las imágenes
            const imageHeight = 65;
            const imageWidth = 67;

            // Calcular la posición para estar un poco arriba del pie de página
            const footerPositionY = pdf.internal.pageSize.height - 25;
            const offsetY = footerPositionY - 45; // Ajuste para estar solo un poco por encima del pie de página

            // Imagen encima de "Ing. Espindola Yoselin", más abajo y a la izquierda
            pdf.addImage('../public/assets/img/coord.png', 'PNG', marginRight - imageWidth / 2 - 22, offsetY - imageHeight + 60, imageWidth, imageHeight); // Ajustar según sea necesario

            // Texto del promotor
            pdf.text('Ing. Espindola Yoselin', marginRight, offsetY - imageHeight + 105, { align: 'right' });
            pdf.text('Coord. Formación Permanente', marginRight, offsetY - imageHeight + 100, { align: 'right' });

            // Imagen a la derecha de "Ing. Espindola Yoselin"
            pdf.addImage('../public/assets/img/sello.png', 'PNG', marginRight - 140, offsetY - imageHeight + 60, imageWidth, imageHeight); // Ajustar según sea necesario

            // Agregar imagen del pie de página
            pdf.addImage('<?php echo $footerPath; ?>', 'JPEG', 10, footerPositionY, pdf.internal.pageSize.width - 20, 0);

            // Agregar segunda página
            pdf.addPage();
            pdf.setFont('Arial', 'B', 16);

            // Generar el código QR con la URL del certificado
            const certificadoUrl = "<?php echo $certificadoUrl; ?>";
            QRCode.toDataURL(certificadoUrl, { width: 150, margin: 1 }, function(err, url) {
                if (err) {
                    console.error(err);
                    return;
                }

                // Agregar el código QR en la parte superior derecha de la segunda página
                pdf.addImage(url, 'PNG', pdf.internal.pageSize.width - 60, 10, 50, 50); // Ajusta las coordenadas según sea necesario
            });
            // Agregar imagen de marca de agua en el centro de la segunda página
            pdf.addImage('<?php echo $imagePath; ?>', 'PNG', (pdf.internal.pageSize.width - watermarkWidth) / 2, (pdf.internal.pageSize.height - watermarkHeight) / 2, watermarkWidth, watermarkHeight);

            // Agregar el título "CONTENIDO:" centrado y grande
            pdf.setFontSize(20);
            pdf.setFont('Cambria', 'normal');
            pdf.text('CONTENIDO:', pdf.internal.pageSize.width / 2, 30, { align: 'center' });

            // Lista de módulos dentro de un "cuadrado" centrado
            pdf.setFontSize(16);
            const leftMargin = 40; // Margen izquierdo del "cuadrado"

            // Agregar los módulos al PDF
            <?php foreach ($modulos as $index => $modulo): ?>
            {
                let moduloTexto = <?php echo json_encode(($index + 1) . ". " . $modulo["nombre_modulo"]); ?>;
                pdf.text(moduloTexto, leftMargin, 50 + <?php echo $index * 10; ?>);
            }
            <?php endforeach; ?>

            // Agregar el texto de registro y calificación con poco interlineado
            pdf.setFontSize(16);
            const notaTexto = <?php echo is_null($nota) || $nota == 0 ? '"Presentando una calificación final de aprobado"' : '"Presentando una calificación final, ' . $nota . ' de una nota máxima (20)."' ?>;
            pdf.text(notaTexto, 10, 200);
            pdf.text('Registrado en formación permanente tomo <?php echo $tomo; ?> y folio <?php echo $folio; ?>.', 10, 190);
            pdf.text('El programa tuvo una duración de <?php echo $duracionTotal; ?> horas cronológicas.', 10, 195);
            const marginRight2 = pdf.internal.pageSize.width - 20;

            // Agregar la firma digital del promotor si existe
            if ('<?php echo $firma_digital; ?>') {
                const img = new Image();
                img.src = '<?php echo $firma_digital; ?>';
                img.onload = function () {
                    pdf.addImage(img, 'PNG', marginRight2 - 40, 130, 30, 30); // Ajusta las coordenadas y el tamaño según sea necesario
                    pdf.text('<?php echo $promotor; ?>', marginRight2, 170, { align: 'right' });
                    pdf.text('Facilitador', marginRight2, 175, { align: 'right' });

                    // Generar el PDF y abrir en una nueva pestaña
                    const pdfOutput = pdf.output('blob');
                    const blobUrl = URL.createObjectURL(pdfOutput);
                    window.location.href = blobUrl; // Navega directamente a la URL del PDF
                };
            } else {
                pdf.text('<?php echo $promotor; ?>', marginRight2, 150, { align: 'right' });
                pdf.text('Facilitador', marginRight2, 155, { align: 'right' });

                // Generar el PDF y abrir en una nueva pestaña
                const pdfOutput = pdf.output('blob');
                const blobUrl = URL.createObjectURL(pdfOutput);
                window.location.href = blobUrl; // Navega directamente a la URL del PDF
            }
        });
    </script>