import re

with open(r"C:\laragon\www\certificaciones\controllers\pagos_controlador.php", "r", encoding="utf-8") as f:
    content = f.read()

target = """        try {
            $sql = "INSERT INTO cursos.comprobantes_pago 
                    (id_usuario, id_curso, id_materia_bimestre, id_cuenta_bancaria, metodo_pago, banco_origen, 
                     numero_operacion, fecha_pago, moneda, monto, estado, archivo_comprobante, creado_en)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NOW())";
            
            $pdo = $db->getConn();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $id_usuario, $id_curso, $id_materia, $id_cuenta, $metodo_pago, $banco_origen,
                $referencia, $fecha_pago, $moneda, $monto, $estado
            ]);"""

replacement = """        try {
            $pdo = $db->getConn();
            
            // Get id_extension from the course
            $stmt_ext = $pdo->prepare("SELECT id_extension FROM cursos.cursos WHERE id_curso = ?");
            $stmt_ext->execute([$id_curso]);
            $id_extension = $stmt_ext->fetchColumn();

            $sql = "INSERT INTO cursos.comprobantes_pago 
                    (id_usuario, id_curso, id_materia_bimestre, id_cuenta_destino, id_extension, metodo_pago, banco_origen, 
                     numero_operacion, fecha_pago, moneda, monto, estado, archivo_comprobante, creado_en)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NOW())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $id_usuario, $id_curso, $id_materia, $id_cuenta, $id_extension, $metodo_pago, $banco_origen,
                $referencia, $fecha_pago, $moneda, $monto, $estado
            ]);"""

content = content.replace(target, replacement)

with open(r"C:\laragon\www\certificaciones\controllers\pagos_controlador.php", "w", encoding="utf-8") as f:
    f.write(content)

print("pagos_controlador manual insert fixed")
