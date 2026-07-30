<?php
// controllers/update_tomo_folio_materia.php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/../config/model.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_rol']) || !in_array($_SESSION['id_rol'], [3, 4])) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

$db = new DB();
$conn = $db->getConn();

$bulk = isset($_POST['bulk']) ? true : false;
$id_materia = isset($_POST['id_materia']) ? (int)$_POST['id_materia'] : 0;
$tomo = isset($_POST['tomo']) ? (int)$_POST['tomo'] : 0;
$folio = isset($_POST['folio']) ? (int)$_POST['folio'] : 0;

if ($id_materia <= 0 || $tomo <= 0 || $folio <= 0) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos o inválidos.']);
    exit;
}

try {
    $conn->beginTransaction();

    $stmt_check = $conn->prepare("SELECT id_cert_materia FROM cursos.certificaciones_materias WHERE id_usuario = :u AND id_materia_bimestre = :m");
    $stmt_insert = $conn->prepare("INSERT INTO cursos.certificaciones_materias (id_usuario, id_materia_bimestre, valor_unico, tomo, folio) VALUES (:u, :m, :v, :t, :f)");
    $stmt_update = $conn->prepare("UPDATE cursos.certificaciones_materias SET tomo = :t, folio = :f WHERE id_usuario = :u AND id_materia_bimestre = :m");

    if ($bulk) {
        $ids = isset($_POST['ids']) && is_array($_POST['ids']) ? $_POST['ids'] : [];
        if (empty($ids)) {
            echo json_encode(['success' => false, 'message' => 'No hay estudiantes seleccionados.']);
            exit;
        }

        foreach ($ids as $id_usuario) {
            $id_u = (int)$id_usuario;
            $stmt_check->execute(['u' => $id_u, 'm' => $id_materia]);
            if ($stmt_check->fetch()) {
                // Existe -> Update
                $stmt_update->execute(['u' => $id_u, 'm' => $id_materia, 't' => $tomo, 'f' => $folio]);
            } else {
                // No existe -> Insert
                $valor_unico = strtoupper(substr(md5(uniqid(rand(), true)), 0, 16));
                $stmt_insert->execute(['u' => $id_u, 'm' => $id_materia, 'v' => $valor_unico, 't' => $tomo, 'f' => $folio]);
            }
        }
    } else {
        $id_usuario = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : 0;
        if ($id_usuario <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de estudiante inválido.']);
            exit;
        }

        $stmt_check->execute(['u' => $id_usuario, 'm' => $id_materia]);
        if ($stmt_check->fetch()) {
            $stmt_update->execute(['u' => $id_usuario, 'm' => $id_materia, 't' => $tomo, 'f' => $folio]);
        } else {
            $valor_unico = strtoupper(substr(md5(uniqid(rand(), true)), 0, 16));
            $stmt_insert->execute(['u' => $id_usuario, 'm' => $id_materia, 'v' => $valor_unico, 't' => $tomo, 'f' => $folio]);
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Tomo y Folio guardados correctamente.']);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error en base de datos: ' . $e->getMessage()]);
}
