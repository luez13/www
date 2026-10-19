import re

# 1. FIX taquilla_pagos.php
path_taquilla = r"C:\laragon\www\certificaciones\public\taquilla_pagos.php"
with open(path_taquilla, "r", encoding="utf-8") as f:
    content = f.read()

content = re.sub(r"banco != 'CAJA F.SICA'", "banco NOT ILIKE 'CAJA F%SICA'", content)

with open(path_taquilla, "w", encoding="utf-8") as f:
    f.write(content)

# 2. FIX taquilla_controlador.php
path_taq_ctrl = r"C:\laragon\www\certificaciones\controllers\taquilla_controlador.php"
with open(path_taq_ctrl, "r", encoding="utf-8") as f:
    content = f.read()

content = re.sub(r"banco = 'CAJA F.SICA'", "banco ILIKE 'CAJA F%SICA'", content)

with open(path_taq_ctrl, "w", encoding="utf-8") as f:
    f.write(content)

print("Queries fixed to avoid encoding issues!")
