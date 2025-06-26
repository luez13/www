<?php
require_once('../config/model.php');
require_once('../models/curso.php');

// Crear una instancia de la clase DB
$db = new DB();

// Crear una instancia de la clase Curso
$curso = new Curso($db);

if (isset($_GET['id_curso'])) {
    $id_curso = $_GET['id_curso'];
    $datos = $curso->obtener_datos_constancia_por_curso($id_curso);

    $nombre_curso = $datos[0]['nombre_curso'];
    $nombre_promotor = $datos[0]['nombre_promotor'];
    $horas_cronologicas = $datos[0]['horas_cronologicas'];
    $cedula = $datos[0]['cedula'];
// Obtener los módulos del curso
$modulos = [];
foreach ($datos as $modulo) {
    if (!empty($modulo['nombre_modulo'])) {
        // Limpiar el nombre del módulo eliminando caracteres no alfanuméricos al inicio y al final
        $nombre_limpio = trim($modulo['nombre_modulo'], " \t\n\r\0\x0B!@#$%^&*()-_=+[]{};:'\",.<>?/\\|");
        $modulos[] = addslashes($nombre_limpio);
    }
}

// Convertir los módulos en una lista separada por comas
$nombre_modulo = implode(", ", $modulos);

$nombre_curso_capitalizado = ucwords(strtolower($nombre_curso));

if (!isset($datos['fecha_finalizacion']) || empty($datos['fecha_finalizacion'])) {
    $fecha_finalizacion = "Fecha no disponible";
} else {
    $fecha_obj = DateTime::createFromFormat('Y-m-d H:i:s', $datos['fecha_finalizacion']);

    if ($fecha_obj) {
        $fecha_finalizacion = $fecha_obj->format("d \\d\\e F \\d\\e Y");

        // Traducción de los meses
        $meses = [
            "January" => "enero",
            "February" => "febrero",
            "March" => "marzo",
            "April" => "abril",
            "May" => "mayo",
            "June" => "junio",
            "July" => "julio",
            "August" => "agosto",
            "September" => "septiembre",
            "October" => "octubre",
            "November" => "noviembre",
            "December" => "diciembre",
        ];

        $fecha_finalizacion = str_replace(array_keys($meses), array_values($meses), $fecha_finalizacion);
    } else {
        $fecha_finalizacion = "Fecha inválida";
    }
}

}

$piePagina ='../public/assets/img/piePagina.jpg';
$encabezado ='../public/assets/img/encabezado.jpg';
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.4.4/build/qrcode.min.js"></script>
<script>

    const piePagina = "data:image/jpeg;base64,<?php echo base64_encode(file_get_contents($piePagina)); ?>";
    const encabezado = "data:image/jpeg;base64,<?php echo base64_encode(file_get_contents($encabezado)); ?>";
