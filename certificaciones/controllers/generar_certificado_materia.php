<?php
// controllers/generar_certificado_materia.php
include 'init.php';
require_once __DIR__ . '/../config/model.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Endroid\QrCode\QrCode;

$db = new DB();
$conn = $db->getConn();

if (isset($_GET['valor_unico'])) {
    $valor_unico = $_GET['valor_unico'];
    
    $sql = "SELECT u.id as id_usuario, u.nombre, u.apellido, u.cedula,
                   m.id_materia_bimestre as id_materia, m.nombre_materia, m.total_horas, m.fecha_finalizacion, m.docente_id,
                   c.id_curso, c.nombre_curso, c.tipo_curso, c.nota_minima_aprobatoria, c.articulo_tipo_curso, c.promotor,
                   um.nota_regular, um.nota_recuperativa, um.estado as estado_materia
            FROM cursos.certificaciones_materias cm
            JOIN cursos.usuarios u ON cm.id_usuario = u.id
            JOIN cursos.materias_bimestre m ON cm.id_materia_bimestre = m.id_materia_bimestre
            JOIN cursos.usuario_materias um ON (u.id = um.id_usuario AND m.id_materia_bimestre = um.id_materia_bimestre)
            JOIN cursos.cursos c ON m.id_curso = c.id_curso
            WHERE cm.valor_unico = :vu";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute(['vu' => $valor_unico]);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$info) {
        die('Certificado no encontrado o valor único inválido.');
    }
    
    $id_materia = $info['id_materia'];
    
} else if (isset($_GET['id_materia']) && isset($_GET['cedula'])) {
    $id_materia = (int)$_GET['id_materia'];
    $cedula = $_GET['cedula'];

    $sql = "SELECT u.id as id_usuario, u.nombre, u.apellido, u.cedula,
                   m.nombre_materia, m.total_horas, m.docente_id,
                   c.id_curso, c.nombre_curso, c.tipo_curso, c.promotor,
                   um.nota_regular, um.nota_recuperativa, um.estado as estado_materia,
                   ac.fecha_cierre as fecha_acta_materia
            FROM cursos.usuarios u
            JOIN cursos.usuario_materias um ON u.id = um.id_usuario
            JOIN cursos.materias_bimestre m ON um.id_materia_bimestre = m.id_materia_bimestre
            JOIN cursos.cursos c ON m.id_curso = c.id_curso
            LEFT JOIN cursos.actas_cierre ac ON ac.id_materia_bimestre = m.id_materia_bimestre AND ac.tipo_acta = 'Regular'
            WHERE u.cedula = :cedula AND m.id_materia_bimestre = :id_materia";

    $stmt = $conn->prepare($sql);
    $stmt->execute(['cedula' => $cedula, 'id_materia' => $id_materia]);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$info) {
        die('No se encontraron registros de aprobación para esta materia y cédula.');
    }
    
    if (empty($info['fecha_acta_materia'])) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<body style='background-color: #f8f9fc; display:flex; justify-content:center; align-items:center; height:100vh; margin:0;'>
              <script>
                Swal.fire({
                    icon: 'warning',
                    title: 'Acta no cerrada',
                    text: 'La materia aún se encuentra en proceso de cierre. Por favor revisa más tarde cuando el acta sea emitida.',
                    confirmButtonText: 'Entendido'
                }).then(() => { window.close(); });
              </script></body>";
        exit;
    }
} else {
    die('Faltan parámetros requeridos (valor_unico O id_materia+cedula).');
}

$nota_final = max((float)$info['nota_regular'], (float)$info['nota_recuperativa']);
$nota_minima = 12;

if ($nota_final < $nota_minima) {
    die('El participante no aprobó esta materia, por lo que no puede generar el certificado.');
}

// 2. Gestionar el valor único (certificaciones_materias)
$stmt_check = $conn->prepare("SELECT valor_unico, tomo, folio FROM cursos.certificaciones_materias WHERE id_usuario = :u AND id_materia_bimestre = :m");
$stmt_check->execute(['u' => $info['id_usuario'], 'm' => $id_materia]);
$cert_row = $stmt_check->fetch(PDO::FETCH_ASSOC);

$valor_unico = $cert_row ? $cert_row['valor_unico'] : null;
$tomo_db = $cert_row ? $cert_row['tomo'] : '';
$folio_db = $cert_row ? $cert_row['folio'] : '';

if (!$valor_unico) {
    $valor_unico = strtoupper(substr(md5(uniqid(rand(), true)), 0, 16)); // Generar uno de 16 caracteres
    $stmt_insert = $conn->prepare("INSERT INTO cursos.certificaciones_materias (id_usuario, id_materia_bimestre, valor_unico) VALUES (:u, :m, :v)");
    $stmt_insert->execute(['u' => $info['id_usuario'], 'm' => $id_materia, 'v' => $valor_unico]);
}

