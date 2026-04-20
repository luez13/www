<?php
// controllers/generar_acta_cierre_fpdf.php

include 'init.php';
require_once __DIR__ . '/../config/model.php';
require_once __DIR__ . '/../models/curso.php';
require_once __DIR__ . '/../vendor/autoload.php';

$db = new DB();
$curso = new Curso($db);

// Datos del Diplomado (Hardcodeados para el ejemplo según el controlador jsPDF original)
$id_curso = isset($_GET['id_curso']) ? (int)$_GET['id_curso'] : 0;

// Aquí se debería obtener la data real de la DB, pero seguimos la lógica del auditor de mantener fidelidad al original
$nombre_diplomado = "DIPLOMADO DOCENTE UNIVERSITARIO"; 
$nombre_materia = "Estrategias de enseñanza y aprendizaje"; 
$duracion = "1 Bimestre"; 
$total_horas = "48 horas"; 
$modalidad = "Online por la plataforma Moodle de la U.P.T.A.I.E.T."; 
$docente_responsable = "Dra. Yolly Soto"; 
$inscritos = 26; 
$aprobados = 22; 
$no_aprobaron = $inscritos - $aprobados; 

// Firmas
$firma_vicerrector = "Msc. Jhoan Sánchez"; 
$cargo_vicerrector = "Vice-Rector Académico"; 
$firma_coord = "Esp. Yoselin Espíndola";
$cargo_coord = "Coord. Formación Permanente";

$dia_cierre = date("d");
$mes_cierre = "octubre"; 
$anio_cierre = date("Y");
$hora_cierre = "09:48 am";

$img_encabezado = realpath(__DIR__ . '/../public/assets/img/encabezado.jpg');
$img_pie = realpath(__DIR__ . '/../public/assets/img/piePagina.jpg');

$data = [
    'nombre_diplomado' => $nombre_diplomado,
    'nombre_materia' => $nombre_materia,
    'duracion' => $duracion,
    'total_horas' => $total_horas,
    'modalidad' => $modalidad,
    'docente_responsable' => $docente_responsable,
    'inscritos' => $inscritos,
    'aprobados' => $aprobados,
    'no_aprobaron' => $no_aprobaron,
    'firma_vicerrector' => $firma_vicerrector,
    'cargo_vicerrector' => $cargo_vicerrector,
    'firma_coord' => $firma_coord,
    'cargo_coord' => $cargo_coord,
    'dia_cierre' => $dia_cierre,
    'mes_cierre' => $mes_cierre,
    'anio_cierre' => $anio_cierre,
    'hora_cierre' => $hora_cierre,
    'img_encabezado' => $img_encabezado,
    'img_pie' => $img_pie
];

// --- GENERACIÓN CON FPDF ---
$pdf = new \FPDF();
$pdf->SetAutoPageBreak(true, 30);
require_once __DIR__ . '/../views/actas/acta_cierre_fpdf.php';

$pdf->Output('I', 'Acta_Cierre_Diplomado.pdf');