document.addEventListener("DOMContentLoaded", function () {
    const generarConstancia = (datos) => {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('portrait', 'mm', 'letter');

        pdf.setFont("century", "normal");
        pdf.setFontSize(12);

        // Título centrado
        pdf.setFontSize(13);
        pdf.text("CONSTANCIA", 105, 45, { align: "center" });

        const obtenerFechaActual = () => {
            const fecha = new Date();
            const meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio",
                           "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
            return `${fecha.getDate()} de ${meses[fecha.getMonth()]} de ${fecha.getFullYear()}`;
        };

        const pageWidth = pdf.internal.pageSize.getWidth(); // Ancho total de la página
        const pageHeight = pdf.internal.pageSize.getHeight(); // Altura total de la página

        const imgWidth = pageWidth - 20; // Ancho ajustado con márgenes
        const imgHeight = 20; // Altura fija para el pie de página
        const x = 10; // Márgen izquierdo
        const y = pageHeight - imgHeight - 1; // Colocar la imagen en la parte inferior

        const imgWidthEncabezado = pageWidth - 4; // Ajustar el ancho del encabezado con márgenes laterales
        const imgHeightEncabezado = 20; // Altura fija para el encabezado
        const xEncabezado = 0; // Márgen izquierdo
        const yEncabezado = 0; // Colocar la imagen en la parte superior

        // Agregar el encabezado al inicio de la página
        pdf.addImage('<?php echo $encabezado; ?>', 'JPEG', xEncabezado, yEncabezado, imgWidthEncabezado, imgHeightEncabezado);

        const fechaInicio = obtenerFechaActual();

        // **Marcar el nombre del curso con "**" para que se detecte como negrita**
        let contenido = `
            Por medio de la presente, hacemos constar que el ciudadano **${datos.nombre_promotor}**, titular de la cédula de identidad N° V-**${datos.cedula}**, participó satisfactoriamente como facilitador en la ponencia titulada **${datos.nombre_curso}**, el cual fue organizado por la **Coordinación de Formación Permanente** de la Universidad Politécnica Territorial Agroindustrial del Estado Táchira.
            
            La ponencia se realizó el día ${fechaInicio} la misma se desarrolló en la sede de la Universidad Politécnica Territorial Agroindustrial del Estado Táchira. Durante su desarrollo, el facilitador impartió conocimientos y habilidades relevantes de ${datos.nombre_modulo}.
            
            Agradecemos su participación y compromiso con la formación continua de nuestros participantes.
        `;

        const startX = 30;
        let startY = 50;
        const fontSize = 13;
        const lineSpacing = 7;

        const regex = /(\*{2})+/g;
        const textoSinMarcas = contenido.replace(regex, '');
        let textoDividido = pdf.splitTextToSize(textoSinMarcas, 150);

        let charsMapLength = 0;
        let position = 0;
        let isBold = false;

        let textRows = textoDividido.map((row, i) => {
            const charsMap = row.split('');
            const chars = charsMap.map((char, j) => {
                position = charsMapLength + j + i;
                let currentChar = contenido.charAt(position);

                if (currentChar === "*") {
                    const spyNextChar = contenido.charAt(position + 1);
                    if (spyNextChar === "*") {
                        isBold = !isBold;
                        currentChar = contenido.charAt(position + 2);

                        let removeMarks = contenido.split('');
                        removeMarks.splice(position, 2);
                        contenido = removeMarks.join('');
                    }
                }

                return { char: currentChar, bold: isBold };
            });

            charsMapLength += charsMap.length;
            return { ...chars };
        });

        printCharacters(pdf, textRows, startY, startX, fontSize, lineSpacing);

        const atent = `Atentamente,\n\n\n\n\nIng. Espindola Yoselin\nCoordinación de Formación Permanente`;
        pdf.text(atent, 100, 180, { align: "center" });

        const correo = `Correo: techo.uptai@gmail.com\nTeléfono: 0426-5108012\nUniversidad Politécnica Territorial Agroindustrial del Estado Táchira`;
        pdf.text(correo, 20, pdf.internal.pageSize.getHeight() - 45, { maxWidth: 150, align: "left" });

        // Agregar la imagen centrada
        pdf.addImage('<?php echo $piePagina; ?>', 'JPEG', x, y, imgWidth, imgHeight);

        // Convertir y abrir el PDF
        const pdfOutput = pdf.output('blob');
        const blobUrl = URL.createObjectURL(pdfOutput);
        window.location.href = blobUrl;
    };

    const printCharacters = (doc, textObject, startY, startX, fontSize, lineSpacing) => {
        const startXCached = startX;
        textObject.map(row => {
            Object.entries(row).map(([key, value]) => {
                doc.setFont("century", value.bold ? "bold" : "normal");
                doc.text(value.char, startX, startY);
                startX += doc.getStringUnitWidth(value.char) * fontSize * 0.38;
            });

            startX = startXCached;
            startY += lineSpacing;
        });
    };

    // Generar la constancia
    generarConstancia({
        nombre_promotor: "<?php echo addslashes($nombre_promotor); ?>",
        cedula: "<?php echo $cedula; ?>",
        nombre_curso: "<?php echo addslashes($nombre_curso_capitalizado); ?>",
        nombre_modulo: "<?php echo addslashes($nombre_modulo); ?>"
    });
});
</script>