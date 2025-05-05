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
        $modulos[] = addslashes($modulo['nombre_modulo']); // Evitar problemas con comillas
    }
}

// Convertir los módulos en una lista separada por comas
$nombre_modulo = implode(", ", $modulos);

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

$piePagina ='..\public\assets\img\piePagina.jpg';
$encabezado ='..\public\assets\img\encabezado.jpg';
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>

        const nombre_curso = "<?php echo addslashes($nombre_curso); ?>";
        const piePagina = "data:image/jpeg;base64,<?php echo base64_encode(file_get_contents($piePagina)); ?>";
        const encabezado = "data:image/jpeg;base64,<?php echo base64_encode(file_get_contents($encabezado)); ?>";

document.addEventListener("DOMContentLoaded", function () {
    const generarConstancia = (datos) => {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('a4');

        // Configuración de márgenes y fuente
        pdf.setFont("helvetica", "normal");
        pdf.setFontSize(12);

        // Título centrado
        pdf.setFont("helvetica", "normal");
        pdf.setFontSize(13);
        pdf.text("CONSTANCIA", 105, 50, { align: "center" });

        // Obtener la fecha actual en formato "San Cristóbal, DD de MM de AAAA"
        const obtenerFechaActual = () => {
            const fecha = new Date();
            const meses = [
                "enero", "febrero", "marzo", "abril", "mayo", "junio",
                "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"
            ];
            const dia = fecha.getDate();
            const mes = meses[fecha.getMonth()];
            const anio = fecha.getFullYear();
            return `San Cristóbal, ${dia} de ${mes} de ${anio}`;
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

        // Incorporar la función al texto de fecha
        const textoFecha = obtenerFechaActual();
        const textWidth = pdf.getTextWidth(textoFecha);
        const xPosition = pageWidth - textWidth - 20;
        pdf.text(textoFecha, xPosition, 70);

        
        // Agregar el encabezado al inicio de la página
        pdf.addImage(encabezado, 'JPEG', xEncabezado, yEncabezado, imgWidthEncabezado, imgHeightEncabezado);
        
        // A quien corresponda
        pdf.setFont("helvetica", "normal");
        pdf.text("A quien corresponda:", 20, 80);

        // Obtener la fecha actual desde la función en JavaScript
        const fechaInicio = obtenerFechaActual();

        // Cuerpo del texto
        const contenido = `
            Por medio de la presente, hacemos constar que <?php echo $nombre_promotor; ?>, con cédula de identidad <?php echo $cedula; ?>, ha participado satisfactoriamente como facilitador en el curso titulado "${nombre_curso}", el cual fue organizado por la Coordinación de Formación Permanente de la Universidad Politécnica Territorial Agroindustrial del Estado Táchira.
            
            El curso se llevó a cabo en la fecha ${fechaInicio} al <?php echo $fecha_finalizacion; ?>, teniendo una duración de <?php echo $horas_cronologicas; ?> horas, y tuvo lugar en la sede UPTAIET. Durante el mismo, el facilitador impartió conocimientos y habilidades relevantes sobre <?php echo $nombre_modulo; ?>.
            
            Agradecemos su participación y compromiso con la formación continua de nuestros participantes.`;

        pdf.text(contenido, 20, 90, { maxWidth: 170, align: "justify" });

        const atent = `
        Atentamente,







        _____________________________
        ing. Espindola Yoselin
        coordinación de Formación Permanente
        Universidad Politécnica Territorial Agroindustrial del Estado Táchira
        0426-5108012
        techo.uptai@gmail.com
        `;

        pdf.text(atent, 100, 180, { align: "center" });

            // Agregar la imagen centrada
            pdf.addImage(piePagina, 'JPEG', x, y, imgWidth, imgHeight);

        // Footer
        pdf.setFontSize(10);
        pdf.setFont("helvetica", "italic");
        pdf.text("Certificación emitida por [Nombre del Promotor]", 100, 310, { align: "center" });

        // Convertir el PDF a un blob
        const pdfOutput = pdf.output('blob');

        // Crear una URL temporal para el blob
        const blobUrl = URL.createObjectURL(pdfOutput);

        // Abrir el PDF en la misma pestaña
        window.location.href = blobUrl;
    };

    // Generar la constancia
    generarConstancia();
});
</script>