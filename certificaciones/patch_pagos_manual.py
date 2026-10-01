import re

with open(r"C:\laragon\www\certificaciones\controllers\pagos_controlador.php", "r", encoding="utf-8") as f:
    content = f.read()

# 1. Update user_id logic in obtener_materias
target_user_id = "$user_id = $_SESSION['user_id'];"
replacement_user_id = """
            if (isset($_POST['id_estudiante']) && tieneAcceso([4, 5, 6])) {
                $user_id = $_POST['id_estudiante'];
            } else {
                $user_id = $_SESSION['user_id'];
            }"""
content = content.replace(target_user_id, replacement_user_id)

# 2. Add 'registrar_pago_manual' action
new_action = """    case 'registrar_pago_manual':
        if (!tieneAcceso([4, 5, 6])) {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
            exit;
        }
        
        $id_usuario = isset($_POST['id_estudiante']) ? intval($_POST['id_estudiante']) : 0;
        $id_curso = isset($_POST['id_curso']) ? intval($_POST['id_curso']) : 0;
        $id_materia = isset($_POST['id_materia']) ? intval($_POST['id_materia']) : 0;
        $id_cuenta = isset($_POST['id_cuenta_bancaria']) ? intval($_POST['id_cuenta_bancaria']) : 0;
        
        $monto = isset($_POST['monto']) ? floatval($_POST['monto']) : 0;
        $moneda = isset($_POST['moneda']) ? trim($_POST['moneda']) : 'Dolares';
        $metodo_pago = isset($_POST['metodo_pago']) ? trim($_POST['metodo_pago']) : '';
        $banco_origen = ($metodo_pago === 'Efectivo') ? 'Taquilla de la Universidad' : trim($_POST['banco_origen']);
        $referencia = trim($_POST['numero_operacion']);
        $fecha_pago = isset($_POST['fecha_pago']) ? $_POST['fecha_pago'] : date('Y-m-d');
        
        if ($id_usuario <= 0 || $id_curso <= 0 || $monto <= 0 || empty($referencia) || $id_cuenta <= 0) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
            exit;
        }

        if (strlen($referencia) < 6) {
            echo json_encode(['success' => false, 'message' => 'La referencia debe tener un minimo 6 digitos.']);
            exit;
        }

        if (!preg_match('/^[a-zA-Z0-9-]+$/', $referencia)) {
            echo json_encode(['success' => false, 'message' => 'La referencia contiene caracteres invlidos.']);
            exit;
        }

        // Siempre entra como pendiente por la regla de segregacin de funciones
        $estado = 'Pendiente';

        try {
            $sql = "INSERT INTO cursos.comprobantes_pago 
                    (id_usuario, id_curso, id_materia_bimestre, id_cuenta_bancaria, metodo_pago, banco_origen, 
                     numero_operacion, fecha_pago, moneda, monto, estado, archivo_comprobante, creado_en)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NOW())";
            
            $pdo = $db->getConn();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $id_usuario, $id_curso, $id_materia, $id_cuenta, $metodo_pago, $banco_origen,
                $referencia, $fecha_pago, $moneda, $monto, $estado
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Pago manual registrado correctamente como Pendiente.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al registrar el pago: ' . $e->getMessage()]);
        }
        break;

"""

content = content.replace("switch ($action) {", "switch ($action) {\n\n" + new_action)

with open(r"C:\laragon\www\certificaciones\controllers\pagos_controlador.php", "w", encoding="utf-8") as f:
    f.write(content)

print("pagos_controlador.php updated successfully")
