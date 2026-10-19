import re

with open(r"C:\laragon\www\certificaciones\public\perfil.php", "r", encoding="utf-8") as f:
    content = f.read()

# 1. Update Tesoreria y Pagos to include Role 3
content = re.sub(r"<\?php if \(tieneAcceso\(\[4, 5, 6\]\)\): \?>\s*<h6 class=\"collapse-header text-success\">Tesorera y Pagos:</h6>", 
                 "<?php if (tieneAcceso([3, 4, 5, 6])): ?>\n                          <h6 class=\"collapse-header text-success\">Tesorería y Pagos:</h6>", 
                 content)

# Update Auditar Pagos to not be restricted inside (it wasn't restricted anyway, just under the [4,5,6] wrapper)
# But Taquilla Fisica is restricted to [3, 4, 5], which we already did `tieneAcceso([3, 4, 5])`.
# Let's check Cuentas Destino:
content = re.sub(r"<\?php if \(tieneAcceso\(\[4, 6\]\)\): \?>\s*<a class=\"collapse-item\"", 
                 "<?php if (tieneAcceso([3, 4, 6])): ?>\n                          <a class=\"collapse-item\"", 
                 content)

# 2. Update "Mi Aprendizaje" block
# Remove es_academico restriction
content = re.sub(r"<\?php if \(tieneAcceso\(\[1, 2, 3, 4, 5, 6\]\) && \$_SESSION\['es_academico'\]\): \?>", 
                 "<?php if (tieneAcceso([1, 2, 3, 4, 5, 6])): ?>", 
                 content)

content = content.replace("<span>Mi Aprendizaje</span>", "<span>Mis Inscripciones</span>")
content = content.replace("Mi Expediente:", "Mi Expediente:")
content = content.replace("Cursos en Progreso", "Inscripciones Activas")
content = content.replace("Cursos Finalizados", "Historial")

with open(r"C:\laragon\www\certificaciones\public\perfil.php", "w", encoding="utf-8") as f:
    f.write(content)

print("perfil.php updated with correct roles and text")
