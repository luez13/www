import re

with open(r"C:\laragon\www\certificaciones\controllers\pagos_controlador.php", "r", encoding="utf-8") as f:
    content = f.read()

# Add id_extension to $datosCuenta array
old_array = """'numero_cuenta' => isset($_POST['numero_cuenta']) ? $_POST['numero_cuenta'] : '',
            // Los checkboxes HTML no envan nada si no estn marcados
            'activo' => isset($_POST['activo']) ? true : false"""

# Because of UTF-8 chars in comments, I will use regex
array_pattern = r"'numero_cuenta'.*?'activo'\s*=>\s*isset\(\$_POST\['activo'\]\)\s*\?\s*true\s*:\s*false"
new_array = """'numero_cuenta' => isset($_POST['numero_cuenta']) ? $_POST['numero_cuenta'] : '',
            'id_extension' => isset($_POST['id_extension']) && $_POST['id_extension'] !== '' ? intval($_POST['id_extension']) : null,
            // Los checkboxes HTML no envian nada si no estan marcados
            'activo' => isset($_POST['activo']) ? true : false"""
content = re.sub(array_pattern, new_array, content, flags=re.DOTALL)

with open(r"C:\laragon\www\certificaciones\controllers\pagos_controlador.php", "w", encoding="utf-8") as f:
    f.write(content)

with open(r"C:\laragon\www\certificaciones\models\Pago.php", "r", encoding="utf-8") as f:
    content2 = f.read()

# In crearCuenta
old_crear = """(banco, titular, cedula_rif, telefono, correo, tipo_cuenta, numero_cuenta, activo) 
                VALUES 
                (:banco, :titular, :cedula_rif, :telefono, :correo, :tipo_cuenta, :numero_cuenta, :activo)";"""
new_crear = """(banco, titular, cedula_rif, telefono, correo, tipo_cuenta, numero_cuenta, activo, id_extension) 
                VALUES 
                (:banco, :titular, :cedula_rif, :telefono, :correo, :tipo_cuenta, :numero_cuenta, :activo, :id_extension)";"""
content2 = content2.replace(old_crear, new_crear)

old_crear_execute = """'numero_cuenta' => $datos['numero_cuenta'],
            'activo' => isset($datos['activo']) ? $datos['activo'] : true // Por defecto true
        ]);"""
new_crear_execute = """'numero_cuenta' => $datos['numero_cuenta'],
            'activo' => isset($datos['activo']) ? $datos['activo'] : true, // Por defecto true
            'id_extension' => isset($datos['id_extension']) ? $datos['id_extension'] : null
        ]);"""
content2 = content2.replace(old_crear_execute, new_crear_execute)

# In actualizarCuenta
old_actualizar = """numero_cuenta = :numero_cuenta, 
                activo = :activo 
                WHERE id_cuenta = :id_cuenta";"""
new_actualizar = """numero_cuenta = :numero_cuenta, 
                activo = :activo,
                id_extension = :id_extension
                WHERE id_cuenta = :id_cuenta";"""
content2 = content2.replace(old_actualizar, new_actualizar)

old_act_execute = """'numero_cuenta' => $datos['numero_cuenta'],
            'activo' => isset($datos['activo']) ? $datos['activo'] : true
        ]);"""
new_act_execute = """'numero_cuenta' => $datos['numero_cuenta'],
            'activo' => isset($datos['activo']) ? $datos['activo'] : true,
            'id_extension' => isset($datos['id_extension']) ? $datos['id_extension'] : null
        ]);"""
content2 = content2.replace(old_act_execute, new_act_execute)

with open(r"C:\laragon\www\certificaciones\models\Pago.php", "w", encoding="utf-8") as f:
    f.write(content2)
