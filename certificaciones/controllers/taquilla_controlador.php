<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/model.php';
require_once 'autenticacion.php';

header('Content-Type: application/json; charset=utf-8');

// Validar acceso: Solo Roles 4 (ADMIN) y 5 (ANALISTA)
if (!isset($_SESSION['user_id']) || !tieneAcceso([4, 5])) {
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado.']);
    exit;
}

if (!isset($_POST['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Acción no especificada.']);
    exit;
}

$action = $_POST['action'];
$db = new DB();
$pdo = $db->getConn();
$id_extension_activa = $_SESSION['id_extension'];

try {
    switch ($action) {
        case 'buscar_estudiante':
            if (empty($_POST['cedula'])) throw new Exception("Cédula vacía.");
            $cedula = trim($_POST['cedula']);
            
            $stmt = $pdo->prepare("SELECT id, nombre, apellido, correo FROM cursos.usuarios WHERE cedula = :cedula LIMIT 1");
            $stmt->execute([':cedula' => $cedula]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Verificar si pertenece a la sede actual, si no, asignarlo (JIT silencioso para usuarios existentes)
                $stmtCheck = $pdo->prepare("SELECT 1 FROM cursos.usuarios_extensiones WHERE id_usuario = :id_usuario AND id_extension = :id_extension");
                $stmtCheck->execute([':id_usuario' => $user['id'], ':id_extension' => $id_extension_activa]);
                if (!$stmtCheck->fetch()) {
                    $stmtAssign = $pdo->prepare("INSERT INTO cursos.usuarios_extensiones (id_usuario, id_extension) VALUES (:id, :ext)");
                    $stmtAssign->execute([':id' => $user['id'], ':ext' => $id_extension_activa]);
                }
                echo json_encode(['status' => 'found', 'usuario' => $user]);
            } else {
                echo json_encode(['status' => 'not_found']);
            }
            break;
            
        case 'registro_jit':
            $cedula = trim($_POST['cedula']);
            $nombre = trim($_POST['nombre']);
            $apellido = trim($_POST['apellido']);
            $correo = strtolower(trim($_POST['correo']));
            
            if (empty($cedula) || empty($nombre) || empty($apellido) || empty($correo)) {
                throw new Exception("Todos los campos son obligatorios.");
            }
            
            // Auto-generar password y token
            $password = password_hash($cedula, PASSWORD_DEFAULT); // Default password as cedula
            $token = md5($correo . time());
            
            $pdo->beginTransaction();
            
            // 1. Crear usuario (Rol 1 por defecto para alumnos)
            $stmt = $pdo->prepare('INSERT INTO cursos.usuarios (nombre, apellido, correo, password, cedula, token, confirmado, id_rol) VALUES (:nombre, :apellido, :correo, :password, :cedula, :token, true, 1) RETURNING id');
            $stmt->execute([
                ':nombre' => $nombre,
                ':apellido' => $apellido,
                ':correo' => $correo,
                ':password' => $password,
                ':cedula' => $cedula,
                ':token' => $token
            ]);
            $nuevo_id = $stmt->fetchColumn();
            
            // 2. Asignar JIT a la sede de la taquilla
            $stmtAssign = $pdo->prepare("INSERT INTO cursos.usuarios_extensiones (id_usuario, id_extension) VALUES (:id_user, :id_ext)");
            $stmtAssign->execute([':id_user' => $nuevo_id, ':id_ext' => $id_extension_activa]);
            
            $pdo->commit();
            
            echo json_encode([
                'status' => 'success', 
                'usuario' => [
                    'id' => $nuevo_id,
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'correo' => $correo
                ]
            ]);
            break;
            
        case 'listar_cursos':
            // Cursos que pertenecen a la sede activa del cajero
            $stmt = $pdo->prepare("SELECT id_curso, nombre_curso, tipo_curso FROM cursos.cursos WHERE id_extension = :id_extension AND estado = true ORDER BY nombre_curso ASC");
            $stmt->execute([':id_extension' => $id_extension_activa]);
            $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['status' => 'success', 'cursos' => $cursos]);
            break;
            
        case 'registrar_pago_taquilla':
            $id_usuario = (int)$_POST['id_usuario'];
            $id_curso = (int)$_POST['id_curso'];
            $numero_operacion = trim($_POST['numero_operacion']);
            $monto = (float)$_POST['monto'];
            $moneda = trim($_POST['moneda']);
            $observacion = isset($_POST['observacion']) ? trim($_POST['observacion']) : '';
            
            if (empty($id_usuario) || empty($id_curso) || empty($numero_operacion) || $monto <= 0 || empty($moneda)) {
                throw new Exception("Datos de pago incompletos o inválidos.");
            }
            
            // Verificar que el curso pertenece a la sede actual
            $stmtCheck = $pdo->prepare("SELECT 1 FROM cursos.cursos WHERE id_curso = :id_curso AND id_extension = :id_ext");
            $stmtCheck->execute([':id_curso' => $id_curso, ':id_ext' => $id_extension_activa]);
            if (!$stmtCheck->fetch()) {
                throw new Exception("El concepto a pagar no pertenece a su Sede.");
            }
            
            // Obtener la cuenta bancaria CAJA FISICA para esta sede
            $stmtCuenta = $pdo->prepare("SELECT id_cuenta FROM cursos.cuentas_bancarias WHERE id_extension = :id_ext AND banco = 'CAJA FÍSICA' LIMIT 1");
            $stmtCuenta->execute([':id_ext' => $id_extension_activa]);
            $cuenta_caja = $stmtCuenta->fetch(PDO::FETCH_ASSOC);
            if (!$cuenta_caja) {
                throw new Exception("Error contable: No existe una CAJA FÍSICA configurada para esta sede.");
            }
            $id_cuenta_destino = $cuenta_caja['id_cuenta'];
            
            // Registrar pago AUTO-APROBADO
            $stmt = $pdo->prepare("INSERT INTO cursos.comprobantes_pago 
                (id_usuario, id_curso, numero_operacion, banco_origen, monto, estado, fecha_pago, observacion, moneda, id_admin_gestor, fecha_gestion, id_cuenta_destino, id_extension) 
                VALUES 
                  (:id_usuario, :id_curso, :numero_operacion, 'Taquilla Física', :monto, 'Pendiente', CURRENT_DATE, :observacion, :moneda, :id_admin, CURRENT_TIMESTAMP, :id_cuenta_destino, (SELECT id_extension FROM cursos.cursos WHERE id_curso = :id_curso LIMIT 1))");
            
            $stmt->execute([
                ':id_usuario' => $id_usuario,
                ':id_curso' => $id_curso,
                ':numero_operacion' => $numero_operacion,
                ':monto' => $monto,
                ':observacion' => $observacion,
                ':moneda' => $moneda,
                ':id_admin' => $_SESSION['user_id'],
                ':id_cuenta_destino' => $id_cuenta_destino
            ]);
            
            echo json_encode(['status' => 'success']);
            break;
            
        default:
            throw new Exception("Acción inválida.");
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
