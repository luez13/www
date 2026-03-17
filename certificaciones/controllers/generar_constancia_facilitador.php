<?php
// controllers/generar_constancia_facilitador.php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Acceso denegado.");
}

require_once('../config/model.php');

$db = new DB();

$id_materia = isset($_GET['id_materia']) ? intval($_GET['id_materia']) : 0;
if (!$id_materia)
    die("Falta ID de la materia");

// Obtener datos de la materia, curso y docente
$sql = "SELECT m.nombre_materia, m.duracion_bimestres, m.total_horas, m.modalidad, m.lapso_academico,
               c.nombre_curso, c.tipo_curso, c.inicio_mes, c.fecha_finalizacion,
               u.nombre as nombre_docente, u.apellido as apellido_docente, u.cedula 
        FROM cursos.materias_bimestre m
        JOIN cursos.cursos c ON m.id_curso = c.id_curso
        JOIN cursos.usuarios u ON m.docente_id = u.id
        WHERE m.id_materia_bimestre = :id_materia";

$stmt = $db->getConn()->prepare($sql);
$stmt->execute(['id_materia' => $id_materia]);
$datos = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$datos)
    die("Datos no encontrados.");

// --- INICIO: Lógica para obtener el Firmante Dinámico ---
$nombre_coordinador = "Coordinación de Formación Permanente"; // Valor por defecto si falla la BD

try {
    // 1. Obtener los IDs de los cargos y datos de contacto configurados por defecto
    $stmtConfig = $db->getConn()->prepare("SELECT clave_config, valor_config FROM cursos.config_sistema WHERE clave_config IN ('ID_CARGO_COORD_FP_POR_DEFECTO', 'ID_CARGO_VICERRECTORADO_POR_DEFECTO', 'CORREO_CONTACTO_POR_DEFECTO', 'TELEFONO_CONTACTO_POR_DEFECTO')");
    $stmtConfig->execute();
    $configs = $stmtConfig->fetchAll(PDO::FETCH_KEY_PAIR);

    $id_cargo_coord = isset($configs['ID_CARGO_COORD_FP_POR_DEFECTO']) ? $configs['ID_CARGO_COORD_FP_POR_DEFECTO'] : null;
    $id_cargo_vicerrectorado = isset($configs['ID_CARGO_VICERRECTORADO_POR_DEFECTO']) ? $configs['ID_CARGO_VICERRECTORADO_POR_DEFECTO'] : null;

    $correo_contacto = isset($configs['CORREO_CONTACTO_POR_DEFECTO']) ? $configs['CORREO_CONTACTO_POR_DEFECTO'] : 'techo.uptai@gmail.com';
    $telefono_contacto = isset($configs['TELEFONO_CONTACTO_POR_DEFECTO']) ? $configs['TELEFONO_CONTACTO_POR_DEFECTO'] : '0426-5108012';

    $stmtCargo = $db->getConn()->prepare("SELECT nombre, apellido, titulo FROM cursos.cargos WHERE id_cargo = :id");

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
    error_log("Error obteniendo firmantes constancia facilitador: " . $e->getMessage());
}
// --- FIN: Lógica Firmante Dinámico ---

$nombre_curso = $datos['nombre_curso'];
$nombre_materia = $datos['nombre_materia'];
$nombre_docente = trim($datos['nombre_docente'] . ' ' . $datos['apellido_docente']);
$cedula = $datos['cedula'];
$lapso_academico = $datos['lapso_academico'] ?? '1';

$nombre_curso_capitalizado = ucwords(strtolower($nombre_curso));
$nombre_materia_capitalizado = ucwords(strtolower($nombre_materia));

$piePagina = realpath(__DIR__ . '/../public/assets/img/piePagina.jpg');
$encabezado = realpath(__DIR__ . '/../public/assets/img/vector membrete 1-01.png');

// Empaquetar datos para la vista
$data = [
    'bannerPath' => 'data:image/png;base64,' . base64_encode(file_get_contents($encabezado)),
    'footerPath' => 'data:image/jpeg;base64,' . base64_encode(file_get_contents($piePagina)),
    'nombre_curso' => $nombre_curso_capitalizado,
    'nombre_materia' => $nombre_materia_capitalizado,
    'nombre_docente' => $nombre_docente,
    'cedula' => $cedula,
    'lapso_academico' => $lapso_academico,
    'correo_contacto' => $correo_contacto ?? 'No disponible',
    'telefono_contacto' => $telefono_contacto ?? 'No disponible',
    'nombre_coordinador' => $nombre_coordinador,
    'nombre_vicerrector' => isset($nombre_vicerrector) ? $nombre_vicerrector : 'Vicerrectorado Académico'
];

// --- GENERACIÓN DEL PDF CON DOMPDF ---
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Helvetica');

$dompdf = new Dompdf($options);

// Renderizar la vista pasando los datos
ob_start();
include '../views/certificados/constancia_docente_base.php';
$html = ob_get_clean();

$dompdf->loadHtml($html);

// Configurar orientación y tamaño
$dompdf->setPaper('letter', 'portrait');

// Renderizar PDF
$dompdf->render();

// Forzar apertura en el navegador
$dompdf->stream('Constancia_Docente_' . $cedula . '.pdf', [
    'Attachment' => false
]);