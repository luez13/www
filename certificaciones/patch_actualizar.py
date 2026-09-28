import re

with open(r"C:\laragon\www\certificaciones\models\Pago.php", "r", encoding="utf-8") as f:
    content = f.read()

# Fix actualizarCuenta SQL
sql_pattern = r'numero_cuenta = :numero_cuenta,\s*activo = :activo\s*WHERE id_cuenta = :id_cuenta'
sql_replacement = r'numero_cuenta = :numero_cuenta, activo = :activo, id_extension = :id_extension WHERE id_cuenta = :id_cuenta'
content = re.sub(sql_pattern, sql_replacement, content)

# Fix actualizarCuenta execute
exec_pattern = r"'numero_cuenta' => \$datos\['numero_cuenta'\],\s*'activo' => \$datos\['activo'\],\s*'id_cuenta' => \$datos\['id_cuenta'\]\s*\]\);"
exec_replacement = """'numero_cuenta' => $datos['numero_cuenta'],
            'activo' => $datos['activo'],
            'id_extension' => isset($datos['id_extension']) ? $datos['id_extension'] : null,
            'id_cuenta' => $datos['id_cuenta']
        ]);"""
content = re.sub(exec_pattern, exec_replacement, content)

with open(r"C:\laragon\www\certificaciones\models\Pago.php", "w", encoding="utf-8") as f:
    f.write(content)
