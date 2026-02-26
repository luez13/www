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

    // --- INICIO: Lógica para obtener el Firmante Dinámico ---
    $nombre_coordinador = "Coordinación de Formación Permanente"; // Valor por defecto si falla la BD

    try {
        // 1. Obtener el ID del cargo configurado por defecto
        $stmtConfig = $db->prepare("SELECT valor_config FROM cursos.config_sistema WHERE clave_config = 'ID_CARGO_COORD_FP_POR_DEFECTO'");
        $stmtConfig->execute();
        $id_cargo_coord = $stmtConfig->fetchColumn();

        if ($id_cargo_coord) {
            // 2. Obtener Nombre y Título de la tabla cargos usando ese ID
            // Nota: Asumo que la tabla cargos tiene columnas 'id_cargo' (o 'id'), 'nombre', 'apellido', 'titulo'
            // Ajusta 'id_cargo' si tu llave primaria se llama diferente en la vista de cargos
            $stmtCargo = $db->prepare("SELECT nombre, apellido, titulo FROM cursos.cargos WHERE id_cargo = :id");
            $stmtCargo->execute(['id' => $id_cargo_coord]);
            $coordData = $stmtCargo->fetch();

            if ($coordData) {
                $titulo = !empty($coordData['titulo']) ? $coordData['titulo'] : '';
                $nombre_coordinador = trim("$titulo " . $coordData['nombre'] . " " . $coordData['apellido']);
            }
        }
    } catch (PDOException $e) {
        // En caso de error, mantenemos un fallback o logueamos
        error_log("Error obteniendo firmante constancia: " . $e->getMessage());
    }
    // --- FIN: Lógica Firmante Dinámico ---

    $nombre_curso = $datos[0]['nombre_curso'];
    $nombre_promotor = $datos[0]['nombre_promotor'];
    $horas_cronologicas = $datos[0]['horas_cronologicas'];
    $cedula = $datos[0]['cedula'];
    
    // Obtener los módulos del curso
    $modulos = [];
    foreach ($datos as $modulo) {
        if (!empty($modulo['nombre_modulo'])) {
            // Limpiar el nombre del módulo
            $nombre_limpio = trim($modulo['nombre_modulo'], " \t\n\r\0\x0B!@#$%^&*()-_=+[]{};:'\",.<>?/\\|");
            $modulos[] = addslashes($nombre_limpio);
        }
    }

    // Convertir los módulos en una lista separada por comas
    $nombre_modulo = implode(", ", $modulos);

    $nombre_curso_capitalizado = ucwords(strtolower($nombre_curso));
    
    // ... (Tu lógica de fechas se mantiene igual) ...
    // Solo para prevenir errores si $datos['fecha_finalizacion'] no existe en constancia
    // ya que la constancia suele ser "cursando actualmente", quizás no necesites fecha finalización aquí.
}

$piePagina ='../public/assets/img/piePagina.jpg';
$encabezado ='../public/assets/img/encabezado.jpg';
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script>

    const piePagina = "data:image/jpeg;base64,<?php echo base64_encode(file_get_contents($piePagina)); ?>";
    const encabezado = "data:image/jpeg;base64,<?php echo base64_encode(file_get_contents($encabezado)); ?>";
    
    document.addEventListener("DOMContentLoaded", function () {
    const generarConstancia = (datos) => {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('portrait', 'mm', 'letter');

        pdf.setFont("Helvetica", "normal");
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

        const pageWidth = pdf.internal.pageSize.getWidth();
        const pageHeight = pdf.internal.pageSize.getHeight();

        const imgWidth = pageWidth - 20;
        const imgHeight = 20;
        const x = 10;
        const y = pageHeight - imgHeight - 1;

        const imgWidthEncabezado = pageWidth - 4;
        const imgHeightEncabezado = 20;
        const xEncabezado = 0;
        const yEncabezado = 0;

        pdf.addImage('<?php echo $encabezado; ?>', 'JPEG', xEncabezado, yEncabezado, imgWidthEncabezado, imgHeightEncabezado);

        const fechaInicio = obtenerFechaActual();

        // Texto del cuerpo
        let contenido = `
            Por medio de la presente, hacemos constar que el ciudadano **${datos.nombre_promotor}**, titular de la cédula de identidad N° V-**${datos.cedula}**, se encuentra actualmente participando como estudiante en la unidad curricular / curso titulado **${datos.nombre_curso}**, el cual es organizado por la **Coordinación de Formación Permanente** de la Universidad Politécnica Territorial Agroindustrial del Estado Táchira.
            
            La presente constancia se expide en San Cristóbal, a los ${fechaInicio}.
            
            Agradecemos su participación y compromiso con la formación continua.
        `;
        // Nota: He ajustado ligeramente el texto para que tenga sentido como "Constancia de Estudio" (Estudiante Activo)
        // en lugar de "participó como facilitador" (Pasado/Ponente), ya que dijiste que era para inscritos.

        const startX = 30;
        let startY = 50;
        const fontSize = 13;
        const lineSpacing = 6;

        const regex = /(\*{2})+/g;
        const textoSinMarcas = contenido.replace(regex, '');
        let textoDividido = pdf.splitTextToSize(textoSinMarcas, 150);

        // --- Lógica de renderizado con negritas (Mantenida igual) ---
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

        // --- FIRMA DINÁMICA ---
        // Aquí usamos la variable que PHP nos inyectó
        const nombreFirmante = "<?php echo $nombre_coordinador; ?>";
        const cargoFirmante = "Coordinación de Formación Permanente";

        const atent = `Atentamente,\n\n\n\n\n${nombreFirmante}\n${cargoFirmante}`;
        pdf.text(atent, 105, 180, { align: "center" });

        const correo = `Correo: techo.uptai@gmail.com\nTeléfono: 0426-5108012\nUniversidad Politécnica Territorial Agroindustrial del Estado Táchira`;
        pdf.text(correo, 20, pdf.internal.pageSize.getHeight() - 45, { maxWidth: 150, align: "left" });

        pdf.addImage('<?php echo $piePagina; ?>', 'JPEG', x, y, imgWidth, imgHeight);

        const pdfOutput = pdf.output('blob');
        const blobUrl = URL.createObjectURL(pdfOutput);
        window.location.href = blobUrl;
    };

    const printCharacters = (doc, textObject, startY, startX, fontSize, lineSpacing) => {
        const startXCached = startX;
        textObject.map(row => {
            Object.entries(row).map(([key, value]) => {
                if(value.char) { // Evitar undefined
                    doc.setFont("Helvetica", value.bold ? "bold" : "normal");
                    doc.text(value.char, startX, startY);
                    startX += doc.getStringUnitWidth(value.char) * fontSize * 0.3528; // Factor de corrección para Helvetica
                }
            });
            startX = startXCached;
            startY += lineSpacing;
        });
    };

    // Generar la constancia con datos PHP
    generarConstancia({
        nombre_promotor: "<?php echo addslashes($nombre_promotor); ?>", // OJO: Aquí 'nombre_promotor' es el estudiante según tu SQL anterior, verifica eso.
        cedula: "<?php echo $cedula; ?>",
        nombre_curso: "<?php echo addslashes($nombre_curso_capitalizado); ?>",
        nombre_modulo: "<?php echo addslashes($nombre_modulo); ?>"
    });
});
</script>