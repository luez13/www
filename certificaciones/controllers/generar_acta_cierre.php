<?php
// generar_acta_cierre.php
require_once('../config/model.php');
require_once('../models/curso.php');

// Crear una instancia de la clase DB
$db = new DB();

// Crear una instancia de la clase Curso
$curso = new Curso($db);

// Definición de variables estáticas o placeholder (Debes obtener estos datos del modelo/BD)
$nombre_diplomado = "DIPLOMADO DOCENTE UNIVERSITARIO"; // [DATO ACTA]
$nombre_materia = "Estrategias de enseñanza y aprendizaje"; // [DATO ACTA]
$duracion = "1 Bimestre"; // [DATO ACTA]
$total_horas = "48 horas"; // [DATO ACTA]
$modalidad = "Online por la plataforma Moodle de la U.P.T.A.I.E.T."; // [DATO ACTA]
$docente_responsable = "Dra. Yolly Soto"; // [DATO ACTA]
$inscritos = 26; // [DATO ACTA]
$aprobados = 22; // [DATO ACTA]
$no_aprobaron = $inscritos - $aprobados; // Calculado

// Firmas
$firma_vicerrector = "Msc. Jhoan Sánchez"; // [DATO ACTA]
$cargo_vicerrector = "Vice-Rector Académico"; // [DATO ACTA]
$firma_coord = "Esp. Yoselin Espíndola";
$cargo_coord = "Coord. Formación Permanente";

// Variables de fecha y hora para el acta
$hora_cierre = "09:48 am";
// Simulación de la obtención de la fecha de cierre (Usaremos la fecha actual como fallback)
$dia_cierre = date("d");
$mes_cierre = "octubre"; // Hardcodeado para la solicitud, idealmente se obtiene de la BD
$anio_cierre = date("Y");


if (isset($_GET['id_curso'])) {
    $id_curso = $_GET['id_curso'];
// $datos = $curso->obtener_datos_acta_cierre($id_curso); // **DEBES CAMBIAR EL MÉTODO**

// Aquí deberías obtener todos los datos necesarios para el acta de cierre,
// como el nombre del diplomado, la materia, el docente, inscritos, etc.
// Por simplicidad y dado que no tengo tu modelo, uso los placeholders de arriba.

}
else {
// Si no se proporciona un ID, podrías lanzar un error o usar datos de prueba
}

// 🔒 RESTRICCIÓN: Mostrar el botón de cierre irreversible SOLO a roles autorizados
if (isset($_SESSION['id_rol']) && ($_SESSION['id_rol'] == 3 || $_SESSION['id_rol'] == 4)) {
    // Es administrador o autorizador: Mostramos el botón
    echo '<div class="text-center my-4">';
    echo '    <button class="btn btn-primary btn-lg shadow mt-2" onclick="cerrarDiplomado()"><i class="fas fa-lock me-2"></i>Cerrar Diplomado Oficialmente</button>';
    echo '</div>';
}
else {
    // Es promotor o facilitador: Mostramos un aviso de modo lectura
    echo '<div class="alert alert-info shadow-sm text-center my-4" role="alert">';
    echo '    <i class="fas fa-info-circle me-2"></i> <strong>Modo de visualización:</strong> Puedes consultar el acta, pero solo los administradores tienen permisos para realizar el cierre definitivo del diplomado en el sistema.';
    echo '</div>';
}

$piePagina = '../public/assets/img/piePagina.jpg';
$encabezado = '../public/assets/img/encabezado.jpg';
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.4.4/build/qrcode.min.js"></script>
<script>

    // Nota: El uso de '<?php echo $encabezado; ?>' en el JS es incorrecto para una ruta de archivo.
    // Usaremos el base64 de las variables PHP, como lo hiciste con piePagina.
    const piePagina = "data:image/jpeg;base64,<?php echo base64_encode(file_get_contents($piePagina)); ?>";
    const encabezado = "data:image/jpeg;base64,<?php echo base64_encode(file_get_contents($encabezado)); ?>";


