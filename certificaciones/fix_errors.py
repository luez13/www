import re

with open(r"C:\laragon\www\certificaciones\views\mis_pagos.php", "r", encoding="utf-8") as f:
    content = f.read()

# Fix the broken line
broken_code = "$costoTexto = $c['costo'] > 0 ? ' - \n                                    ?>"
fixed_code = "$costoTexto = $c['costo'] > 0 ? ' - $' . number_format($c['costo'], 2, ',', '.') : '';\n                                    ?>"
content = content.replace(broken_code, fixed_code)

with open(r"C:\laragon\www\certificaciones\views\mis_pagos.php", "w", encoding="utf-8") as f:
    f.write(content)

with open(r"C:\laragon\www\certificaciones\controllers\gestion_materia.php", "r", encoding="utf-8") as f:
    content2 = f.read()

# Fix the corrupted gestion_materia
broken_code2 = "'docente_id' => (!empty(<?php"
# Find everything after broken_code2 to cut it
idx = content2.find(broken_code2)
if idx != -1:
    # We will restore the array manually
    # Let's see what was supposed to be there.
    pass
