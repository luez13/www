<?php
// controllers/generar_acta_materia_fpdf.php

include 'init.php';
require_once __DIR__ . '/../config/model.php';
require_once __DIR__ . '/../models/Materia.php';
require_once __DIR__ . '/../models/Nota.php';
require_once __DIR__ . '/../vendor/autoload.php';

if (!isset($_SESSION['user_id'])) { die('Acceso denegado.'); }

$db = new DB();
$conn = $db->getConn();
$materiaModel = new Materia($conn);
$notaModel = new Nota($conn);

$id_materia = isset($_REQUEST['id_materia']) ? (int)$_REQUEST['id_materia'] : 0;
if ($id_materia === 0) { die("Error: Se requiere el ID de la materia."); }

$tipo_acta = isset($_REQUEST['tipo']) && $_REQUEST['tipo'] === 'recuperativo' ? 'Recuperativo' : 'Regular';

// --- 1. PROCESAMIENTO DE IMÁGENES (RUTAS ABSOLUTAS) ---
$img_encabezado = realpath(__DIR__ . '/../public/assets/img/vector membrete 1-01.png');
$img_pie = realpath(__DIR__ . '/../public/assets/img/piePagina.jpg');

// --- 2. FUNCIONES DE FORMATO (IDÉNTICAS PARA MANTENER DISEÑO) ---
$tituloActaBase = ($tipo_acta === 'Recuperativo') ? "ACTA DE CIERRE (RECUPERATIVO) DEL " : "ACTA DE CIERRE DEL ";

