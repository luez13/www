<?php
require_once '../controllers/init.php';
require_once '../config/model.php';

if (!tieneAcceso([5, 6])) {
    die('Acceso denegado.');
}

$db = new DB();
$pdo = $db->getConn();

$fechaDesde = isset($_GET['desde']) ? $_GET['desde'] : '';
$fechaHasta = isset($_GET['hasta']) ? $_GET['hasta'] : '';
$id_extension = $_SESSION['id_extension'];

$sql = "SELECT cp.fecha_pago, u.cedula, u.nombre, u.apellido, c.nombre_curso, cp.monto, cp.moneda, cp.numero_operacion, cp.banco_origen, cp.estado, cp.observacion,
        gestor.nombre as admin_nombre, gestor.apellido as admin_apellido
        FROM cursos.comprobantes_pago cp
        JOIN cursos.usuarios u ON cp.id_usuario = u.id
        JOIN cursos.cursos c ON cp.id_curso = c.id_curso
        LEFT JOIN cursos.usuarios gestor ON cp.id_admin_gestor = gestor.id
        WHERE cp.id_extension = :id_extension
        AND cp.estado IN ('Pendiente', 'Comprobado')";

$params = [':id_extension' => $id_extension];

if (!empty($fechaDesde)) {
    $sql .= " AND cp.fecha_pago >= :desde";
    $params[':desde'] = $fechaDesde;
}
if (!empty($fechaHasta)) {
    $sql .= " AND cp.fecha_pago <= :hasta";
    $params[':hasta'] = $fechaHasta;
}

$sql .= " ORDER BY cp.fecha_pago DESC, cp.estado ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generar CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Consolidado_Pagos_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');
// UTF-8 BOM para que Excel lea acentos
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

fputcsv($output, ['Fecha de Pago', 'Cédula', 'Nombre', 'Apellido', 'Curso/Diplomado', 'Monto', 'Moneda', 'Referencia/Método', 'Banco Origen', 'Estado', 'Observación', 'Gestionado Por'], ';');

foreach ($resultados as $fila) {
    $gestionado_por = $fila['admin_nombre'] ? ($fila['admin_nombre'] . ' ' . $fila['admin_apellido']) : 'No asignado';
    
    fputcsv($output, [
        $fila['fecha_pago'],
        "=\"" . $fila['cedula'] . "\"", 
        $fila['nombre'],
        $fila['apellido'],
        $fila['nombre_curso'],
        number_format($fila['monto'], 2, ',', '.'),
        $fila['moneda'],
        "=\"" . $fila['numero_operacion'] . "\"", 
        $fila['banco_origen'],
        $fila['estado'],
        $fila['observacion'],
        $gestionado_por
    ], ';');
}

fclose($output);
exit;
