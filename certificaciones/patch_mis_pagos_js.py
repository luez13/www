import re

with open(r"C:\laragon\www\certificaciones\views\mis_pagos.php", "r", encoding="utf-8") as f:
    content = f.read()

# Fix JS to remove Bimestre text for PNFA
old_js = "option.text = 'Bimestre ' + materia.lapso_academico + ' - ' + materia.nombre_materia;"
new_js = "option.text = response.is_pnfa ? materia.nombre_materia : 'Bimestre ' + materia.lapso_academico + ' - ' + materia.nombre_materia;"
content = content.replace(old_js, new_js)

# We should also fix the initial label and default option for PNFA
# "Seleccione la Materia (Opcional - Solo para pagos individuales):"
# "-- Pago general del diplomado --"
# Since it's dynamic based on is_pnfa, maybe we can update these texts in the same JS if response.is_pnfa?
# Actually, the user just said "que no se vea el bimestre solo dira el nombre".

with open(r"C:\laragon\www\certificaciones\views\mis_pagos.php", "w", encoding="utf-8") as f:
    f.write(content)
