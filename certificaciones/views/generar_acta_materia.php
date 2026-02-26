<?php
// views/generar_acta_materia.php

include '../controllers/init.php';
require_once('../config/model.php');
require_once('../models/Materia.php');
require_once('../models/Nota.php');

if (!isset($_SESSION['user_id'])) { die('Acceso denegado.'); }

$db = new DB();
$conn = $db->getConn();
$materiaModel = new Materia($conn);
$notaModel = new Nota($conn);

$id_materia = isset($_REQUEST['id_materia']) ? (int)$_REQUEST['id_materia'] : 0;
if ($id_materia === 0) { die("Error: Se requiere el ID de la materia."); }

// --- 1. PROCESAMIENTO DE IMÁGENES ---
$pathEncabezado = '../public/assets/img/encabezado.jpg';
$pathPiePagina = '../public/assets/img/piePagina.jpg';

$encabezadoB64 = "";
$piePaginaB64 = "";

if (file_exists($pathEncabezado)) {
    $encabezadoB64 = "data:image/jpeg;base64," . base64_encode(file_get_contents($pathEncabezado));
}
if (file_exists($pathPiePagina)) {
    $piePaginaB64 = "data:image/jpeg;base64," . base64_encode(file_get_contents($pathPiePagina));
}

// --- 2. FUNCIONES DE FORMATO DE TEXTO (NUEVO) ---

