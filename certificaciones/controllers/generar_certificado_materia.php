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
                   c.nombre_curso, c.tipo_curso, c.nota_minima_aprobatoria, c.articulo_tipo_curso, c.promotor,
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
                   m.nombre_materia, m.total_horas, m.fecha_finalizacion, m.docente_id,
                   c.nombre_curso, c.tipo_curso, c.nota_minima_aprobatoria, c.articulo_tipo_curso, c.promotor,
                   um.nota_regular, um.nota_recuperativa, um.estado as estado_materia
            FROM cursos.usuarios u
            JOIN cursos.usuario_materias um ON u.id = um.id_usuario
            JOIN cursos.materias_bimestre m ON um.id_materia_bimestre = m.id_materia_bimestre
            JOIN cursos.cursos c ON m.id_curso = c.id_curso
            WHERE u.cedula = :cedula AND m.id_materia_bimestre = :id_materia";

    $stmt = $conn->prepare($sql);
    $stmt->execute(['cedula' => $cedula, 'id_materia' => $id_materia]);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$info) {
        die('No se encontraron registros de aprobación para esta materia y cédula.');
    }
} else {
    die('Faltan parámetros requeridos (valor_unico O id_materia+cedula).');
}

$nota_final = max((float)$info['nota_regular'], (float)$info['nota_recuperativa']);
$nota_minima = (float)$info['nota_minima_aprobatoria'];

if ($nota_final < $nota_minima) {
    die('El participante no aprobó esta materia, por lo que no puede generar el certificado.');
}

// 2. Gestionar el valor único (certificaciones_materias)
$stmt_check = $conn->prepare("SELECT valor_unico FROM cursos.certificaciones_materias WHERE id_usuario = :u AND id_materia_bimestre = :m");
$stmt_check->execute(['u' => $info['id_usuario'], 'm' => $id_materia]);
$valor_unico = $stmt_check->fetchColumn();

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
$data['fechaInscripcion'] = $info['fecha_finalizacion'] ? $info['fecha_finalizacion'] : date('Y-m-d');

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

// Obtener firma del promotor (coordinador) para el certificado de materia
$stmt_firmante = $conn->prepare("SELECT nombre, apellido, firma_digital FROM cursos.usuarios WHERE id = :id");
$stmt_firmante->execute(['id' => $info['promotor']]);
$firmante_db = $stmt_firmante->fetch(PDO::FETCH_ASSOC);

$firmantes = [];
if ($firmante_db) {
    $firma_base64 = null;
    if (!empty($firmante_db['firma_digital'])) {
        $ruta_firma = dirname(__DIR__) . '/public/assets/firmas/' . basename($firmante_db['firma_digital']);
        if (file_exists($ruta_firma)) {
            $firma_base64 = 'data:' . mime_content_type($ruta_firma) . ';base64,' . base64_encode(file_get_contents($ruta_firma));
        }
    }
    
    $firmantes[] = [
        'nombre' => mb_convert_case(mb_strtolower($firmante_db['nombre'] . ' ' . $firmante_db['apellido'], 'UTF-8'), MB_CASE_TITLE, 'UTF-8'),
        'cargo' => 'Coordinación del Diplomado',
        'firma_base64' => $firma_base64,
        'posicion_codigo' => 'P1',
        'pagina' => 1
    ];
}
$data['firmantes'] = $firmantes;

// Código QR
$qrCode = new QrCode($data['certificadoUrl']);
$qrCode->setSize(300);
$qrCode->setMargin(10);
$data['qrImageBase64'] = base64_encode($qrCode->writeString());

// Generar PDF
$pdf = new \FPDF('L', 'mm', 'Letter'); // Landscape, Milimeters, Letter
$pdf->SetAutoPageBreak(false);

require __DIR__ . '/../views/certificados/certificado_logo_ministerio.php';

$pdf->Output('I', 'Certificado_' . $info['cedula'] . '_' . str_replace(' ', '_', $info['nombre_materia']) . '.pdf');