// 3. Preparar los datos para el certificado
$data = [];
$data['nombreEstudiante'] = mb_convert_case(mb_strtolower($info['nombre'] . ' ' . $info['apellido'], 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
$data['cedula'] = $info['cedula'];
$data['paso'] = 'aprobado';
$data['articulo_tipo_curso'] = 'la Unidad Curricular';
$data['tipo_curso'] = '';
$data['nombre_curso'] = $info['nombre_materia'] . ' (Correspondiente a ' . $info['nombre_curso'] . ')';
$data['nota'] = $nota_final;
$data['fechaInscripcion'] = $info['fecha_acta_materia'];
$data['mostrar_firmas'] = true;
$data['titulo'] = '';
$data['titulo'] = '';
$data['tomo'] = $tomo_db;
$data['folio'] = $folio_db;
$data['horas_cronologicas'] = $info['total_horas'] ? $info['total_horas'] : '';
$data['inicioMesCurso'] = $info['fecha_acta_materia'];
$data['fechaFinalizacionCurso'] = $info['fecha_acta_materia'];

// Lógica de validación QR
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}" . explode('/controllers/', $_SERVER['PHP_SELF'])[0];
$data['certificadoUrl'] = $baseUrl . "/controllers/generar_certificado_materia.php?valor_unico={$valor_unico}";
$data['valor_unico'] = $valor_unico;

// Fondo
$data['fondoPath'] = realpath(__DIR__ . '/../public/assets/img/certificado_logo_ministerio.png');
if (!$data['fondoPath'] || !file_exists($data['fondoPath'])) {
    $data['fondoPath'] = realpath(__DIR__ . '/../public/assets/img/certificado_logo_ministerio.jpg');
    if (!$data['fondoPath'] || !file_exists($data['fondoPath'])) {
        $data['fondoPath'] = realpath(__DIR__ . '/../public/assets/img/certificado_base.jpg');
    }
}

// Obtener firmas configuradas para el curso (diplomado) base
require_once __DIR__ . '/../models/curso.php';
$cursoModel = new Curso($db);
$firmantes = $cursoModel->obtenerFirmasCurso($info['id_curso']);

// Si la materia tiene un docente asignado, reemplazar al facilitador del diplomado
if (!empty($info['docente_id'])) {
    $stmt_doc = $conn->prepare("SELECT nombre, apellido, titulo, firma_digital FROM cursos.usuarios WHERE id = :id");
    $stmt_doc->execute(['id' => $info['docente_id']]);
    $docente_db = $stmt_doc->fetch(PDO::FETCH_ASSOC);
    
    if ($docente_db) {
        $firma_base64 = null;
        if (!empty($docente_db['firma_digital'])) {
            $ruta_firma = dirname(__DIR__) . '/public/assets/firmas/' . basename($docente_db['firma_digital']);
            if (file_exists($ruta_firma)) {
                $firma_base64 = 'data:' . mime_content_type($ruta_firma) . ';base64,' . base64_encode(file_get_contents($ruta_firma));
            }
        }
        
        $primer_nombre = explode(' ', trim($docente_db['nombre']))[0];
        $primer_apellido = explode(' ', trim($docente_db['apellido']))[0];
        $nombre_completo = $primer_nombre . ' ' . $primer_apellido;
        
        foreach ($firmantes as &$f) {
            if (!empty($f['es_promotor']) || stripos($f['cargo'], 'Facilitador') !== false || stripos($f['cargo'], 'Promotor') !== false) {
                $f['nombre'] = mb_convert_case(mb_strtolower($nombre_completo, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                $f['titulo'] = isset($docente_db['titulo']) ? $docente_db['titulo'] : '';
                $f['cargo'] = 'Facilitador'; // Opcional: Estandarizar a Facilitador
                if ($firma_base64) {
                    $f['firma_base64'] = $firma_base64;
                } else {
                    $f['firma_base64'] = null;
                    $f['cargo'] .= ' (Firma no encontrada)';
                }
            }
        }
        unset($f); // VERY IMPORTANT: Remove reference to last element to avoid corruption in later loops
        
        $data['firmantes'] = $firmantes;
    }
}

$data['firmantes'] = $firmantes;

// Obtener las evaluaciones de la materia para mostrarlas en el reverso (como si fuesen módulos)
$stmt_eval = $conn->prepare("SELECT nombre_actividad as nombre_modulo, ROW_NUMBER() OVER (ORDER BY id_actividad_config ASC) as numero FROM cursos.actividades_config WHERE id_materia_bimestre = :id_materia");
$stmt_eval->execute(['id_materia' => $id_materia]);
$data['modulos'] = $stmt_eval->fetchAll(PDO::FETCH_ASSOC);

// Código QR
$qrCode = new QrCode($data['certificadoUrl']);
$qrCode->setSize(300);
$qrCode->setMargin(10);
$data['qrImageBase64'] = base64_encode($qrCode->writeString());

// ==========================================
// INICIA FPDF
// ==========================================

if (!defined('FPDF_FONTPATH')) {
    define('FPDF_FONTPATH', realpath(__DIR__ . '/../public/assets/vendor/'));
}

$pdf = new \FPDF('L', 'mm', 'Letter');
$pdf->SetAutoPageBreak(false);

require __DIR__ . '/../views/certificados/certificado_logo_ministerio.php';

$pdf->Output('I', 'Certificado_' . $info['cedula'] . '_' . str_replace(' ', '_', $info['nombre_materia']) . '.pdf');
