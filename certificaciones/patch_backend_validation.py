import re

with open(r"C:\laragon\www\certificaciones\controllers\pagos_controlador.php", "r", encoding="utf-8") as f:
    content = f.read()

validation_code = """
        // Parche de Seguridad: Validacion estricta de numero de operacion en backend
        if (isset($_POST['numero_operacion']) && !empty(trim($_POST['numero_operacion']))) {
            $ref = trim($_POST['numero_operacion']);
            if (strlen($ref) < 4 || !preg_match('/^[0-9]+$/', $ref)) {
                echo json_encode(['success' => false, 'message' => 'Error Crtico: El nmero de referencia debe contener al menos 4 dgitos numricos vlidos. Transaccin abortada.']);
                exit;
            }
        }
"""

# Patch subir_comprobante
# Find the end of the requeridos loop in subir_comprobante
subir_pattern = r"(case 'subir_comprobante':.*?foreach \(\$requeridos as \$campo\) \{.*?\}\s*\})"
subir_replacement = r"\1" + validation_code

content = re.sub(subir_pattern, subir_replacement, content, flags=re.DOTALL)

# Patch editar_comprobante
editar_pattern = r"(case 'editar_comprobante':.*?foreach \(\$requeridos as \$campo\) \{.*?\}\s*\})"
editar_replacement = r"\1" + validation_code

content = re.sub(editar_pattern, editar_replacement, content, flags=re.DOTALL)

with open(r"C:\laragon\www\certificaciones\controllers\pagos_controlador.php", "w", encoding="utf-8") as f:
    f.write(content)