document.addEventListener("DOMContentLoaded", function () {
    const generarActa = (datos) => {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('portrait', 'mm', 'letter');
        
        // Configuración de Fuente
        pdf.setFont("century", "normal");
        pdf.setFontSize(12);
        
        // --- TÍTULOS ---
        pdf.setFontSize(16);
        pdf.text("ACTA DE CIERRE DEL SEGUNDO BIMESTRE", 105, 35, { align: "center" });
        pdf.setFontSize(14);
        pdf.text("DEL DIPLOMADO DOCENTE UNIVERSITARIO", 105, 45, { align: "center" });
        
        // --- DATOS DEL ACTA (Delimitación del Área de Contenido) ---
        const startX = 30; // Margen Izquierdo para el contenido
        let startY = 65;
        const widthContent = 150; // Ancho máximo del texto
        const lineSpacing = 6; // Espaciado entre líneas

        const obtenerFechaActa = () => {
            return `${datos.dia_cierre} días del mes de ${datos.mes_cierre} del año ${datos.anio_cierre}`;
        };
        
        // **Contenido del Acta**
        // Nota: He usado `\t` para simular la indentación, pero jsPDF solo interpreta bien `\n`.
        // Usaremos el margen inicial (startX) para el texto.

        let contenido = `
En la ciudad de San Cristóbal, Estado Táchira, a los **${obtenerFechaActa()}**, siendo las **${datos.hora_cierre}**, se procede a realizar el cierre del segundo bimestre de la cohorte 2 correspondiente al **${datos.nombre_diplomado}**, impartiendo en la Universidad Politécnica Territorial Agroindustrial del Estado Táchira. Teniendo como unidad curricular.

• Nombre de la materia: **${datos.nombre_materia}**
• Duración: ${datos.duracion}
• Total de horas: ${datos.total_horas}
• Modalidad: ${datos.modalidad}
• Docente responsable: **${datos.docente_responsable}**

En cumplimiento con los lineamientos establecidos por la institución y con el objetivo de evaluar el desarrollo académico de los participantes, así como el cumplimiento de los objetivos planteados al inicio del diplomado, se procede a dar cierre formal a la materia antes mencionada.

Durante el transcurso de este bimestre, se llevaron a cabo diversas actividades académicas, que incluyeron clases teóricas, talleres prácticos y evaluación, las cuales permitieron a los participantes adquirir competencias y habilidades en el área de estudio. Por otra parte, se registró una inscripción de un total **${datos.inscritos}** de participantes, con la culminación de **${datos.aprobados}** participantes aprobados y **${datos.no_aprobaron}** que no aprobaron.

En cuanto a los resultados los estudiantes fueron evaluados mediante una combinación de trabajos prácticos, foros, talleres y participación continua en la plataforma. La calificación definitiva se ha registrado de acuerdo a los criterios establecidos en el programa del diplomado. Agradecemos a todos los participantes por su dedicación y esfuerzo, así como al cuerpo docente y administrativo de la Universidad Politécnica Territorial Agroindustrial del Estado Táchira por su apoyo y colaboración durante este proceso formativo.

Sin más asuntos que tratar, se levanta la presente acta, que será firmada por los presentes como constancia del cierre de la materia.
        `;

        // -------------------------------------------------------------
        // **Mecanismo de Manejo de Negritas (Adaptado de tu código)**
        // -------------------------------------------------------------
        const fontSize = 12;
        const regex = /(\*{2})+/g;
        const textoSinMarcas = contenido.replace(regex, '');
        let textoDividido = pdf.splitTextToSize(textoSinMarcas, widthContent);

        let charsMapLength = 0;
        let position = 0;
        let isBold = false;

        let textRows = textoDividido.map((row, i) => {
            const charsMap = row.split('');
            const chars = charsMap.map((char, j) => {
                // Ajustamos la posición para que coincida con el texto original (incluyendo las marcas **)
                let originalContent = contenido; // Usar el original para espiar
                let currentPos = charsMapLength + j + (i * 2); // Factor de ajuste para saltos de línea y marcas
                
                // Buscar el inicio de ** en la posición actual
                if (originalContent.substring(currentPos, currentPos + 2) === "**") {
                    isBold = !isBold;
                    // Saltar las dos marcas para que el char sea el contenido
                    currentPos += 2; 
                }
                
                let currentChar = originalContent.charAt(currentPos);
                
                // Buscar el fin de **
                if (originalContent.substring(currentPos + 1, currentPos + 3) === "**") {
                    // El próximo char (el final de la palabra/frase) será normal
                }

                return { char: currentChar, bold: isBold };
            });

            charsMapLength += charsMap.length; // Longitud del texto SIN marcas
            return { ...chars };
        });

        // NOTA: El mecanismo de negritas que usas es MUY frágil. 
        // Si falla, sugiero usar la función `doc.text()` con el estilo normal y luego reestablecer.
        
        // Pintar el contenido
        printCharacters(pdf, textRows, startY, startX, fontSize, lineSpacing);
        // -------------------------------------------------------------
        
        // --- FIRMAS ---
        const yFirmas = startY + (textoDividido.length * lineSpacing) + 20; // Posición después del texto

        // Columna Izquierda (Coordinación)
        pdf.setFont("century", "bold");
        pdf.text(datos.firma_coord, 55, yFirmas + 50, { align: "center" });
        pdf.setFont("century", "normal");
        pdf.text(datos.cargo_coord, 55, yFirmas + 55, { align: "center" });
        
        // Columna Centro (Vicerrectorado)
        pdf.setFont("century", "bold");
        pdf.text(datos.firma_vicerrector, 105, yFirmas + 50, { align: "center" });
        pdf.setFont("century", "normal");
        pdf.text(datos.cargo_vicerrector, 105, yFirmas + 55, { align: "center" });

        // Columna Derecha (Facilitador/Docente)
        pdf.setFont("century", "bold");
        pdf.text(datos.docente_responsable, 155, yFirmas + 50, { align: "center" });
        pdf.setFont("century", "normal");
        pdf.text("Facilitador", 155, yFirmas + 55, { align: "center" });


        // --- PIE DE PÁGINA Y ENCABEZADO ---
        const pageWidth = pdf.internal.pageSize.getWidth();
        const pageHeight = pdf.internal.pageSize.getHeight();
        const imgWidth = pageWidth - 20;
        const imgHeight = 20;
        const xImg = 10;
        const yImg = pageHeight - imgHeight - 1;

        const imgWidthEncabezado = pageWidth - 4;
        const imgHeightEncabezado = 20;
        const xEncabezado = 0;
        const yEncabezado = 0;

        // Agregar el encabezado y pie de página
        pdf.addImage(encabezado, 'JPEG', xEncabezado, yEncabezado, imgWidthEncabezado, imgHeightEncabezado);
        pdf.addImage(piePagina, 'JPEG', xImg, yImg, imgWidth, imgHeight);


        // --- FUNCIÓN DE IMPRESIÓN (SIN CAMBIOS) ---
        const printCharacters = (doc, textObject, startY, startX, fontSize, lineSpacing) => {
            const startXCached = startX;
            // doc.setFont("century", "normal"); // Restablecer la fuente antes de empezar a pintar

            Object.entries(textObject).map(([_key, row]) => {
                Object.entries(row).map(([key, value]) => {
                    // console.log(value.char, value.bold); // Para debug
                    doc.setFont("century", value.bold ? "bold" : "normal");
                    doc.text(value.char, startX, startY);
                    // Este cálculo de ancho es muy específico del script original
                    startX += doc.getStringUnitWidth(value.char) * fontSize * 0.38;
                });

                startX = startXCached;
                startY += lineSpacing;
            });
        };
        // -------------------------------------------------------------


        // Convertir y abrir el PDF
        const pdfOutput = pdf.output('blob');
        const blobUrl = URL.createObjectURL(pdfOutput);
        window.location.href = blobUrl;
    };

    // Generar el Acta con los datos PHP
    generarActa({
        nombre_diplomado: "<?php echo addslashes($nombre_diplomado); ?>",
        nombre_materia: "<?php echo addslashes($nombre_materia); ?>",
        duracion: "<?php echo $duracion; ?>",
        total_horas: "<?php echo $total_horas; ?>",
        modalidad: "<?php echo addslashes($modalidad); ?>",
        docente_responsable: "<?php echo addslashes($docente_responsable); ?>",
        inscritos: <?php echo $inscritos; ?>,
        aprobados: <?php echo $aprobados; ?>,
        no_aprobaron: <?php echo $no_aprobaron; ?>,
        firma_vicerrector: "<?php echo addslashes($firma_vicerrector); ?>",
        cargo_vicerrector: "<?php echo addslashes($cargo_vicerrector); ?>",
        firma_coord: "<?php echo addslashes($firma_coord); ?>",
        cargo_coord: "<?php echo addslashes($cargo_coord); ?>",
        dia_cierre: "<?php echo $dia_cierre; ?>",
        mes_cierre: "<?php echo $mes_cierre; ?>",
        anio_cierre: "<?php echo $anio_cierre; ?>",
        hora_cierre: "<?php echo $hora_cierre; ?>",
    });
});
</script>