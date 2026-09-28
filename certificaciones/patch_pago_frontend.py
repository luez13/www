import re

with open(r"C:\laragon\www\certificaciones\views\gestion_cuentas_bancarias.php", "r", encoding="utf-8") as f:
    content = f.read()

# 1. Fetch extensions at the top
old_top = """$db = new DB();
$pagoModel = new Pago($db);
$cuentas = $pagoModel->obtenerCuentas();"""

new_top = """$db = new DB();
$pagoModel = new Pago($db);
$cuentas = $pagoModel->obtenerCuentas();

$stmt_ext = $db->getConn()->query("SELECT id_extension, nombre_extension FROM cursos.extensiones ORDER BY nombre_extension ASC");
$extensiones = $stmt_ext->fetchAll(PDO::FETCH_ASSOC);"""

content = content.replace(old_top, new_top)

# 2. Add id_extension field
old_banco = """<div class="col-md-6 form-group">
                            <label class="font-weight-bold">Banco <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="banco" name="banco"
                                placeholder="Ej: Banesco, Mercantil, Zelle" required>
                        </div>"""

new_banco = """<div class="col-md-12 form-group">
                            <label class="font-weight-bold">Sede / Departamento <span class="text-danger">*</span></label>
                            <select class="form-control" id="id_extension" name="id_extension" required>
                                <option value="">-- Seleccione --</option>
                                <option value="0">Global (Todas las Sedes / General)</option>
                                <?php foreach ($extensiones as $ext): ?>
                                    <option value="<?= $ext['id_extension'] ?>"><?= htmlspecialchars($ext['nombre_extension']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Selecciona "Global" si esta cuenta recibe pagos de cualquier sede.</small>
                        </div>

                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Banco <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="banco" name="banco"
                                placeholder="Ej: Banesco, Mercantil, Zelle" required>
                        </div>"""

content = content.replace(old_banco, new_banco)

# 3. Add to JS reset
old_reset = """$('#cuenta_action').val('crear_cuenta');
        $('#id_cuenta').val('');"""

new_reset = """$('#cuenta_action').val('crear_cuenta');
        $('#id_cuenta').val('');
        $('#id_extension').val('');"""
content = content.replace(old_reset, new_reset)

# 4. Add to JS edit
old_edit = """$('#cuenta_action').val('actualizar_cuenta');
        $('#id_cuenta').val(cuenta.id_cuenta);"""

new_edit = """$('#cuenta_action').val('actualizar_cuenta');
        $('#id_cuenta').val(cuenta.id_cuenta);
        $('#id_extension').val(cuenta.id_extension || '0');"""
content = content.replace(old_edit, new_edit)

with open(r"C:\laragon\www\certificaciones\views\gestion_cuentas_bancarias.php", "w", encoding="utf-8") as f:
    f.write(content)
