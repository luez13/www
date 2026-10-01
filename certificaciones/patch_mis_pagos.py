import re

with open(r"C:\laragon\www\certificaciones\views\mis_pagos.php", "r", encoding="utf-8") as f:
    content = f.read()

target = r'<\?php foreach \(\$cuentasActivas as \$cuenta\): \?>'
replacement = """<?php foreach ($cuentasActivas as $cuenta): ?>
                                      <?php if (stripos($cuenta['tipo_cuenta'], 'caja') !== false || stripos($cuenta['tipo_cuenta'], 'boveda') !== false || stripos($cuenta['banco'], 'fisica') !== false || stripos($cuenta['banco'], 'efectivo') !== false) continue; // Ocultar cajas fisicas a los estudiantes ?>"""

content = re.sub(target, replacement, content)

with open(r"C:\laragon\www\certificaciones\views\mis_pagos.php", "w", encoding="utf-8") as f:
    f.write(content)

print("mis_pagos patched to hide caja")
