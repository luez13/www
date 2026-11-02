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

// 🔒 Validación de Seguridad Multi-Tenant
if ($_SESSION['id_rol'] != 7 && !$_SESSION['es_multisede'] && $_SESSION['id_extension'] != $curso_info['id_extension']) {
    die("Acceso denegado: Este curso no pertenece a su dependencia operativa.");
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
    SELECT u.cedula, u.nombre, u.apellido, cert.nota, cert.nota as nota_original_bd, cert.completado, cert.tomo, cert.folio, u.id as id_usuario
    FROM cursos.certificaciones cert
    JOIN cursos.usuarios u ON cert.id_usuario = u.id
    WHERE cert.curso_id = :id
    ORDER BY u.apellido ASC, u.nombre ASC
");
$stmtAlumnos->execute(['id' => $id_curso]);
$alumnos = $stmtAlumnos->fetchAll(PDO::FETCH_ASSOC);

$stmtMaterias = $conn->prepare("SELECT id_materia_bimestre, nombre_materia FROM cursos.materias_bimestre WHERE id_curso = :id ORDER BY id_materia_bimestre ASC");
$stmtMaterias->execute(['id' => $id_curso]);
$lista_materias = $stmtMaterias->fetchAll(PDO::FETCH_ASSOC);

// 5. EVENTO DE CIERRE EXPLÍCITO Y ARRASTRE DE CRÉDITOS
$total_materias_curso = count($lista_materias);
$tipo_acta = (isset($_GET['tipo_acta']) && $_GET['tipo_acta'] === 'Recuperativo') ? 'Recuperativo' : 'Regular';
$mapa_recup_curso = $cursoModel->obtenerMapaRecuperativosCurso($id_curso);

$stmtHist = $conn->prepare("
    SELECT 
        m_pensum.id_materia_bimestre,
        m_pensum.nombre_materia,
        MAX(um_hist.nota_regular) as nota_regular,
        MAX(um_hist.nota_recuperativa) as nota_recuperativa,
        MAX(COALESCE(NULLIF(um_hist.nota_recuperativa, 0), um_hist.nota_regular)) AS nota_historica
    FROM cursos.materias_bimestre m_pensum
    LEFT JOIN cursos.materias_bimestre mb_hist ON UPPER(TRIM(m_pensum.nombre_materia)) = UPPER(TRIM(mb_hist.nombre_materia))
    LEFT JOIN cursos.usuario_materias um_hist ON mb_hist.id_materia_bimestre = um_hist.id_materia_bimestre 
                                              AND um_hist.id_usuario = :id_usuario
                                              AND um_hist.estado LIKE 'Aprobado%'
    WHERE m_pensum.id_curso = :id_curso
    GROUP BY m_pensum.id_materia_bimestre, m_pensum.nombre_materia
    ORDER BY m_pensum.id_materia_bimestre ASC
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
    $uid = (int)$al['id_usuario'];
    $al['nota_regular_curso'] = ($al['nota_original_bd'] !== null && $al['nota_original_bd'] !== '') ? round((float)$al['nota_original_bd']) : null;
    $al['nota_recuperativa_curso'] = null;

    // Calculamos el arrastre
    $stmtHist->execute(['id_curso' => $id_curso, 'id_usuario' => $uid]);
    $materias_historial = $stmtHist->fetchAll(PDO::FETCH_ASSOC);
    
    $al['historial_materias'] = [];
    $materias_aprobadas = 0;
    $suma_notas = 0;
    $max_recup = null;
    foreach ($materias_historial as $mh) {
        $al['historial_materias'][$mh['id_materia_bimestre']] = [
            'nombre_materia' => $mh['nombre_materia'],
            'nota_historica' => $mh['nota_historica'],
            'nota_regular' => $mh['nota_regular'],
            'nota_recuperativa' => $mh['nota_recuperativa']
        ];
        
        if ($mh['nota_recuperativa'] !== null) {
            if ($max_recup === null || $mh['nota_recuperativa'] > $max_recup) {
                $max_recup = $mh['nota_recuperativa'];
            }
        }
        
        if ($mh['nota_historica'] !== null) {
            $materias_aprobadas++;
            $suma_notas += (float)$mh['nota_historica'];
        }
    }
    
    $al['recuperativo_diplomado'] = $max_recup;

    if ($total_materias_curso == 0 && isset($mapa_recup_curso[$uid]) && $mapa_recup_curso[$uid]['nota_recuperativa'] !== null) {
        $al['nota_regular_curso'] = $mapa_recup_curso[$uid]['nota_regular'] !== null ? round((float)$mapa_recup_curso[$uid]['nota_regular']) : 0;
        $al['nota_recuperativa_curso'] = round((float)$mapa_recup_curso[$uid]['nota_recuperativa']);
        $al['nota'] = max((float)$al['nota_regular_curso'], (float)$al['nota_recuperativa_curso']);
        $al['completado'] = ($al['nota'] >= $nota_min);
        $al['recuperativo_diplomado'] = $al['nota_recuperativa_curso'];

        // Inyectar en historial_materias para que el Anexo 3 también funcione en cursos normales
        $al['historial_materias'][0] = [
            'nombre_materia' => $curso_info['nombre_curso'],
            'nota_historica' => $al['nota'],
            'nota_regular' => $al['nota_regular_curso'],
            'nota_recuperativa' => $al['nota_recuperativa_curso']
        ];
    } elseif (isset($al['nota_original_bd']) && $al['nota_original_bd'] !== null && $al['nota_original_bd'] !== '') {
        $al['completado'] = true;
        $al['nota'] = $al['nota_original_bd'];
    } elseif ($total_materias_curso > 0) {
        if ($materias_aprobadas == $total_materias_curso) {
            $al['completado'] = true;
            $al['nota'] = round($suma_notas / $total_materias_curso);
        } else {
            $al['completado'] = false;
            $al['nota'] = $materias_aprobadas > 0 ? round($suma_notas / $total_materias_curso) : null;
        }

        // Persistir estado final de certificación para inmutabilidad del acta solo si es curso modular
        $stmtUpdateCert->execute([
            'nota' => $al['nota'],
            'completado' => $al['completado'] ? 'true' : 'false',
            'id_usuario' => $al['id_usuario'],
            'id_curso' => $id_curso
        ]);
    } else {
        // Si no tiene materias (curso normal), conservamos la nota manual de la base de datos
        $al['completado'] = (bool)$al['completado'];
    }

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

// Si el usuario solicitó explícitamente el Acta de Cierre de Recuperativo, filtramos y recalculamos métricas
if ($tipo_acta === 'Recuperativo') {
    $alumnos_recup = [];
    $aprobados = 0;
    $reprobados = 0;
    $participantes = 0;
    $no_completaron = 0;
    $tiene_notas = true;

    foreach ($alumnos as $al) {
        $tiene_recup = ($al['nota_recuperativa_curso'] !== null) || ($al['recuperativo_diplomado'] !== null);
        if ($tiene_recup) {
            $nota_eval = $al['nota_recuperativa_curso'] !== null ? (float)$al['nota_recuperativa_curso'] : (float)$al['recuperativo_diplomado'];
            if ($nota_eval >= $nota_min) {
                $aprobados++;
            } else {
                $reprobados++;
            }
            $alumnos_recup[] = $al;
        }
    }
    $alumnos = $alumnos_recup;
    $inscritos = count($alumnos);
}

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

// 7. Cargar Firmantes Dinámicos (Extensión + Fallback a Configuración Global)
$firmantes = [];
$id_extension_curso = !empty($curso_info['id_extension']) ? (int)$curso_info['id_extension'] : (isset($_SESSION['id_extension']) ? (int)$_SESSION['id_extension'] : 1);

$configs_globales = [];
try {
    $stmtCfg = $conn->query("SELECT clave_config, valor_config FROM cursos.config_sistema WHERE clave_config IN ('ID_CARGO_COORD_FP_POR_DEFECTO', 'ID_CARGO_ENCARGADO_POR_DEFECTO', 'ID_CARGO_VICERRECTORADO_POR_DEFECTO')");
    $configs_globales = $stmtCfg->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Exception $e) {
    $configs_globales = [];
}

// A. Vicerrectorado (Encargado del Área)
$c1 = null;
$id_cfg_enc = !empty($configs_globales['ID_CARGO_VICERRECTORADO_POR_DEFECTO'])
    ? (int)$configs_globales['ID_CARGO_VICERRECTORADO_POR_DEFECTO']
    : (!empty($configs_globales['ID_CARGO_ENCARGADO_POR_DEFECTO']) ? (int)$configs_globales['ID_CARGO_ENCARGADO_POR_DEFECTO'] : 0);

$enc_deshabilitado_global = (
    (array_key_exists('ID_CARGO_VICERRECTORADO_POR_DEFECTO', $configs_globales) || array_key_exists('ID_CARGO_ENCARGADO_POR_DEFECTO', $configs_globales))
    && $id_cfg_enc === 0
);

if (!$enc_deshabilitado_global) {
    if ($id_cfg_enc > 0) {
        $stmtC1Cfg = $conn->prepare("SELECT nombre, apellido, nombre_cargo, titulo, firma_digital FROM cursos.cargos WHERE id_cargo = :id AND activo = true LIMIT 1");
        $stmtC1Cfg->execute(['id' => $id_cfg_enc]);
        $c1 = $stmtC1Cfg->fetch(PDO::FETCH_ASSOC);
    }
    if (!$c1 && $id_extension_curso) {
        $stmtCargo1 = $conn->prepare("SELECT nombre, apellido, nombre_cargo, titulo, firma_digital FROM cursos.cargos WHERE (nombre_cargo ILIKE '%Vice-Rector%' OR nombre_cargo ILIKE '%Vicerrector%' OR nombre_cargo ILIKE '%Encargad%') AND activo = true AND id_extension = :id_extension LIMIT 1");
        $stmtCargo1->execute(['id_extension' => $id_extension_curso]);
        $c1 = $stmtCargo1->fetch(PDO::FETCH_ASSOC);
    }
}
if ($c1) {
    $firmantes[] = [
        'nombre' => trim($c1['nombre'] . ' ' . $c1['apellido']),
        'titulo' => !empty($c1['titulo']) ? trim($c1['titulo']) : '',
        'cargo' => $c1['nombre_cargo'],
        'firma_digital' => $c1['firma_digital']
    ];
}

// B. Coordinador(a) / Director(a) de la Dependencia
$c2 = null;
$coord_deshabilitado_global = (array_key_exists('ID_CARGO_COORD_FP_POR_DEFECTO', $configs_globales) && trim((string)$configs_globales['ID_CARGO_COORD_FP_POR_DEFECTO']) === '');

if (!$coord_deshabilitado_global) {
    if (!empty($configs_globales['ID_CARGO_COORD_FP_POR_DEFECTO'])) {
        $stmtC2Cfg = $conn->prepare("SELECT nombre, apellido, nombre_cargo, titulo, firma_digital FROM cursos.cargos WHERE id_cargo = :id AND activo = true LIMIT 1");
        $stmtC2Cfg->execute(['id' => (int)$configs_globales['ID_CARGO_COORD_FP_POR_DEFECTO']]);
        $c2 = $stmtC2Cfg->fetch(PDO::FETCH_ASSOC);
    }
    if (!$c2 && $id_extension_curso) {
        $stmtCargo2 = $conn->prepare("SELECT nombre, apellido, nombre_cargo, titulo, firma_digital FROM cursos.cargos WHERE (nombre_cargo ILIKE '%Coord%' OR nombre_cargo ILIKE '%Director%') AND activo = true AND id_extension = :id_extension LIMIT 1");
        $stmtCargo2->execute(['id_extension' => $id_extension_curso]);
        $c2 = $stmtCargo2->fetch(PDO::FETCH_ASSOC);
    }
}
if ($c2) {
    $firmantes[] = [
        'nombre' => trim($c2['nombre'] . ' ' . $c2['apellido']),
        'titulo' => !empty($c2['titulo']) ? trim($c2['titulo']) : '',
        'cargo' => $c2['nombre_cargo'],
        'firma_digital' => $c2['firma_digital']
    ];
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
    'tipo_acta' => $tipo_acta,
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
    'lista_materias' => $lista_materias,
    'nota_minima_aprobatoria' => $nota_min
];

// --- GENERACIÓN CON FPDF ---
$pdf = new \FPDF();
$pdf->SetAutoPageBreak(true, 25);
require_once __DIR__ . '/../views/actas/acta_cierre_fpdf.php';

$prefijo_pdf = ($tipo_acta === 'Recuperativo') ? 'Acta_Cierre_Recuperativo_' : 'Acta_Cierre_';
$pdf->Output('I', $prefijo_pdf . preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($curso_info['nombre_curso'])) . '.pdf');
exit;
