import re

with open(r"C:\laragon\www\certificaciones\public\perfil.php", "r", encoding="utf-8") as f:
    content = f.read()

target = r'<a class="collapse-item" href="#" onclick="loadCategory\(\'recepcion_pago\', true\)"><i\s*class="fas fa-money-bill-wave me-2 text-muted"></i>Pagos y Aranceles</a>'
replacement = r'<a class="collapse-item" href="#" onclick="loadCategory(\'pnfa\', true)"><i class="fas fa-university me-2 text-muted"></i>PNFA</a>\n                        <a class="collapse-item" href="#" onclick="loadCategory(\'recepcion_pago\', true)"><i class="fas fa-money-bill-wave me-2 text-muted"></i>Pagos y Aranceles</a>'

content = re.sub(target, replacement, content)

with open(r"C:\laragon\www\certificaciones\public\perfil.php", "w", encoding="utf-8") as f:
    f.write(content)

print("perfil.php added PNFA to catalog")