// Para Nombres: Luis David Gomez Peñaloza
function formatoNombre($texto) {
    return mb_convert_case(mb_strtolower($texto, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
}

// Para Títulos y Materias: Capitaliza inicio y después de . : -
function formatoTexto($texto) {
    $texto = mb_strtolower(trim($texto), 'UTF-8');
    // Regex: Busca el inicio (^) o puntuación (. : -) seguido de espacios opcionales, y captura la siguiente letra
    return preg_replace_callback('/(^|[\.\:\-]\s*)([a-zñáéíóúü])/u', function($m) {
        return $m[1] . mb_strtoupper($m[2], 'UTF-8');
    }, $texto);
}

// Para Periodos (Mantiene lógica anterior pero retorna en Mayúsculas para destacar en el título)
function formatearPeriodo($textoRaw, $lapsoNumBd) {
    $texto = mb_strtolower(trim($textoRaw), 'UTF-8');
    $tipo = "PERIODO"; 
    if (strpos($texto, 'bimestre') !== false) { $tipo = "BIMESTRE"; }
    elseif (strpos($texto, 'trimestre') !== false) { $tipo = "TRIMESTRE"; }
    elseif (strpos($texto, 'semestre') !== false) { $tipo = "SEMESTRE"; }
    elseif (strpos($texto, 'modulo') !== false || strpos($texto, 'módulo') !== false) { $tipo = "MÓDULO"; }
    elseif (strpos($texto, 'lapso') !== false) { $tipo = "LAPSO"; }
    elseif (strpos($texto, 'corte') !== false) { $tipo = "CORTE"; }

    $numero = intval($lapsoNumBd);
    if ($numero <= 1) {
        if (preg_match('/(\d+)/', $texto, $matches)) {
            $val = intval($matches[1]);
            if ($val > 1) $numero = $val;
        }
        elseif (strpos($texto, 'segundo') !== false) { $numero = 2; }
        elseif (strpos($texto, 'tercer') !== false) { $numero = 3; }
    }

    $ordinal = "";
    switch ($numero) {
        case 1: $ordinal = "PRIMER"; break;
        case 2: $ordinal = "SEGUNDO"; break;
        case 3: $ordinal = "TERCER"; break;
        case 4: $ordinal = "CUARTO"; break;
        case 5: $ordinal = "QUINTO"; break;
        case 6: $ordinal = "SEXTO"; break;
        default: $ordinal = ($numero > 0) ? $numero : ""; break;
    }

    if ($numero === 0 && $tipo === "PERIODO") {
        return mb_strtoupper($textoRaw, 'UTF-8');
    }
    return trim($ordinal . " " . $tipo);
}

// --- 3. OBTENER DATOS DE LA BD ---
$sql_info = "SELECT m.nombre_materia, m.duracion_bimestres, m.lapso_academico, m.total_horas, m.modalidad, 
                    c.nombre_curso, c.id_curso,
                    u.nombre as nom_doc, u.apellido as ape_doc, u.cedula as ced_doc, u.firma_digital
             FROM cursos.materias_bimestre m
             JOIN cursos.cursos c ON m.id_curso = c.id_curso
             LEFT JOIN cursos.usuarios u ON m.docente_id = u.id
             WHERE m.id_materia_bimestre = :id";
$stmt = $conn->prepare($sql_info);
$stmt->execute(['id' => $id_materia]);
$info = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$info) { die("Materia no encontrada."); }

// --- 4. DATOS DEL COORDINADOR ---
$nombre_coordinador = "Coordinación de Formación Permanente";
$cargo_coordinador = "Coordinador(a)";

$sql_curso_config = "SELECT car.nombre, car.apellido, car.nombre_cargo 
                     FROM cursos.cursos_config_firmas ccf
                     JOIN cursos.cargos car ON ccf.id_cargo_firmante = car.id_cargo
                     WHERE ccf.id_curso = :id_curso AND ccf.usar_promotor_curso = false LIMIT 1";
$stmt_cc = $conn->prepare($sql_curso_config);
$stmt_cc->execute(['id_curso' => $info['id_curso']]);
$coord_curso = $stmt_cc->fetch(PDO::FETCH_ASSOC);

if ($coord_curso) {
    $nombre_coordinador = $coord_curso['nombre'] . ' ' . $coord_curso['apellido'];
    $cargo_coordinador = $coord_curso['nombre_cargo'];
} else {
    $stmtConfig = $conn->prepare("SELECT valor_config FROM cursos.config_sistema WHERE clave_config = 'ID_CARGO_COORD_FP_POR_DEFECTO'");
    $stmtConfig->execute();
    $id_defecto = $stmtConfig->fetchColumn();
    if ($id_defecto) {
        $stmtCargo = $conn->prepare("SELECT nombre, apellido, nombre_cargo FROM cursos.cargos WHERE id_cargo = :id");
        $stmtCargo->execute(['id' => $id_defecto]);
        $coord_sys = $stmtCargo->fetch(PDO::FETCH_ASSOC);
        if ($coord_sys) {
            $nombre_coordinador = $coord_sys['nombre'] . ' ' . $coord_sys['apellido'];
            $cargo_coordinador = $coord_sys['nombre_cargo'];
        }
    }
}

// --- 5. PROCESAR NOTAS ---
$plan = $notaModel->getPlanEvaluacion($id_materia);
$alumnos_raw = $notaModel->getNotasDetalladas($id_materia);

$lista_alumnos = [];
$total_aprobados = 0;
$total_reprobados = 0;

foreach ($alumnos_raw as $al) {
    $notas_alumno = [];
    $definitiva_acum = 0;

    foreach ($plan as $actividad) {
        $id_act = $actividad['id_actividad_config'];
        $valor = isset($al['notas_actividad'][$id_act]) ? floatval($al['notas_actividad'][$id_act]) : 0;
        $notas_alumno[] = $valor; 
        $definitiva_acum += $valor * ($actividad['ponderacion_porcentaje'] / 100);
    }

    $definitiva = round($definitiva_acum);
    $estado = ($definitiva >= 12) ? 'APROBADO' : 'REPROBADO';

    if ($estado === 'APROBADO') $total_aprobados++;
    else $total_reprobados++;

    $lista_alumnos[] = [
        'cedula' => $al['cedula'],
        // Aplicamos formatoNombre (Title Case)
        'nombre' => formatoNombre($al['apellido'] . ' ' . $al['nombre']),
        'notas_parciales' => $notas_alumno,
        'definitiva' => $definitiva,
        'estado' => $estado
    ];
}

$periodo_titulo = formatearPeriodo($info['duracion_bimestres'], $info['lapso_academico']);

// --- CONSTRUCCIÓN DEL ARRAY PARA JS CON EL NUEVO FORMATO ---
$actaData = [
    // Usamos formatoTexto() para respetar : y .
    'curso' => formatoTexto($info['nombre_curso']), 
    'materia' => formatoTexto($info['nombre_materia']),
    'duracion' => formatoTexto($info['duracion_bimestres']),
    'periodo_titulo' => $periodo_titulo,
    'horas' => $info['total_horas'],
    'modalidad' => formatoTexto($info['modalidad']),
    
    // Usamos formatoNombre() para las personas
    'docente' => formatoNombre($info['nom_doc'] . ' ' . $info['ape_doc']),
    'coordinador' => formatoNombre($nombre_coordinador),
    
    // Cargos (Texto normal capitalizado)
    'cargo_coordinador' => formatoTexto($cargo_coordinador),

    'fecha_actual' => date('d'),
    'mes_actual' => date('m'),
    'anio_actual' => date('Y'),
    'hora_actual' => date('h:i a'),
    
    'total_participantes' => count($lista_alumnos),
    'aprobados' => $total_aprobados,
    'reprobados' => $total_reprobados,
    'columnas_evaluacion' => array_map(function($p) { 
        // Formato: Taller (15%) - Taller capitalizado
        return formatoTexto($p['nombre_actividad']) . "\n(" . floatval($p['ponderacion_porcentaje']) . "%)"; 
    }, $plan),
    'alumnos' => $lista_alumnos,
    
    'img_encabezado' => $encabezadoB64,
    'img_pie' => $piePaginaB64
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta - <?= htmlspecialchars($info['nombre_materia']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Acta de Cierre</h5>
            <button class="btn btn-light btn-sm" onclick="window.close()">Cerrar Pestaña</button>
        </div>
        <div class="card-body">
            <div id="preview-container" class="border mt-3" style="height: 800px;">
                <iframe id="pdf-preview" width="100%" height="100%"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
const DATA = <?= json_encode($actaData) ?>;

function getMesNombre(num) {
    const meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
    return meses[parseInt(num) - 1];
}

window.onload = function() { generarPDF(); };

function generarPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'letter'); 

    doc.setFont("times", "normal");
    const marginX = 20;
    const pageWidth = doc.internal.pageSize.getWidth();
    const pageHeight = doc.internal.pageSize.getHeight();
    const contentWidth = pageWidth - (marginX * 2);

    // --- 1. ENCABEZADO ---
    if (DATA.img_encabezado) {
        doc.addImage(DATA.img_encabezado, 'JPEG', 0, 0, pageWidth, 25);
    }

    let y = 40; 

    // --- 2. TÍTULOS ---
    doc.setFontSize(14);
    doc.setFont("times", "bold"); 
    
    // El periodo (PRIMER BIMESTRE) se ve bien en mayúsculas por ser parte de la estructura del título
    const tituloActa = `ACTA DE CIERRE DEL ${DATA.periodo_titulo} DEL DIPLOMADO:`;
    const splitTitulo = doc.splitTextToSize(tituloActa, contentWidth);
    doc.text(splitTitulo, pageWidth / 2, y, { align: 'center' });
    y += (splitTitulo.length * 7);

    // El nombre del diplomado ahora respeta Mayúsculas/Minúsculas
    const nombreDiplomado = DATA.curso;
    const splitNomDip = doc.splitTextToSize(nombreDiplomado, contentWidth);
    doc.text(splitNomDip, pageWidth / 2, y, { align: 'center' });
    y += (splitNomDip.length * 7) + 5; 

    // --- 3. CUERPO DEL ACTA ---
    doc.setFontSize(12);
    doc.setFont("times", "normal");
    const mesNombre = getMesNombre(DATA.mes_actual);
    
    const sangria = "     "; 
    // Texto intro usando las variables ya formateadas correctamente
    const textoIntro = `${sangria}En la ciudad de San Cristóbal, Estado Táchira, a los ${DATA.fecha_actual} días del mes de ${mesNombre} del año ${DATA.anio_actual}, siendo las ${DATA.hora_actual}, se procede a realizar el cierre académico correspondiente a la materia del Diplomado impartido en la Universidad Politécnica Territorial Agroindustrial del Estado Táchira.`;
    
    const splitIntro = doc.splitTextToSize(textoIntro, contentWidth);
    doc.text(splitIntro, marginX, y, { align: 'justify', maxWidth: contentWidth });
    y += (splitIntro.length * 6) + 3;

    doc.text("Teniendo como unidad curricular:", marginX, y);
    y += 6;

    // Detalles (Ahora con formato inteligente)
    doc.setFont("times", "bold");
    const detalles = [
        `• Nombre de la materia: ${DATA.materia}`,
        `• Duración: ${DATA.duracion}`,
        `• Total de horas: ${DATA.horas} horas`,
        `• Modalidad: ${DATA.modalidad}`,
        `• Docente responsable: ${DATA.docente}`
    ];

    const detalleLineHeight = 5; 
    detalles.forEach(d => {
        doc.text(d, marginX + 10, y);
        y += detalleLineHeight;
    });
    y += 4; 

    // Párrafos Legales
    doc.setFont("times", "normal");
    
    const parrafos = [
        "En cumplimiento con los lineamientos establecidos por la institución y con el objetivo de evaluar el desarrollo académico de los participantes, así como el cumplimiento de los objetivos planteados, se procede a dar cierre formal a la materia antes mencionada. Durante el transcurso de este periodo, se llevaron a cabo diversas actividades académicas, que incluyeron clases teóricas, talleres prácticos y evaluaciones, las cuales permitieron a los participantes adquirir competencias y habilidades en el área de estudio.",
        `Por otra parte, se registró la participación de un total de ${DATA.total_participantes} estudiantes, con la aprobación de ${DATA.aprobados} participantes, quienes demostraron un compromiso notable a lo largo del curso. En cuanto a los resultados, los estudiantes fueron evaluados mediante una combinación de trabajos prácticos, foros, talleres y participación continua. La calificación definitiva se ha registrado de acuerdo a los criterios establecidos en el programa del diplomado.`,
        "Agradecemos a todos los participantes por su dedicación y esfuerzo, así como al cuerpo docente y administrativo de la UPTAIET por su apoyo y colaboración durante este proceso formativo. Sin más asuntos que tratar, se levanta la presente acta, que será firmada por los presentes como constancia del cierre de la materia."
    ];

    parrafos.forEach(p => {
        const textoConSangria = sangria + p;
        const lines = doc.splitTextToSize(textoConSangria, contentWidth);
        doc.text(lines, marginX, y, { align: 'justify', maxWidth: contentWidth });
        y += (lines.length * 6) + 3; 
    });

    // --- 4. FIRMAS (FÍSICAS) ---
    y = Math.max(y + 10, pageHeight - 55); 
    if (y > pageHeight - 45) { doc.addPage(); y = 40; }

    doc.setLineWidth(0.5);
    
    const xIzq = marginX + 40; 
    const xDer = pageWidth - marginX - 40; 

    doc.line(marginX + 5, y, marginX + 75, y); 
    doc.line(pageWidth - marginX - 75, y, pageWidth - marginX - 5, y); 

    // Textos de Firma (Formateados)
    doc.setFont("times", "bold");
    doc.setFontSize(10);
    
    // Izquierda
    doc.text(DATA.coordinador, xIzq, y + 5, { align: 'center' });
    doc.text(DATA.cargo_coordinador, xIzq, y + 10, { align: 'center' });
    
    // Derecha
    doc.text(DATA.docente, xDer, y + 5, { align: 'center' });
    doc.setFont("times", "normal");
    doc.text("Facilitador(a)", xDer, y + 10, { align: 'center' });

    // --- 5. PIE DE PÁGINA ---
    if (DATA.img_pie) {
        doc.addImage(DATA.img_pie, 'JPEG', 10, pageHeight - 20, pageWidth - 20, 15);
    }

    // ================= PÁGINA 2: ANEXO =================
    doc.addPage('letter', 'l');
    const pageLWidth = doc.internal.pageSize.getWidth();
    const marginL = 15;

    if (DATA.img_encabezado) {
        doc.addImage(DATA.img_encabezado, 'JPEG', 0, 0, pageLWidth, 25);
    }

    doc.setFontSize(14);
    doc.setFont("times", "bold");
    doc.text("ANEXO: CALIFICACIONES DETALLADAS", pageLWidth / 2, 35, { align: 'center' });
    
    doc.setFontSize(10);
    doc.setFont("times", "normal");
    doc.text(`Materia: ${DATA.materia}`, marginL, 45);

    let columns = [
        { header: 'No.', dataKey: 'index' },
        { header: 'Cédula', dataKey: 'cedula' },
        { header: 'Participante', dataKey: 'nombre' }
    ];

    DATA.columnas_evaluacion.forEach((colName, idx) => {
        columns.push({ header: colName, dataKey: 'act_' + idx });
    });

    columns.push({ header: 'Def.', dataKey: 'definitiva' });

    let body = DATA.alumnos.map((al, i) => {
        let row = {
            index: i + 1,
            cedula: al.cedula,
            nombre: al.nombre,
            definitiva: al.definitiva,
            estado_interno: al.estado 
        };
        al.notas_parciales.forEach((nota, idx) => {
            row['act_' + idx] = nota > 0 ? nota : 'NP';
        });
        return row;
    });

    doc.autoTable({
        startY: 50,
        columns: columns,
        body: body,
        theme: 'grid',
        headStyles: { fillColor: [44, 62, 80], textColor: 255, halign: 'center', valign: 'middle' },
        styles: { fontSize: 10, cellPadding: 2, valign: 'middle', halign: 'center' },
        columnStyles: {
            0: { cellWidth: 15 }, 
            1: { cellWidth: 25 },
            2: { cellWidth: 'auto', halign: 'left' },
            [columns.length - 1]: { fontStyle: 'bold', cellWidth: 20 }
        },
        didParseCell: function(data) {
            if (data.section === 'body') {
                let rowRaw = data.row.raw; 
                if (rowRaw.estado_interno === 'REPROBADO') {
                    data.cell.styles.fillColor = [255, 230, 230]; 
                }
            }
        }
    });

    if (DATA.img_pie) {
        doc.addImage(DATA.img_pie, 'JPEG', 10, doc.internal.pageSize.getHeight() - 20, pageLWidth - 20, 15);
    }

    const pdfBlob = doc.output('bloburl');
    document.getElementById('pdf-preview').src = pdfBlob;
}
</script>

</body>
</html>