function formatoNombre($texto) {
    return mb_convert_case(mb_strtolower($texto, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
}

function formatoTexto($texto) {
    $texto = mb_strtolower(trim($texto), 'UTF-8');
    return preg_replace_callback('/(^|[\.\:\-]\s*)([a-zñáéíóúü])/u', function($m) {
        return $m[1] . mb_strtoupper($m[2], 'UTF-8');
    }, $texto);
}

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
    $ordinal = "";
    switch ($numero) {
        case 1: $ordinal = "PRIMER"; break;
        case 2: $ordinal = "SEGUNDO"; break;
        case 3: $ordinal = "TERCER"; break;
        case 4: $ordinal = "CUARTO"; break;
        default: $ordinal = ($numero > 0) ? $numero : ""; break;
    }
    return trim($ordinal . " " . $tipo);
}

// --- 3. OBTENER DATOS DE LA BD ---
$sql_info = "SELECT m.nombre_materia, m.duracion_bimestres, m.lapso_academico, m.total_horas, m.modalidad, 
                    c.nombre_curso, c.id_curso, c.tipo_curso,
                    u.nombre as nom_doc, u.apellido as ape_doc, u.cedula as ced_doc, u.firma_digital as firma_doc
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

$firma_coordinador = "";

$stmtConfig = $conn->prepare("SELECT valor_config FROM cursos.config_sistema WHERE clave_config = 'ID_CARGO_COORD_FP_POR_DEFECTO'");
$stmtConfig->execute();
$id_defecto = $stmtConfig->fetchColumn();
if ($id_defecto) {
    $stmtCargo = $conn->prepare("SELECT nombre, apellido, nombre_cargo, firma_digital FROM cursos.cargos WHERE id_cargo = :id");
    $stmtCargo->execute(['id' => $id_defecto]);
    $coord_sys = $stmtCargo->fetch(PDO::FETCH_ASSOC);
    if ($coord_sys) {
        $nombre_coordinador = $coord_sys['nombre'] . ' ' . $coord_sys['apellido'];
        $cargo_coordinador = $coord_sys['nombre_cargo'];
        $firma_coordinador = $coord_sys['firma_digital'];
    }
}

// --- 4.5 DATOS DEL ENCARGADO DEL ÁREA ---
$nombre_encargado = "Vicerrectorado Territorial";
$cargo_encargado = "Encargado(a)";
$firma_encargado = "";

$stmtConfigEnc = $conn->prepare("SELECT valor_config FROM cursos.config_sistema WHERE clave_config = 'ID_CARGO_VICERRECTORADO_POR_DEFECTO'");
$stmtConfigEnc->execute();
$id_enc_defecto = $stmtConfigEnc->fetchColumn();
if ($id_enc_defecto) {
    $stmtCargoEnc = $conn->prepare("SELECT nombre, apellido, nombre_cargo, firma_digital FROM cursos.cargos WHERE id_cargo = :id");
    $stmtCargoEnc->execute(['id' => $id_enc_defecto]);
    $enc_sys = $stmtCargoEnc->fetch(PDO::FETCH_ASSOC);
    if ($enc_sys) {
        $nombre_encargado = $enc_sys['nombre'] . ' ' . $enc_sys['apellido'];
        $cargo_encargado = $enc_sys['nombre_cargo'];
        $firma_encargado = $enc_sys['firma_digital'];
    }
}

// --- 5. PROCESAR NOTAS ---
$plan = $notaModel->getPlanEvaluacion($id_materia);
$alumnos_raw = $notaModel->getNotasDetalladas($id_materia);

$lista_alumnos = [];
$total_aprobados = 0;
$total_reprobados = 0;

// Preparar inserción de congelación para Acta Regular
$stmt_freeze = $conn->prepare("INSERT INTO cursos.usuario_materias (id_usuario, id_materia_bimestre, nota_regular, estado) 
                               VALUES (:u, :m, :n, :e) 
                               ON CONFLICT (id_usuario, id_materia_bimestre) 
                               DO UPDATE SET nota_regular = EXCLUDED.nota_regular, 
                               estado = CASE WHEN cursos.usuario_materias.nota_recuperativa IS NULL THEN EXCLUDED.estado ELSE cursos.usuario_materias.estado END");

// Obtener nota_minima
$stmt_min = $conn->prepare("SELECT nota_minima_aprobatoria FROM cursos.cursos WHERE id_curso = :id");
$stmt_min->execute(['id' => $info['id_curso']]);
$nota_minima = $stmt_min->fetchColumn() ?: 12;

foreach ($alumnos_raw as $al) {
    $definitiva_acum = 0;
    $notas_alumno = [];
    foreach ($plan as $actividad) {
        $id_act = $actividad['id_actividad_config'];
        $valor = isset($al['notas_actividad'][$id_act]) ? floatval($al['notas_actividad'][$id_act]) : 0;
        $notas_alumno[] = $valor; 
        $definitiva_acum += $valor * ($actividad['ponderacion_porcentaje'] / 100);
    }
    $definitiva = round($definitiva_acum);
    $estado_regular = ($definitiva >= $nota_minima) ? 'APROBADO' : 'REPROBADO';

    // Congelar en BD si es Acta Regular
    if ($tipo_acta === 'Regular') {
        $stmt_freeze->execute([
            'u' => $al['id'],
            'm' => $id_materia,
            'n' => $definitiva,
            'e' => ($estado_regular == 'APROBADO') ? 'Aprobado' : 'Reprobado'
        ]);
    }

    if ($tipo_acta === 'Recuperativo') {
        // En recuperativo, solo mostrar si tienen nota recuperativa
        if (isset($al['nota_recuperativa']) && $al['nota_recuperativa'] !== null) {
            $recup = floatval($al['nota_recuperativa']);
            $estado_recup = ($recup >= $nota_minima) ? 'APROBADO' : 'REPROBADO';
            
            // Reemplazar columnas de evaluación por "Nota Regular" y "Nota Recuperativo"
            $notas_alumno = [$definitiva, $recup];
            
            if ($estado_recup === 'APROBADO') $total_aprobados++; else $total_reprobados++;

            $lista_alumnos[] = [
                'cedula' => $al['cedula'],
                'nombre' => formatoNombre($al['apellido'] . ' ' . $al['nombre']),
                'notas_parciales' => $notas_alumno,
                'definitiva' => $recup,
                'estado' => $estado_recup
            ];
        }
    } else {
        // Acta Regular
        if ($estado_regular === 'APROBADO') $total_aprobados++; else $total_reprobados++;

        $lista_alumnos[] = [
            'cedula' => $al['cedula'],
            'nombre' => formatoNombre($al['apellido'] . ' ' . $al['nombre']),
            'notas_parciales' => $notas_alumno,
            'definitiva' => $definitiva,
            'estado' => $estado_regular
        ];
    }
}

if ($tipo_acta === 'Recuperativo') {
    $columnas = ['Nota Regular', 'Nota Recuperativo'];
} else {
    $columnas = array_map(function($p) { 
        return formatoTexto($p['nombre_actividad']) . " (" . floatval($p['ponderacion_porcentaje']) . "%)"; 
    }, $plan);
}

$meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];

$data = [
    'tipo_acta' => $tipo_acta,
    'tipo_curso' => $info['tipo_curso'],
    'curso' => formatoTexto($info['nombre_curso']), 
    'materia' => formatoTexto($info['nombre_materia']),
    'duracion' => formatoTexto($info['duracion_bimestres']),
    'periodo_titulo' => formatearPeriodo($info['duracion_bimestres'], $info['lapso_academico']),
    'horas' => $info['total_horas'],
    'modalidad' => str_replace([' (Clases síncronas/asíncronas)', ' (Clases sincronas/asincronas)'], '', formatoTexto($info['modalidad'])),
    'docente' => formatoNombre($info['nom_doc'] . ' ' . $info['ape_doc']),
    'firma_docente' => $info['firma_doc'],
    'coordinador' => formatoNombre($nombre_coordinador),
    'cargo_coordinador' => formatoTexto($cargo_coordinador),
    'firma_coordinador' => $firma_coordinador,
    'encargado' => formatoNombre($nombre_encargado),
    'cargo_encargado' => formatoTexto($cargo_encargado),
    'firma_encargado' => $firma_encargado,
    'fecha_actual' => date('d'),
    'mes_nombre' => $meses[date('n')-1],
    'anio_actual' => date('Y'),
    'hora_actual' => date('h:i a'),
    'total_participantes' => count($lista_alumnos),
    'aprobados' => $total_aprobados,
    'reprobados' => $total_reprobados,
    'columnas_evaluacion' => $columnas,
    'alumnos' => $lista_alumnos,
    'img_encabezado' => $img_encabezado,
    'img_pie' => $img_pie
];

// --- 6. REGISTRAR ACTA EN BD Y OBTENER FECHA FORENSE ---
// Buscar si ya existe el acta para no sobreescribir la fecha de generación a menos que lo pida Rol 4
$stmt_check_acta = $conn->prepare("SELECT id_acta, fecha_generacion, fecha_cierre FROM cursos.actas_cierre WHERE id_materia_bimestre = :m AND tipo_acta = :tipo LIMIT 1");
$stmt_check_acta->execute(['m' => $id_materia, 'tipo' => $tipo_acta]);
$acta_existente = $stmt_check_acta->fetch(PDO::FETCH_ASSOC);

$fecha_final_usada = time(); // Por defecto, ahora

// Verificar si el Rol 4 envió una fecha histórica forzada
if (isset($_GET['fecha_historica']) && !empty($_GET['fecha_historica'])) {
    if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 4) {
        $fecha_final_usada = strtotime($_GET['fecha_historica']);
    }
} else {
    // Si no envió fecha forzada, pero ya existía, usar la existente
    if ($acta_existente) {
        $fecha_final_usada = strtotime($acta_existente['fecha_cierre']);
    }
}

