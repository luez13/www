import re

with open(r"C:\laragon\www\certificaciones\views\mis_pagos.php", "r", encoding="utf-8") as f:
    content = f.read()

# Fix the broken line
broken_code = "$costoTexto = $c['costo'] > 0 ? '$' . number_format($c['costo'], 2) : 'Gratis';"
fixed_code = "$costoTexto = $c['costo'] > 0 ? ' - $' . number_format($c['costo'], 2) : '';"
content = content.replace(broken_code, fixed_code)

broken_line2 = "<?= h($c['nombre_curso']) ?> - <?= $costoTexto ?>    <?= $estadoPago ?>"
fixed_line2 = "<?= h($c['nombre_curso']) ?><?= $costoTexto ?>    <?= $estadoPago ?>"
content = content.replace(broken_line2, fixed_line2)


with open(r"C:\laragon\www\certificaciones\views\mis_pagos.php", "w", encoding="utf-8") as f:
    f.write(content)
