import re

# 1. Update public/taquilla_pagos.php
with open(r"C:\laragon\www\certificaciones\public\taquilla_pagos.php", "r", encoding="utf-8") as f:
    content = f.read()

target_warning_regex = r'<div class="alert alert-warning mt-3">[\s\S]*?aprobado autom.ticamente[\s\S]*?</div>'

replacement_warning = """<div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle"></i> <b>Atencin:</b> Al registrar este pago, quedar en cola de <b>Pendientes</b> para su conciliacin por parte de Administracin.
                    </div>"""

content = re.sub(target_warning_regex, replacement_warning, content)

with open(r"C:\laragon\www\certificaciones\public\taquilla_pagos.php", "w", encoding="utf-8") as f:
    f.write(content)


# 2. Update controllers/taquilla_controlador.php
with open(r"C:\laragon\www\certificaciones\controllers\taquilla_controlador.php", "r", encoding="utf-8") as f:
    content = f.read()

replacement_insert = "VALUES \n                  (:id_usuario, :id_curso, :numero_operacion, 'Taquilla Física', :monto, 'Pendiente', CURRENT_DATE"

content = re.sub(r"VALUES\s*\(\:id_usuario, \:id_curso, \:numero_operacion, 'Taquilla.*?', \:monto, 'Comprobado', CURRENT_DATE", replacement_insert, content)

with open(r"C:\laragon\www\certificaciones\controllers\taquilla_controlador.php", "w", encoding="utf-8") as f:
    f.write(content)

print("taquilla patched")
