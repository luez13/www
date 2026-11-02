<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/model.php';
if (!function_exists('tieneAcceso')) {
    require_once '../config/auth_helper.php';
}
require_once 'autenticacion.php';

header('Content-Type: application/json; charset=utf-8');

// Validar acceso: Roles 3, 4 (ADMIN), 5 (ANALISTA), 6 y 7 (SUPER_ADMIN)
if (!isset($_SESSION['user_id']) || !tieneAcceso([3, 4, 5, 6])) {
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado.']);
    exit;
}

$id_admin = $_SESSION['user_id'];

if (!isset($_POST['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Acción no especificada.']);
    exit;
}

$action = $_POST['action'];
$db = new DB();
$pdo = $db->getConn();
$id_extension_activa = isset($_SESSION['id_extension']) ? $_SESSION['id_extension'] : 1;

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
                try {
                    $stmtCheck = $pdo->prepare("SELECT 1 FROM cursos.usuarios_extensiones WHERE id_usuario = :id_usuario AND id_extension = :id_extension");
                    $stmtCheck->execute([':id_usuario' => $user['id'], ':id_extension' => $id_extension_activa]);
                    if (!$stmtCheck->fetch()) {
                        $stmtAssign = $pdo->prepare("INSERT INTO cursos.usuarios_extensiones (id_usuario, id_extension) VALUES (:id, :ext)");
                        $stmtAssign->execute([':id' => $user['id'], ':ext' => $id_extension_activa]);
                    }
                } catch (Exception $e) {
                    // Ignorar si la tabla usuarios_extensiones no aplica o ya existe
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
            try {
                $stmtAssign = $pdo->prepare("INSERT INTO cursos.usuarios_extensiones (id_usuario, id_extension) VALUES (:id_user, :id_ext)");
                $stmtAssign->execute([':id_user' => $nuevo_id, ':id_ext' => $id_extension_activa]);
            } catch (Exception $e) {
                // Ignorar si no aplica
            }
            
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
            // Listar exclusivamente los cursos/conceptos del departamento activo con su ID
            $stmt = $pdo->prepare("SELECT id_curso, nombre_curso, tipo_curso FROM cursos.cursos WHERE id_extension = :id_extension ORDER BY id_curso DESC");
            $stmt->execute([':id_extension' => (int)$id_extension_activa]);
            $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['status' => 'success', 'cursos' => $cursos]);
            break;
            
        case 'registrar_pago_taquilla':
            $id_usuario = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : 0;
            $id_curso = isset($_POST['id_curso']) ? (int)$_POST['id_curso'] : 0;
            $id_materia_bimestre = !empty($_POST['id_materia_bimestre']) ? (int)$_POST['id_materia_bimestre'] : null;
            $numero_operacion = isset($_POST['numero_operacion']) ? trim($_POST['numero_operacion']) : '';
            $monto = isset($_POST['monto']) ? (float)$_POST['monto'] : 0;
            $moneda = isset($_POST['moneda']) ? trim($_POST['moneda']) : 'Bolivares';
            $observacion = isset($_POST['observacion']) ? trim($_POST['observacion']) : '';
            $tipo_recepcion = isset($_POST['tipo_recepcion']) ? trim($_POST['tipo_recepcion']) : 'transferencia';
            
            if (empty($id_usuario) || empty($id_curso) || empty($numero_operacion) || $monto <= 0 || empty($moneda)) {
                throw new Exception("Datos de pago incompletos o inválidos.");
            }
            
            // Verificar que el curso exista y pertenezca exclusivamente al departamento activo
            $stmtCheck = $pdo->prepare("SELECT id_curso, id_extension FROM cursos.cursos WHERE id_curso = :id_curso AND id_extension = :id_ext LIMIT 1");
            $stmtCheck->execute([':id_curso' => $id_curso, ':id_ext' => (int)$id_extension_activa]);
            $cursoRow = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            if (!$cursoRow) {
                throw new Exception("El curso o concepto seleccionado no pertenece a este departamento.");
            }
            $id_extension_curso = (int)$id_extension_activa;
            
            $estado_pago = 'Pendiente';
            $banco_origen = '';
            $id_cuenta_destino = null;

            if ($tipo_recepcion === 'efectivo_punto') {
                $estado_pago = 'Comprobado';
                $banco_origen = 'Taquilla Física';
                
                // Obtener la cuenta bancaria de tipo Caja Física / Bóveda / Efectivo para este departamento
                $stmtCuenta = $pdo->prepare("
                    SELECT id_cuenta 
                    FROM cursos.cuentas_bancarias 
                    WHERE (id_extension = :id_ext1 OR id_extension IS NULL)
                      AND activo = true
                      AND (banco ILIKE '%caja%' OR tipo_cuenta ILIKE '%caja%' OR tipo_cuenta ILIKE '%boveda%' OR tipo_cuenta ILIKE '%bóveda%' OR banco ILIKE '%efectivo%')
                    ORDER BY CASE WHEN id_extension = :id_ext2 THEN 0 ELSE 1 END, id_cuenta ASC
                    LIMIT 1
                ");
                $stmtCuenta->execute([':id_ext1' => $id_extension_curso, ':id_ext2' => $id_extension_curso]);
                $cuenta_caja = $stmtCuenta->fetch(PDO::FETCH_ASSOC);
                if ($cuenta_caja) {
                    $id_cuenta_destino = (int)$cuenta_caja['id_cuenta'];
                } elseif (!empty($_POST['id_cuenta_destino'])) {
                    $id_cuenta_destino = (int)$_POST['id_cuenta_destino'];
                }
            } else {
                $estado_pago = 'Pendiente';
                $banco_origen = isset($_POST['banco_origen']) ? trim($_POST['banco_origen']) : '';
                $id_cuenta_destino = !empty($_POST['id_cuenta_destino']) ? (int)$_POST['id_cuenta_destino'] : 0;
                
                if (empty($banco_origen) || empty($id_cuenta_destino)) {
                    throw new Exception("Debe especificar el Banco de Origen y la Cuenta Destino para una transferencia.");
                }
            }

            // Procesar archivo de comprobante si fue adjuntado (válido tanto para transferencia como para efectivo/punto)
            $rutaBD = null;
            $rutaFisica = null;
            if (isset($_FILES['comprobante_archivo']) && $_FILES['comprobante_archivo']['error'] === UPLOAD_ERR_OK) {
                $archivo = $_FILES['comprobante_archivo'];
                $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                $permitidas = ['pdf', 'jpg', 'jpeg', 'png'];

                if (!in_array($extension, $permitidas)) {
                    throw new Exception("Formato de archivo no permitido. Usa PDF, JPG, JPEG o PNG.");
                }

                if ($archivo['size'] > 5 * 1024 * 1024) {
                    throw new Exception("El archivo supera el tamaño máximo permitido de 5MB.");
                }

                if (function_exists('finfo_open')) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeReal = finfo_file($finfo, $archivo['tmp_name']);
                    finfo_close($finfo);
                    $mimesPermitidos = ['application/pdf', 'image/jpeg', 'image/png'];
                    if (!in_array($mimeReal, $mimesPermitidos)) {
                        throw new Exception("El contenido del archivo no corresponde a una imagen o PDF válido.");
                    }
                }

                $directorioDestino = '../public/assets/comprobantes/';
                if (!file_exists($directorioDestino)) {
                    mkdir($directorioDestino, 0777, true);
                }

                $nombreUnico = $id_usuario . '_' . $id_curso . '_' . time() . '.' . $extension;
                $rutaFisica = $directorioDestino . $nombreUnico;

                if (move_uploaded_file($archivo['tmp_name'], $rutaFisica)) {
                    $rutaBD = 'assets/comprobantes/' . $nombreUnico;
                } else {
                    throw new Exception("Error al guardar el archivo del comprobante en el servidor.");
                }
            }
            
            // Registrar pago en cursos.comprobantes_pago garantizando id_extension del departamento activo
            $stmt = $pdo->prepare("
                INSERT INTO cursos.comprobantes_pago 
                (id_usuario, id_curso, id_materia_bimestre, archivo_ruta, numero_operacion, banco_origen, monto, estado, fecha_pago, fecha_subida, observacion, moneda, id_admin_gestor, fecha_gestion, id_cuenta_destino, id_extension) 
                VALUES 
                (:id_usuario, :id_curso, :id_materia_bimestre, :archivo_ruta, :numero_operacion, :banco_origen, :monto, :estado, CURRENT_DATE, CURRENT_TIMESTAMP, :observacion, :moneda, :id_admin, CURRENT_TIMESTAMP, :id_cuenta_destino, :id_extension)
            ");
            
            $stmt->execute([
                ':id_usuario' => $id_usuario,
                ':id_curso' => $id_curso,
                ':id_materia_bimestre' => $id_materia_bimestre,
                ':archivo_ruta' => $rutaBD,
                ':numero_operacion' => $numero_operacion,
                ':banco_origen' => $banco_origen,
                ':monto' => $monto,
                ':estado' => $estado_pago,
                ':observacion' => $observacion,
                ':moneda' => $moneda,
                ':id_admin' => $id_admin,
                ':id_cuenta_destino' => $id_cuenta_destino,
                ':id_extension' => $id_extension_curso
            ]);

            // Asegurar inscripción del estudiante en cursos.certificaciones y actualizar estado de pago si corresponde
            try {
                $stmtExisteCert = $pdo->prepare("SELECT id_certificacion FROM cursos.certificaciones WHERE id_usuario = :id_usuario AND curso_id = :id_curso LIMIT 1");
                $stmtExisteCert->execute([':id_usuario' => $id_usuario, ':id_curso' => $id_curso]);
                $existeCert = $stmtExisteCert->fetchColumn();

                $pagoBool = ($estado_pago === 'Comprobado' && empty($id_materia_bimestre));
                if ($existeCert) {
                    if ($pagoBool) {
                        $stmtCert = $pdo->prepare("UPDATE cursos.certificaciones SET pago = true WHERE id_usuario = :id_usuario AND curso_id = :id_curso");
                        $stmtCert->execute([':id_usuario' => $id_usuario, ':id_curso' => $id_curso]);
                    }
                } else {
                    $stmtInsCert = $pdo->prepare("INSERT INTO cursos.certificaciones (id_usuario, curso_id, completado, fecha_inscripcion, pago) VALUES (:id_usuario, :id_curso, false, CURRENT_TIMESTAMP, :pago)");
                    $stmtInsCert->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
                    $stmtInsCert->bindValue(':id_curso', $id_curso, PDO::PARAM_INT);
                    $stmtInsCert->bindValue(':pago', $pagoBool, PDO::PARAM_BOOL);
                    $stmtInsCert->execute();
                }
            } catch (Exception $e) {
                // Continuar aunque certificaciones no requiera actualización
            }
            
            $msg = ($estado_pago === 'Comprobado')
                ? 'El pago en Efectivo / Punto de Venta ha sido registrado y aprobado exitosamente.'
                : 'La transferencia ha sido registrada como Pendiente para su conciliación.';
            echo json_encode(['status' => 'success', 'message' => $msg]);
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
?>
