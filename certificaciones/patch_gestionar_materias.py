import re

with open(r"C:\laragon\www\certificaciones\views\gestionar_materias.php", "r", encoding="utf-8") as f:
    content = f.read()

target = r'</datalist>\s*</div>'

checkbox_html = """</datalist>
                    </div>

                    <div class="col-md-12 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="exento_prelacion" id="exento_prelacion" value="1">
                            <label class="form-check-label font-weight-bold text-danger" for="exento_prelacion">
                                <i class="fas fa-unlock-alt me-1"></i> Eximir de secuencia prelatoria? (Estudiantes podrn pagar en cualquier momento)
                            </label>
                        </div>
                    </div>"""

# Ensure it hasn't been added yet
if 'id="exento_prelacion"' not in content:
    content = re.sub(target, checkbox_html, content, count=1)

# Add population logic for edit form inside JavaScript
js_target = "$('#docente_id').val(d.docente_id);"
js_replacement = "$('#docente_id').val(d.docente_id);\n                    $('#exento_prelacion').prop('checked', d.exento_prelacion == 1 || d.exento_prelacion == true);"
content = content.replace(js_target, js_replacement)

# Reset checkbox in add form
js_add_target = "$('#id_materia').val(0);"
js_add_replacement = "$('#id_materia').val(0);\n        $('#exento_prelacion').prop('checked', false);"
content = content.replace(js_add_target, js_add_replacement)

with open(r"C:\laragon\www\certificaciones\views\gestionar_materias.php", "w", encoding="utf-8") as f:
    f.write(content)

print("Patch applied")
