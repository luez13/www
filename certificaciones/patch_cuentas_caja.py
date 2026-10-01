import re

with open(r"C:\laragon\www\certificaciones\controllers\pagos_controlador.php", "r", encoding="utf-8") as f:
    content = f.read()

new_action = """    case 'obtener_cuentas_caja':
        if (!tieneAcceso([4, 5, 6])) {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
            exit;
        }
        $id_curso = isset($_POST['id_curso']) ? intval($_POST['id_curso']) : 0;
        
        // Obtener el id_extension del curso
        $pdo = $db->getConn();
        $stmt_ext = $pdo->prepare("SELECT id_extension FROM cursos.cursos WHERE id_curso = ?");
        $stmt_ext->execute([$id_curso]);
        $ext = $stmt_ext->fetchColumn();

        if ($ext) {
            // Obtener cuentas de tipo Bveda o Caja Fisica de esa extension
            $stmt = $pdo->prepare("SELECT id_cuenta, banco, tipo_cuenta, numero_cuenta 
                                   FROM cursos.cuentas_bancarias 
                                   WHERE activo = true 
                                   AND id_extension = ? 
                                   AND (tipo_cuenta ILIKE '%caja%' OR tipo_cuenta ILIKE '%boveda%' OR banco ILIKE '%efectivo%' OR banco ILIKE '%caja%')");
            $stmt->execute([$ext]);
            $cuentas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'cuentas' => $cuentas]);
        } else {
            echo json_encode(['success' => false, 'cuentas' => []]);
        }
        break;

"""

content = content.replace("switch ($action) {", "switch ($action) {\n\n" + new_action)

with open(r"C:\laragon\www\certificaciones\controllers\pagos_controlador.php", "w", encoding="utf-8") as f:
    f.write(content)

# Now, update the JS in gestion_pagos.php to call 'obtener_cuentas_caja'
with open(r"C:\laragon\www\certificaciones\views\gestion_pagos.php", "r", encoding="utf-8") as f:
    gp_content = f.read()

gp_content = gp_content.replace("action: 'obtener_cuentas'", "action: 'obtener_cuentas_caja'")

with open(r"C:\laragon\www\certificaciones\views\gestion_pagos.php", "w", encoding="utf-8") as f:
    f.write(gp_content)

print("pagos_controlador updated with obtener_cuentas_caja")
