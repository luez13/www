<?php
// controllers/auditoria_controlador.php

require_once 'init.php';
include_once '../config/model.php';

// Seguridad: SOLO Súper Administrador (Rol 4)
if (!isset($_SESSION['user_id']) || $_SESSION['id_rol'] != 4) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Acceso denegado. Se requiere nivel de Súper Administrador.']);
    exit;
}

$db = new DB();
$pdo = $db->getConn();

// DataTables variables
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$searchValue = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';

// Columnas para ordenar (DataTables pasa el índice de la columna)
$columns = [
    0 => 'a.id_auditoria',
    1 => 'u.nombre',
    2 => 'a.accion',
    3 => 'a.tabla_afectada',
    4 => 'a.fecha'
];

$orderColumnIndex = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 4;
$orderDir = isset($_POST['order'][0]['dir']) && $_POST['order'][0]['dir'] === 'asc' ? 'ASC' : 'DESC';
$orderBy = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : 'a.fecha';

// Base SQL
$baseSql = "FROM cursos.auditoria a 
            LEFT JOIN cursos.usuarios u ON CASE WHEN a.usuario ~ '^-?[0-9]+$' THEN a.usuario::integer ELSE NULL END = u.id";

$whereClauses = [];
$params = [];

if (!empty($searchValue)) {
    $whereClauses[] = "(a.accion ILIKE :search OR a.tabla_afectada ILIKE :search OR u.nombre ILIKE :search OR u.apellido ILIKE :search OR a.usuario ILIKE :search)";
    $params[':search'] = "%{$searchValue}%";
}

$whereSql = '';
if (count($whereClauses) > 0) {
    $whereSql = "WHERE " . implode(' AND ', $whereClauses);
}

try {
    // 1. Obtener total de registros sin filtrar
    $stmtTotal = $pdo->query("SELECT COUNT(a.id_auditoria) " . $baseSql);
    $recordsTotal = $stmtTotal->fetchColumn();

    // 2. Obtener total de registros filtrados
    $stmtFiltered = $pdo->prepare("SELECT COUNT(a.id_auditoria) " . $baseSql . " " . $whereSql);
    foreach ($params as $key => $val) {
        $stmtFiltered->bindValue($key, $val);
    }
    $stmtFiltered->execute();
    $recordsFiltered = $stmtFiltered->fetchColumn();

    // 3. Obtener los datos paginados
    $dataSql = "SELECT a.id_auditoria, u.nombre as admin_nombre, u.apellido as admin_apellido, a.usuario as admin_id_raw, a.accion, a.tabla_afectada, a.fecha, a.dato_previo, a.dato_modificado 
                " . $baseSql . " 
                " . $whereSql . " 
                ORDER BY " . $orderBy . " " . $orderDir . " 
                LIMIT :length OFFSET :start";

    $stmtData = $pdo->prepare($dataSql);
    foreach ($params as $key => $val) {
        $stmtData->bindValue($key, $val);
    }
    $stmtData->bindValue(':length', $length, PDO::PARAM_INT);
    $stmtData->bindValue(':start', $start, PDO::PARAM_INT);
    $stmtData->execute();

    $resultados = $stmtData->fetchAll(PDO::FETCH_ASSOC);

    // Formatear la salida para DataTables
    $data = [];
    foreach ($resultados as $row) {
        $gestor = !empty($row['admin_nombre']) ? htmlspecialchars($row['admin_nombre'] . ' ' . $row['admin_apellido']) : "Usuario ID: " . htmlspecialchars($row['admin_id_raw']);
        
        $btnDetalles = '<button type="button" class="btn btn-info btn-sm btn-detalles text-white" data-previo="' . htmlspecialchars((string)$row['dato_previo'], ENT_QUOTES, 'UTF-8') . '" data-nuevo="' . htmlspecialchars((string)$row['dato_modificado'], ENT_QUOTES, 'UTF-8') . '"><i class="fas fa-eye me-1"></i> Detalles</button>';

        // Colores para la acción
        $accionBadge = '<span class="badge bg-secondary">'.htmlspecialchars($row['accion']).'</span>';
        if (strtoupper($row['accion']) === 'INSERT') {
            $accionBadge = '<span class="badge bg-success">INSERT</span>';
        } elseif (strtoupper($row['accion']) === 'UPDATE') {
            $accionBadge = '<span class="badge bg-primary">UPDATE</span>';
        } elseif (strtoupper($row['accion']) === 'DELETE') {
            $accionBadge = '<span class="badge bg-danger">DELETE</span>';
        }

        $data[] = [
            $row['id_auditoria'],
            $gestor,
            $accionBadge,
            htmlspecialchars($row['tabla_afectada']),
            htmlspecialchars($row['fecha']),
            $btnDetalles
        ];
    }

    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => intval($recordsTotal),
        "recordsFiltered" => intval($recordsFiltered),
        "data" => $data
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => "Error interno: " . $e->getMessage()
    ]);
}
?>
