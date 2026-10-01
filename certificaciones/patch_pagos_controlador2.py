import re

with open(r"C:\laragon\www\certificaciones\controllers\pagos_controlador.php", "r", encoding="utf-8") as f:
    content = f.read()

target = "stripos($m['nombre_materia'], 'otro') !== false || stripos($m['nombre_materia'], 'repitencia') !== false"
replacement = "stripos($m['nombre_materia'], 'otro') !== false || stripos($m['nombre_materia'], 'repitencia') !== false || stripos($m['nombre_materia'], 'trabajo especial de grado') !== false || stripos($m['nombre_materia'], 'teg') !== false"

content = content.replace(target, replacement)

# Also let's update min length reference to 6!
content = content.replace("strlen($ref) < 4", "strlen($ref) < 6")
content = content.replace("menos 4 d", "menos 6 d")

with open(r"C:\laragon\www\certificaciones\controllers\pagos_controlador.php", "w", encoding="utf-8") as f:
    f.write(content)
