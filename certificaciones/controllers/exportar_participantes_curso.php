<?php
session_start();
require_once __DIR__ . '/../config/model.php';
require_once __DIR__ . '/../models/curso.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$id_curso = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_curso <= 0) {
    die("Curso inválido");
}

$db = new DB();
$cursoObj = new Curso($db);
$curso_info = $cursoObj->obtener_curso($id_curso);

if (!$curso_info) {
    die("Curso no encontrado");
}

// Fetch all enrolled participants with details
$sql = "SELECT u.id, u.cedula, u.nombre, u.apellido, c.nombre_curso, 
               cert.nota as nota_cert, cert.tomo, cert.folio
        FROM cursos.usuarios u
        INNER JOIN cursos.certificaciones cert ON u.id = cert.id_usuario
        INNER JOIN cursos.cursos c ON cert.curso_id = c.id_curso
        WHERE cert.curso_id = :curso_id";

$stmt = $db->getConn()->prepare($sql);
$stmt->execute([':curso_id' => $id_curso]);
$participantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Output as CSV
$filename = "participantes_" . preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($curso_info['nombre_curso'])) . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
// Add BOM to fix UTF-8 in Excel
echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');
fputcsv($output, ['Cédula', 'Nombre', 'Apellido', 'Curso', 'Nota', 'Tomo', 'Folio'], ';');

foreach ($participantes as $row) {
    // Obtener la nota real (maneja diplomados y notas globales)
    $nota = $cursoObj->obtener_nota($id_curso, $row['id']);
    if ($nota === null || $nota === '') {
        $nota = 'N/A';
    } else {
        if (is_numeric($nota)) {
            $nota = round((float)$nota); 
        }
    }

    $tomo = isset($row['tomo']) ? $row['tomo'] : '';
    $folio = isset($row['folio']) ? $row['folio'] : '';

    fputcsv($output, [
        '="' . $row['cedula'] . '"',
        mb_convert_case($row['nombre'], MB_CASE_TITLE, "UTF-8"),
        mb_convert_case($row['apellido'], MB_CASE_TITLE, "UTF-8"),
        mb_convert_case($row['nombre_curso'], MB_CASE_TITLE, "UTF-8"),
        '="' . $nota . '"',
        '="' . $tomo . '"',
        '="' . $folio . '"'
    ], ';');
}
fclose($output);
exit;