// Actualizamos o Insertamos el registro
$fecha_db_str = date('Y-m-d H:i:s', $fecha_final_usada);

if ($acta_existente) {
    // Actualizar (UPSERT)
    $stmt_update = $conn->prepare("UPDATE cursos.actas_cierre SET fecha_cierre = :f, fecha_generacion = :fd WHERE id_acta = :id");
    $stmt_update->execute(['f' => $fecha_db_str, 'fd' => date('Y-m-d', $fecha_final_usada), 'id' => $acta_existente['id_acta']]);
} else {
    // Insertar
    $stmt_acta = $conn->prepare("INSERT INTO cursos.actas_cierre (id_materia_bimestre, fecha_cierre, fecha_generacion, participantes_inscritos, participantes_aprobados, tipo_acta) 
                                 VALUES (:m, :f, :fd, :inscritos, :aprobados, :tipo)");
    $stmt_acta->execute([
        'm' => $id_materia,
        'f' => $fecha_db_str,
        'fd' => date('Y-m-d', $fecha_final_usada),
        'inscritos' => $data['total_participantes'],
        'aprobados' => $data['aprobados'],
        'tipo' => $tipo_acta
    ]);
}

// Inyectar en el FPDF
$data['fecha_actual'] = date('d', $fecha_final_usada);
$data['mes_nombre'] = $meses[date('n', $fecha_final_usada)-1];
$data['anio_actual'] = date('Y', $fecha_final_usada);
$data['hora_actual'] = date('h:i a', $fecha_final_usada);

// --- 7. GENERACIÓN CON FPDF ---
$pdf = new \FPDF();
$pdf->SetAutoPageBreak(true, 30);
require_once __DIR__ . '/../views/actas/acta_materia_fpdf.php';

$pdf->Output('I', 'Acta_Materia_' . str_replace(' ', '_', $data['materia']) . '.pdf');
