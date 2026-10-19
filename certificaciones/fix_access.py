import os

path_perfil = r"C:\laragon\www\certificaciones\public\perfil.php"

with open(path_perfil, "r", encoding="utf-8") as f:
    content = f.read()

# Replace the specific Taquilla block
content = content.replace("<?php if (tieneAcceso([4, 5])): ?>\n                          <a class=\"collapse-item font-weight-bold text-success\" href=\"#\" \nonclick=\"loadPage('../public/taquilla_pagos.php')", 
                          "<?php if (tieneAcceso([3, 4, 5])): ?>\n                          <a class=\"collapse-item font-weight-bold text-success\" href=\"#\" \nonclick=\"loadPage('../public/taquilla_pagos.php')")

# If it has carriage returns, let's just do a more flexible regex
import re
content = re.sub(r"<\?php if \(tieneAcceso\(\[4, 5\]\)\): \?>(\s*<a class=\"collapse-item[^\>]*taquilla_pagos\.php)", 
                 r"<?php if (tieneAcceso([3, 4, 5])): ?>\1", 
                 content)

with open(path_perfil, "w", encoding="utf-8") as f:
    f.write(content)

# Now fix Cuentas Bancarias view permissions
path_cuentas = r"C:\laragon\www\certificaciones\views\gestion_cuentas_bancarias.php"
with open(path_cuentas, "r", encoding="utf-8") as f:
    cuentas = f.read()

cuentas = re.sub(r"if \(\!tieneAcceso\(\[6\]\)\) \{", "if (!tieneAcceso([3, 4, 6])) {", cuentas)
with open(path_cuentas, "w", encoding="utf-8") as f:
    f.write(cuentas)

print("perfil and cuentas updated")
