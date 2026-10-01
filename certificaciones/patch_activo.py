import re

with open(r"C:\laragon\www\certificaciones\controllers\pagos_controlador.php", "r", encoding="utf-8") as f:
    content = f.read()

target = "'activo' => isset($_POST['activo']) ? true : false"
replacement = "'activo' => isset($_POST['activo']) ? 'true' : 'false'"
content = content.replace(target, replacement)

with open(r"C:\laragon\www\certificaciones\controllers\pagos_controlador.php", "w", encoding="utf-8") as f:
    f.write(content)

print("pagos_controlador.php updated for activo")
