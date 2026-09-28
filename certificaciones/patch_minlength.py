import re

with open(r"C:\laragon\www\certificaciones\views\mis_pagos.php", "r", encoding="utf-8") as f:
    content = f.read()

# Add minlength to input_numero_operacion
old_input = """<input type="text" name="numero_operacion" id="input_numero_operacion"
                                    class="form-control" placeholder="Ej: 12345678" required>"""
new_input = """<input type="text" name="numero_operacion" id="input_numero_operacion"
                                    class="form-control" placeholder="Ej: 12345678" required minlength="4" pattern="[0-9]{4,}" title="Debe ingresar al menos 4 nmeros para la referencia">"""
content = content.replace(old_input, new_input)

# Add minlength to edit_numero_operacion
old_edit = """<input type="text" name="numero_operacion" id="edit_numero_operacion" class="form-control" required>"""
new_edit = """<input type="text" name="numero_operacion" id="edit_numero_operacion" class="form-control" required minlength="4" pattern="[0-9]{4,}" title="Debe ingresar al menos 4 nmeros para la referencia">"""
content = content.replace(old_edit, new_edit)

with open(r"C:\laragon\www\certificaciones\views\mis_pagos.php", "w", encoding="utf-8") as f:
    f.write(content)
