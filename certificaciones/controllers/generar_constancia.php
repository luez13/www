<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once('../config/model.php');
require_once('../models/curso.php');

// Crear una instancia de la clase DB
$db = new DB();

// Crear una instancia de la clase Curso
$curso = new Curso($db);

if (!isset($_SESSION['user_id'])) {
    die("Error: Debe iniciar sesión para generar su constancia.");
}

if (isset($_GET['id_curso'])) {
    $id_curso = $_GET['id_curso'];
    $id_usuario = $_SESSION['user_id'];
    
    // Evaluar que el usuario sea Facilitador o Participante
    $datos_usuario = $curso->obtener_datos_constancia_dinamica($id_curso, $id_usuario);
    
    if(!$datos_usuario) {
        die("Error: Usted no es facilitador ni está inscrito formalmente en este curso.");
    }

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

    $nombre_curso = $datos_usuario['nombre_curso'];
    $nombre_promotor = $datos_usuario['nombre'] . ' ' . $datos_usuario['apellido'];
    $horas_cronologicas = $datos_usuario['horas_cronologicas'];
    $cedula = $datos_usuario['cedula'];
    $rol_usuario = $datos_usuario['rol'];
}

$piePagina = realpath(__DIR__ . '/../public/assets/img/piePagina.jpg');
$encabezado = realpath(__DIR__ . '/../public/assets/img/vector membrete 1-01.png');

// Empaquetar datos para la vista
$data = [
    'bannerPath' => 'data:image/png;base64,' . base64_encode(file_get_contents($encabezado)),
    'footerPath' => 'data:image/jpeg;base64,' . base64_encode(file_get_contents($piePagina)),
    'nombre_curso' => $nombre_curso,
    'nombre_promotor' => $nombre_promotor,
    'cedula' => $cedula,
    'rol' => isset($rol_usuario) ? $rol_usuario : 'Participante',
    'correo_contacto' => isset($correo_contacto) ? $correo_contacto : 'No disponible',
    'telefono_contacto' => isset($telefono_contacto) ? $telefono_contacto : 'No disponible',
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
include '../views/certificados/constancia_base.php';
$html = ob_get_clean();

$dompdf->loadHtml($html);

// Configurar orientación y tamaño
$dompdf->setPaper('letter', 'portrait');

// Renderizar PDF
$dompdf->render();

// Forzar apertura en el navegador
$dompdf->stream('Constancia_' . $rol_usuario . '_' . $cedula . '.pdf', [
    'Attachment' => false
]);