import re

# 1. FIX taquilla_pagos.php
path_taquilla = r"C:\laragon\www\certificaciones\public\taquilla_pagos.php"
with open(path_taquilla, "r", encoding="utf-8") as f:
    content = f.read()

# Replace CAJA FSICA with CAJA FÍSICA and allow NULL id_extension (Global accounts)
content = content.replace("banco != 'CAJA FSICA'", "banco != 'CAJA FÍSICA'")
content = content.replace("id_extension = :id_ext AND activo = true", "(id_extension = :id_ext OR id_extension IS NULL) AND activo = true")

with open(path_taquilla, "w", encoding="utf-8") as f:
    f.write(content)

# 2. FIX taquilla_controlador.php
path_taq_ctrl = r"C:\laragon\www\certificaciones\controllers\taquilla_controlador.php"
with open(path_taq_ctrl, "r", encoding="utf-8") as f:
    content = f.read()

content = content.replace("banco = 'CAJA FSICA'", "banco = 'CAJA FÍSICA'")

with open(path_taq_ctrl, "w", encoding="utf-8") as f:
    f.write(content)

# 3. FIX pagos_controlador.php
path_pagos = r"C:\laragon\www\certificaciones\controllers\pagos_controlador.php"
with open(path_pagos, "r", encoding="utf-8") as f:
    content = f.read()

# Fix the security patch to allow global accounts
security_find = r"SELECT count\(\*\) FROM cursos\.cuentas_bancarias cb JOIN cursos\.cursos c ON cb\.id_extension = c\.id_extension WHERE cb\.id_cuenta = :id_cuenta AND c\.id_curso = :id_curso"
security_replace = r"SELECT count(*) FROM cursos.cuentas_bancarias cb JOIN cursos.cursos c ON (cb.id_extension = c.id_extension OR cb.id_extension IS NULL) WHERE cb.id_cuenta = :id_cuenta AND c.id_curso = :id_curso"

content = re.sub(security_find, security_replace, content)

with open(path_pagos, "w", encoding="utf-8") as f:
    f.write(content)

# 4. Fix models/Pago.php (if it fetches accounts for the student dropdown, it also needs to support Global accounts)
path_pago_mdl = r"C:\laragon\www\certificaciones\models\Pago.php"
with open(path_pago_mdl, "r", encoding="utf-8") as f:
    content = f.read()

# Currently `obtenerCuentasSede($id_curso)` in models/Pago.php
# Let's read it first to ensure we replace correctly
print("Files updated! Checking Pago.php...")
