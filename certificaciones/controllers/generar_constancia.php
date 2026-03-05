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
        // 1. Obtener los IDs de los cargos y datos de contacto configurados por defecto
        $stmtConfig = $db->prepare("SELECT clave_config, valor_config FROM cursos.config_sistema WHERE clave_config IN ('ID_CARGO_COORD_FP_POR_DEFECTO', 'ID_CARGO_VICERRECTORADO_POR_DEFECTO', 'CORREO_CONTACTO_POR_DEFECTO', 'TELEFONO_CONTACTO_POR_DEFECTO')");
        $stmtConfig->execute();
        $configs = $stmtConfig->fetchAll(PDO::FETCH_KEY_PAIR);

        $id_cargo_coord = isset($configs['ID_CARGO_COORD_FP_POR_DEFECTO']) ? $configs['ID_CARGO_COORD_FP_POR_DEFECTO'] : null;
        $id_cargo_vicerrectorado = isset($configs['ID_CARGO_VICERRECTORADO_POR_DEFECTO']) ? $configs['ID_CARGO_VICERRECTORADO_POR_DEFECTO'] : null;

        $correo_contacto = isset($configs['CORREO_CONTACTO_POR_DEFECTO']) ? $configs['CORREO_CONTACTO_POR_DEFECTO'] : 'techo.uptai@gmail.com';
        $telefono_contacto = isset($configs['TELEFONO_CONTACTO_POR_DEFECTO']) ? $configs['TELEFONO_CONTACTO_POR_DEFECTO'] : '0426-5108012';

        $stmtCargo = $db->prepare("SELECT nombre, apellido, titulo FROM cursos.cargos WHERE id_cargo = :id");

        if ($id_cargo_coord) {
            $stmtCargo->execute(['id' => $id_cargo_coord]);
            $coordData = $stmtCargo->fetch();
            if ($coordData) {
                $titulo = !empty($coordData['titulo']) ? $coordData['titulo'] : '';
                $nombre_coordinador = trim("$titulo " . $coordData['nombre'] . " " . $coordData['apellido']);
            }
        }

        if ($id_cargo_vicerrectorado) {
            $stmtCargo->execute(['id' => $id_cargo_vicerrectorado]);
            $viceData = $stmtCargo->fetch();
            if ($viceData) {
                $titulo = !empty($viceData['titulo']) ? $viceData['titulo'] : '';
                $nombre_vicerrector = trim("$titulo " . $viceData['nombre'] . " " . $viceData['apellido']);
            }
        }
    } catch (PDOException $e) {
        // En caso de error, mantenemos un fallback o logueamos
        error_log("Error obteniendo firmantes constancia: " . $e->getMessage());
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

$piePagina = '../public/assets/img/piePagina.jpg';
$encabezado = '../public/assets/img/vector membrete 1-01.png';
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script>

    const piePagina = "data:image/jpeg;base64,<?php echo base64_encode(file_get_contents($piePagina)); ?>";
    const encabezado = "data:image/png;base64,<?php echo base64_encode(file_get_contents($encabezado)); ?>";

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

            pdf.addImage('<?php echo $encabezado; ?>', 'PNG', xEncabezado, yEncabezado, imgWidthEncabezado, imgHeightEncabezado);

            const fechaInicio = obtenerFechaActual();

            // Texto del cuerpo
            let contenido = `
            Por medio de la presente, hacemos constar que el/la ciudadano(a) **${datos.nombre_promotor}**, titular de la cédula de identidad N° V-**${datos.cedula}**, participó en calidad de **Facilitador / Promotor** en la unidad curricular / curso titulado **${datos.nombre_curso}**, el cual es organizado por la **Coordinación de Formación Permanente** de la Universidad Politécnica Territorial Agroindustrial del Estado Táchira.
            
            La presente constancia se expide en San Cristóbal, a los ${fechaInicio}.
            
            Agradecemos su valiosa participación y compromiso con la formación continua.
        `;
            // Nota: He ajustado ligeramente el texto para que tenga sentido como "Constancia de Estudio" (Estudiante Activo)
            // en lugar de "participó como facilitador" (Pasado/Ponente), ya que dijiste que era para inscritos.

            const startX = 30;
            let startY = 50;
            const fontSize = 13;
            const lineSpacing = 7;
            const maxWidthText = 155;

            const regex = /(\*{2})+/g;
            const textoSinMarcas = contenido.replace(regex, '');
            let textoDividido = pdf.splitTextToSize(textoSinMarcas, maxWidthText);

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

            printCharacters(pdf, textRows, startY, startX, fontSize, lineSpacing, maxWidthText);

            // --- FIRMAS DINÁMICAS ---
            const nombreCoord = "<?php echo $nombre_coordinador; ?>";
            const cargoCoord = "Coordinación de Formación Permanente";

            const nombreVice = "<?php echo isset($nombre_vicerrector) ? $nombre_vicerrector : 'Vicerrectorado Académico'; ?>";
            const cargoVice = "Vicerrectorado Académico";

            // Texto de Atentamente centrado
            pdf.text("Atentamente,", 105, 175, { align: "center" });

            // Líneas superiores para las firmas
            pdf.setLineWidth(0.5);
            pdf.line(25, 205, 85, 205);
            pdf.line(125, 205, 185, 205);

            // Textos abajo de las líneas
            pdf.setFontSize(10);

            // Firma Coordinador (Izquierda)
            pdf.text(nombreCoord, 55, 210, { align: "center" });
            pdf.text(cargoCoord, 55, 215, { align: "center" });

            // Firma Vicerrector (Derecha)
            pdf.text(nombreVice, 155, 210, { align: "center", maxWidth: 55 });
            pdf.text(cargoVice, 155, 215, { align: "center", maxWidth: 55 });

            const correo = "Correo: <?php echo htmlspecialchars($correo_contacto); ?>\nTeléfono: <?php echo htmlspecialchars($telefono_contacto); ?>";
            pdf.text(correo, 20, pdf.internal.pageSize.getHeight() - 40, { maxWidth: 150, align: "left" });

            pdf.addImage('<?php echo $piePagina; ?>', 'JPEG', x, y, imgWidth, imgHeight);

            const pdfOutput = pdf.output('blob');
            const blobUrl = URL.createObjectURL(pdfOutput);
            const nombreArchivo = `Constancia-${datos.cedula}-${datos.nombre_curso.replace(/\s/g, '_')}.pdf`;

            if (/Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
                // Lógica Móvil
                document.body.innerHTML = `
                    <div style="font-family: sans-serif; text-align: center; padding: 40px; background-color: #f8f9fa; min-height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                        <h1 style="color: #0d6efd; margin-bottom: 20px;">¡Constancia Lista! 🎉</h1>
                        <p style="color: #6c757d; font-size: 16px; margin-bottom: 30px; max-width: 80%;">
                            Tu constancia ha sido generada exitosamente.<br><br>
                            Si la descarga no inició automáticamente, presiona el botón de abajo.
                        </p>
                        <button id="btnDescargaConst" style="padding: 15px 30px; font-size: 18px; color: white; background-color: #0d6efd; border: none; border-radius: 8px; box-shadow: 0 4px 6px rgba(13,110,253,0.2); cursor: pointer; font-weight: bold; transition: all 0.2s;">
                            Descargar PDF
                        </button>
                    </div>
                `;

                document.getElementById('btnDescargaConst').onclick = () => {
                    pdf.save(nombreArchivo);
                };

                // Auto-descargar usando save()
                setTimeout(() => {
                    pdf.save(nombreArchivo);
                }, 800);

            } else {
                // Lógica PC
                document.body.innerHTML = `
                    <div style="position: fixed; top: 15px; right: 25px; z-index: 9999;">
                        <button id="btnDescargaPcConst" style="padding: 12px 24px; color: white; background-color: #198754; border: none; border-radius: 6px; font-family: sans-serif; font-weight: 600; box-shadow: 0 4px 12px rgba(0,0,0,0.15); cursor: pointer; transition: transform 0.2s;">
                            Descargar Constancia
                        </button>
                    </div>
                    <iframe src="${blobUrl}" style="width: 100vw; height: 100vh; border: none; margin: 0; padding: 0; overflow: hidden; display: block;"></iframe>
                `;

                const btnPc = document.getElementById('btnDescargaPcConst');
                btnPc.onmouseover = () => btnPc.style.transform = "scale(1.05)";
                btnPc.onmouseout = () => btnPc.style.transform = "scale(1)";
                btnPc.onclick = () => {
                    pdf.save(nombreArchivo);
                };
            }
        };

        const printCharacters = (doc, textObject, startY, startX, fontSize, lineSpacing, maxWidth) => {
            textObject.forEach((row, rowIndex) => {
                let rowWidth = 0;
                let spaceCount = 0;

                // Calcular ancho real y cantidad de espacios
                Object.values(row).forEach(value => {
                    if (value.char) {
                        doc.setFont("Helvetica", value.bold ? "bold" : "normal");
                        rowWidth += doc.getStringUnitWidth(value.char) * fontSize * 0.3528;
                        if (value.char === ' ') spaceCount++;
                    }
                });

                // Espacio adicional por cada espacio ' ' para justificar
                let extraSpace = 0;
                // Solo justificar si hay espacios, ocupa al menos el 80% de la línea, y no es la última línea
                if (spaceCount > 0 && rowWidth > maxWidth * 0.8 && rowIndex < textObject.length - 1) {
                    // Prevenir justificar una línea antes de un salto de párrafo manual
                    const nextRow = textObject[rowIndex + 1];
                    if (nextRow && nextRow[0] && nextRow[0].char && nextRow[0].char.trim() !== '') {
                        extraSpace = (maxWidth - rowWidth) / spaceCount;
                    }
                }

                let currentX = startX;
                Object.values(row).forEach(value => {
                    if (value.char) {
                        doc.setFont("Helvetica", value.bold ? "bold" : "normal");
                        doc.text(value.char, currentX, startY);
                        currentX += doc.getStringUnitWidth(value.char) * fontSize * 0.3528;
                        if (value.char === ' ') {
                            currentX += extraSpace;
                        }
                    }
                });
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