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

// --- 1. PROCESAMIENTO DE IMÁGENES (RUTAS ABSOLUTAS) ---
$img_encabezado = realpath(__DIR__ . '/../public/assets/img/encabezado.jpg');
$img_pie = realpath(__DIR__ . '/../public/assets/img/piePagina.jpg');

// --- 2. FUNCIONES DE FORMATO (IDÉNTICAS PARA MANTENER DISEÑO) ---
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
                    c.nombre_curso, c.id_curso,
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
    $estado = ($definitiva >= 12) ? 'APROBADO' : 'REPROBADO';
    if ($estado === 'APROBADO') $total_aprobados++; else $total_reprobados++;

    $lista_alumnos[] = [
        'cedula' => $al['cedula'],
        'nombre' => formatoNombre($al['apellido'] . ' ' . $al['nombre']),
        'notas_parciales' => $notas_alumno,
        'definitiva' => $definitiva,
        'estado' => $estado
    ];
}

$meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];

$data = [
    'curso' => formatoTexto($info['nombre_curso']), 
    'materia' => formatoTexto($info['nombre_materia']),
    'duracion' => formatoTexto($info['duracion_bimestres']),
    'periodo_titulo' => formatearPeriodo($info['duracion_bimestres'], $info['lapso_academico']),
    'horas' => $info['total_horas'],
    'modalidad' => formatoTexto($info['modalidad']),
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
    'columnas_evaluacion' => array_map(function($p) { 
        return formatoTexto($p['nombre_actividad']) . " (" . floatval($p['ponderacion_porcentaje']) . "%)"; 
    }, $plan),
    'alumnos' => $lista_alumnos,
    'img_encabezado' => $img_encabezado,
    'img_pie' => $img_pie
];

// --- GENERACIÓN CON FPDF ---
$pdf = new \FPDF();
$pdf->SetAutoPageBreak(true, 30);
require_once __DIR__ . '/../views/actas/acta_materia_fpdf.php';

$pdf->Output('I', 'Acta_Materia_' . str_replace(' ', '_', $data['materia']) . '.pdf');
