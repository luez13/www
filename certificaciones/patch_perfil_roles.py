import re

with open(r"C:\laragon\www\certificaciones\public\perfil.php", "r", encoding="utf-8") as f:
    content = f.read()

# 1. Remove Directorios y Cargos for Autorizador (Role 3)
# Currently it's under `<?php if (tieneAcceso([3, 4])): ?>` for "Gestión de Personas"
# We'll change it to `<?php if (tieneAcceso([4])): ?>` if that's the only other role. Wait, let's see.
target_gestion = r"<\?php if \(tieneAcceso\(\[3, 4\]\)\): \?>\s*<h6 class=\"collapse-header text-primary\">Gesti.n de Personas:</h6>"
replacement_gestion = r"<?php if (tieneAcceso([4])): ?>\n                        <h6 class=\"collapse-header text-primary\">Gestión de Personas:</h6>"
content = re.sub(target_gestion, replacement_gestion, content)

# 2. Add Taquilla Fisica for Autorizadores (Role 3)
target_taquilla = r"<\?php if \(tieneAcceso\(\[4, 5\]\)\): \?>\s*<a class=\"collapse-item font-weight-bold text-success\" href=\"#\" onclick=\"loadPage\('\.\./public/taquilla_pagos\.php'\)\)>"
replacement_taquilla = r"<?php if (tieneAcceso([3, 4, 5])): ?>\n                        <a class=\"collapse-item font-weight-bold text-success\" href=\"#\" onclick=\"loadPage('../public/taquilla_pagos.php')\">"
content = re.sub(target_taquilla, replacement_taquilla, content)

with open(r"C:\laragon\www\certificaciones\public\perfil.php", "w", encoding="utf-8") as f:
    f.write(content)

print("perfil.php updated")
