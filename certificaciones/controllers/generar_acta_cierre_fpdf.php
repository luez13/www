<?php
// controllers/generar_acta_cierre_fpdf.php

include 'init.php';
require_once __DIR__ . '/../config/model.php';
require_once __DIR__ . '/../models/curso.php';
require_once __DIR__ . '/../vendor/autoload.php';

$db = new DB();
$conn = $db->getConn();
$cursoModel = new Curso($db);

$id_curso = isset($_GET['id_curso']) ? (int)$_GET['id_curso'] : 0;
if ($id_curso <= 0) {
    die("Curso inválido.");
}

// 1. Obtener información del curso
$stmtCurso = $conn->prepare("
    SELECT c.*, u.nombre as prom_nom, u.apellido as prom_ape, u.titulo as prom_tit
    FROM cursos.cursos c
    LEFT JOIN cursos.usuarios u ON c.promotor = u.id
    WHERE c.id_curso = :id
");
$stmtCurso->execute(['id' => $id_curso]);
$curso_info = $stmtCurso->fetch(PDO::FETCH_ASSOC);

if (!$curso_info) {
    die("Curso no encontrado.");
}

// 2. Docente Responsable
$docente_responsable = "No asignado";
if (!empty($curso_info['prom_nom'])) {
    $titulo = !empty($curso_info['prom_tit']) ? trim($curso_info['prom_tit']) . ' ' : '';
    $docente_responsable = trim($titulo . $curso_info['prom_nom'] . ' ' . $curso_info['prom_ape']);
}

// 3. Duración, Horas y Modalidad
$tipo_curso = $curso_info['tipo_curso'];
$duracion = $curso_info['tiempo_asignado'] ? $curso_info['tiempo_asignado'] . " semanas" : "No especificada";
$total_horas = $curso_info['horas_cronologicas'] ? $curso_info['horas_cronologicas'] . " horas" : "No especificadas";
$modalidad = "Multimodal";

// 4. Obtener todos los alumnos del curso
$stmtAlumnos = $conn->prepare("
    SELECT u.cedula, u.nombre, u.apellido, cert.nota, cert.completado, cert.tomo, cert.folio, u.id as id_usuario
    FROM cursos.certificaciones cert
    JOIN cursos.usuarios u ON cert.id_usuario = u.id
    WHERE cert.curso_id = :id
    ORDER BY u.apellido ASC, u.nombre ASC
");
$stmtAlumnos->execute(['id' => $id_curso]);
$alumnos = $stmtAlumnos->fetchAll(PDO::FETCH_ASSOC);

// 5. EVENTO DE CIERRE EXPLÍCITO Y ARRASTRE DE CRÉDITOS
$stmtTotal = $conn->prepare("SELECT COUNT(*) FROM cursos.materias_bimestre WHERE id_curso = :id");
$stmtTotal->execute(['id' => $id_curso]);
$total_materias_curso = (int)$stmtTotal->fetchColumn();

$stmtHist = $conn->prepare("
    SELECT 
        MAX(COALESCE(NULLIF(um_hist.nota_recuperativa, 0), um_hist.nota_regular)) AS nota_historica
    FROM cursos.materias_bimestre m_pensum
    LEFT JOIN cursos.materias_bimestre mb_hist ON UPPER(TRIM(m_pensum.nombre_materia)) = UPPER(TRIM(mb_hist.nombre_materia))
    LEFT JOIN cursos.usuario_materias um_hist ON mb_hist.id_materia_bimestre = um_hist.id_materia_bimestre 
                                              AND um_hist.id_usuario = :id_usuario
                                              AND um_hist.estado LIKE 'Aprobado%'
    WHERE m_pensum.id_curso = :id_curso
    GROUP BY m_pensum.nombre_materia
");

$stmtUpdateCert = $conn->prepare("UPDATE cursos.certificaciones SET nota = :nota, completado = :completado WHERE id_usuario = :id_usuario AND curso_id = :id_curso");

$inscritos = count($alumnos);
$aprobados = 0;
$reprobados = 0;
$participantes = 0;
$no_completaron = 0;
$tiene_notas = false;

$nota_min = isset($curso_info['nota_minima_aprobatoria']) ? (int)$curso_info['nota_minima_aprobatoria'] : 12;

foreach ($alumnos as &$al) {
    // Calculamos el arrastre
    $stmtHist->execute(['id_curso' => $id_curso, 'id_usuario' => $al['id_usuario']]);
    $materias_historial = $stmtHist->fetchAll(PDO::FETCH_ASSOC);
    
    $materias_aprobadas = 0;
    $suma_notas = 0;
    foreach ($materias_historial as $mh) {
        if ($mh['nota_historica'] !== null) {
            $materias_aprobadas++;
            $suma_notas += (float)$mh['nota_historica'];
        }
    }

    if ($total_materias_curso > 0 && $materias_aprobadas == $total_materias_curso) {
        $al['completado'] = true;
        $al['nota'] = round($suma_notas / $total_materias_curso);
    } else {
        $al['completado'] = false;
        $al['nota'] = $total_materias_curso > 0 && $materias_aprobadas > 0 ? round($suma_notas / $total_materias_curso) : null;
    }

    // Persistir estado final de certificación para inmutabilidad del acta
    $stmtUpdateCert->execute([
        'nota' => $al['nota'],
        'completado' => $al['completado'] ? 'true' : 'false',
        'id_usuario' => $al['id_usuario'],
        'id_curso' => $id_curso
    ]);

    // Calcular contadores para el PDF
    if ($al['completado'] == false) {
        $reprobados++; // No completó -> Reprueba
        $no_completaron++; // Mantengo el contador por si otra vista lo necesita
    } else {
        if ($al['nota'] !== null && $al['nota'] !== '') {
            $tiene_notas = true;
            $nota_val = round((float)$al['nota']);
            if ($nota_val >= $nota_min) {
                $aprobados++; // Completó y alcanzó la nota -> Aprueba
            } else {
                $participantes++; // Completó pero no alcanzó la nota -> Participación
            }
        } else {
            $participantes++; // Completó sin nota cargada -> Participación
        }
    }
}
unset($al);

// 6. Configurar la Fecha y Hora de Cierre (Lógica Forense)
if (!empty($curso_info['fecha_acta_cierre'])) {
    // Si ya tiene una fecha de acta de cierre, usar esa (histórico inmutable)
    $timestamp_cierre = strtotime($curso_info['fecha_acta_cierre']);
} else {
    // Si es primera vez, usamos la que viene del form o la actual
    $fecha_cierre_param = isset($_GET['fecha_cierre']) ? $_GET['fecha_cierre'] : date('Y-m-d');
    $hora_cierre_param = isset($_GET['hora_cierre']) ? $_GET['hora_cierre'] : date('H:i:s');
    
    // Formatear para guardar en BD
    $fecha_completa = $fecha_cierre_param . ' ' . date('H:i:s', strtotime($hora_cierre_param));
    
    $stmt_update_date = $conn->prepare("UPDATE cursos.cursos SET fecha_acta_cierre = :fc WHERE id_curso = :id");
    $stmt_update_date->execute(['fc' => $fecha_completa, 'id' => $id_curso]);
    
    $timestamp_cierre = strtotime($fecha_completa);
}

$dia_cierre = date("d", $timestamp_cierre);
$meses = [
    '01' => 'enero', '02' => 'febrero', '03' => 'marzo', '04' => 'abril',
    '05' => 'mayo', '06' => 'junio', '07' => 'julio', '08' => 'agosto',
    '09' => 'septiembre', '10' => 'octubre', '11' => 'noviembre', '12' => 'diciembre'
];
$mes_cierre = $meses[date("m", $timestamp_cierre)];
$anio_cierre = date("Y", $timestamp_cierre);
$hora_cierre = htmlspecialchars(date('h:i a', $timestamp_cierre));

// 7. Cargar Firmantes Dinámicos
$firmantes = [];

// A. Vicerrectorado (Encargado del Área)
$stmtConfig1 = $conn->prepare("SELECT valor_config FROM cursos.config_sistema WHERE clave_config = 'ID_CARGO_VICERRECTORADO_POR_DEFECTO'");
$stmtConfig1->execute();
$id_vicerrector = $stmtConfig1->fetchColumn();
if ($id_vicerrector) {
    $stmtCargo1 = $conn->prepare("SELECT nombre, apellido, nombre_cargo, titulo, firma_digital FROM cursos.cargos WHERE id_cargo = :id");
    $stmtCargo1->execute(['id' => $id_vicerrector]);
    if ($c = $stmtCargo1->fetch(PDO::FETCH_ASSOC)) {
        $firmantes[] = [
            'nombre' => trim($c['nombre'] . ' ' . $c['apellido']),
            'titulo' => !empty($c['titulo']) ? trim($c['titulo']) : '',
            'cargo' => $c['nombre_cargo'],
            'firma_digital' => $c['firma_digital']
        ];
    }
}

// B. Coordinación de Formación Permanente
$stmtConfig2 = $conn->prepare("SELECT valor_config FROM cursos.config_sistema WHERE clave_config = 'ID_CARGO_COORD_FP_POR_DEFECTO'");
$stmtConfig2->execute();
$id_coord = $stmtConfig2->fetchColumn();
if ($id_coord) {
    $stmtCargo2 = $conn->prepare("SELECT nombre, apellido, nombre_cargo, titulo, firma_digital FROM cursos.cargos WHERE id_cargo = :id");
    $stmtCargo2->execute(['id' => $id_coord]);
    if ($c = $stmtCargo2->fetch(PDO::FETCH_ASSOC)) {
        $firmantes[] = [
            'nombre' => trim($c['nombre'] . ' ' . $c['apellido']),
            'titulo' => !empty($c['titulo']) ? trim($c['titulo']) : '',
            'cargo' => $c['nombre_cargo'],
            'firma_digital' => $c['firma_digital']
        ];
    }
}

// C. Facilitador del Curso
if (!empty($curso_info['promotor'])) {
    $stmtUser = $conn->prepare("SELECT nombre, apellido, titulo, firma_digital FROM cursos.usuarios WHERE id = :id");
    $stmtUser->execute(['id' => $curso_info['promotor']]);
    if ($u = $stmtUser->fetch(PDO::FETCH_ASSOC)) {
        $firmantes[] = [
            'nombre' => trim($u['nombre'] . ' ' . $u['apellido']),
            'titulo' => !empty($u['titulo']) ? trim($u['titulo']) : '',
            'cargo' => 'Facilitador(a)',
            'firma_digital' => $u['firma_digital']
        ];
    }
}

$img_encabezado = realpath(__DIR__ . '/../public/assets/img/vector membrete 1-01.png');
$img_pie = realpath(__DIR__ . '/../public/assets/img/piePagina.jpg');

// Preparar los datos consolidados para la vista FPDF
$data = [
    'nombre_diplomado' => $curso_info['nombre_curso'],
    'tipo_curso' => $tipo_curso,
    'duracion' => $duracion,
    'total_horas' => $total_horas,
    'modalidad' => $modalidad,
    'docente_responsable' => $docente_responsable,
    'inscritos' => $inscritos,
    'aprobados' => $aprobados,
    'no_aprobaron' => $reprobados,
    'participantes' => $participantes,
    'no_completaron' => $no_completaron,
    'tiene_notas' => $tiene_notas,
    'firmantes' => $firmantes,
    'dia_cierre' => $dia_cierre,
    'mes_cierre' => $mes_cierre,
    'anio_cierre' => $anio_cierre,
    'hora_cierre' => $hora_cierre,
    'img_encabezado' => $img_encabezado,
    'img_pie' => $img_pie,
    'alumnos' => $alumnos,
    'nota_minima_aprobatoria' => $nota_min
];

// --- GENERACIÓN CON FPDF ---
$pdf = new \FPDF();
$pdf->SetAutoPageBreak(true, 25);
require_once __DIR__ . '/../views/actas/acta_cierre_fpdf.php';

$pdf->Output('I', 'Acta_Cierre_' . preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($curso_info['nombre_curso'])) . '.pdf');
exit;
