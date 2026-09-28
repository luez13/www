import re

with open(r"C:\laragon\www\certificaciones\views\gestion_pagos.php", "r", encoding="utf-8") as f:
    content = f.read()

# Add minlength to admin_edit_numero_operacion
old_input = """<input type="text" name="numero_operacion" id="admin_edit_numero_operacion"
                                class="form-control" required>"""
new_input = """<input type="text" name="numero_operacion" id="admin_edit_numero_operacion"
                                class="form-control" required minlength="4" pattern="[0-9]{4,}" title="Debe ingresar al menos 4 nmeros para la referencia">"""
content = content.replace(old_input, new_input)

with open(r"C:\laragon\www\certificaciones\views\gestion_pagos.php", "w", encoding="utf-8") as f:
    f.write(content)
