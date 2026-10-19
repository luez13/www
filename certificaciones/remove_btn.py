import re

with open(r"C:\laragon\www\certificaciones\views\gestion_pagos.php", "r", encoding="utf-8") as f:
    content = f.read()

target = r'<button class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#modalPagoManual"><i class="fas fa-plus fa-sm text-white-50"></i> Registrar Pago Manual</button>'
content = content.replace(target, '')

with open(r"C:\laragon\www\certificaciones\views\gestion_pagos.php", "w", encoding="utf-8") as f:
    f.write(content)

print("Removed button from gestion_pagos.php")
