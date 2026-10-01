import re

# 1. Update gestionar_materias.php (Frontend Checkbox)
with open(r"C:\laragon\www\certificaciones\views\gestionar_materias.php", "r", encoding="utf-8") as f:
    content = f.read()

checkbox_html = """
                        <div class="col-md-12 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="exento_prelacion" id="exento_prelacion" value="1">
                                <label class="form-check-label font-weight-bold text-danger" for="exento_prelacion">
                                    <i class="fas fa-unlock-alt me-1"></i> Eximir de secuencia prelatoria? (Estudiantes podrn pagar en cualquier momento)
                                </label>
                            </div>
                        </div>"""

target = """<textarea class="form-control" name="temario" id="temario" rows="4" placeholder="Ingrese el temario especfico de esta materia. Se usar en el reverso del certificado individual."></textarea>
                        </div>"""

# Replace and also populate it in the edit form if applicable
content = content.replace(target, target + "\n" + checkbox_html)

# Add population logic for edit form inside JavaScript
js_target = "$('#docente_id').val(materia.docente_id);"
js_replacement = "$('#docente_id').val(materia.docente_id);\n        $('#exento_prelacion').prop('checked', materia.exento_prelacion == 1 || materia.exento_prelacion == true);"
content = content.replace(js_target, js_replacement)

# Reset checkbox in add form
js_add_target = "$('#modalidad').val('Virtual');"
js_add_replacement = "$('#modalidad').val('Virtual');\n        $('#exento_prelacion').prop('checked', false);"
content = content.replace(js_add_target, js_add_replacement)

with open(r"C:\laragon\www\certificaciones\views\gestionar_materias.php", "w", encoding="utf-8") as f:
    f.write(content)


# 2. Update gestion_materia.php (Controller capturing $_POST)
with open(r"C:\laragon\www\certificaciones\controllers\gestion_materia.php", "r", encoding="utf-8") as f:
    content = f.read()

target_datos = "'lapso_academico'     => isset($_POST['lapso_academico']) ? $_POST['lapso_academico'] : 1,"
replacement_datos = "'lapso_academico'     => isset($_POST['lapso_academico']) ? $_POST['lapso_academico'] : 1,\n                  'exento_prelacion'    => isset($_POST['exento_prelacion']) ? 1 : 0,"
content = content.replace(target_datos, replacement_datos)

with open(r"C:\laragon\www\certificaciones\controllers\gestion_materia.php", "w", encoding="utf-8") as f:
    f.write(content)


# 3. Update Materia.php (Model)
with open(r"C:\laragon\www\certificaciones\models\Materia.php", "r", encoding="utf-8") as f:
    content = f.read()

# Update SQL UPDATE
target_update = "fecha_fin = :fecha_fin"
replacement_update = "fecha_fin = :fecha_fin,\n                              exento_prelacion = :exento"
content = content.replace(target_update, replacement_update)

# Update SQL INSERT
target_insert_cols = "lapso_academico, temario, fecha_inicio, fecha_fin"
replacement_insert_cols = "lapso_academico, temario, fecha_inicio, fecha_fin, exento_prelacion"
content = content.replace(target_insert_cols, replacement_insert_cols)

target_insert_vals = ":lapso, :temario, :fecha_inicio, :fecha_fin"
replacement_insert_vals = ":lapso, :temario, :fecha_inicio, :fecha_fin, :exento"
content = content.replace(target_insert_vals, replacement_insert_vals)

# Add Bind Value
target_bind = "$stmt->bindValue(':temario', $data['temario'], PDO::PARAM_STR);"
replacement_bind = "$stmt->bindValue(':temario', $data['temario'], PDO::PARAM_STR);\n              $stmt->bindValue(':exento', $data['exento_prelacion'], PDO::PARAM_BOOL);"
content = content.replace(target_bind, replacement_bind)

with open(r"C:\laragon\www\certificaciones\models\Materia.php", "w", encoding="utf-8") as f:
    f.write(content)


# 4. Update pagos_controlador.php (Sequential bypass)
with open(r"C:\laragon\www\certificaciones\controllers\pagos_controlador.php", "r", encoding="utf-8") as f:
    content = f.read()

target_seq = "stripos($m['nombre_materia'], 'otro') !== false || stripos($m['nombre_materia'], 'repitencia') !== false || stripos($m['nombre_materia'], 'trabajo especial de grado') !== false || stripos($m['nombre_materia'], 'teg') !== false"
replacement_seq = "($m['exento_prelacion'] == 1 || $m['exento_prelacion'] == true) || stripos($m['nombre_materia'], 'repitencia') !== false"

content = content.replace(target_seq, replacement_seq)

with open(r"C:\laragon\www\certificaciones\controllers\pagos_controlador.php", "w", encoding="utf-8") as f:
    f.write(content)

print("Checkboxes and model updated successfully")
