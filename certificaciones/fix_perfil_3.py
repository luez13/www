import re

path = r"C:\laragon\www\certificaciones\public\perfil.php"
with open(path, "r", encoding="utf-8") as f:
    content = f.read()

# 1. Tesorería y Pagos Wrapper -> [3, 4, 5, 6] remains the same because Taquilla is inside it.
# But Auditar Pagos needs its own wrapper so Role 3 can't see it: [4, 5, 6].
# Wait, let's just wrap Auditar Pagos directly.
auditar_pagos_find = """<a class="collapse-item" href="#" onclick="loadPage('../views/gestion_pagos.php')"><i
                                class="fas fa-search-dollar me-2 text-muted"></i>Auditar Pagos</a>"""

auditar_pagos_replace = """<?php if (tieneAcceso([4, 5, 6])): ?>
                        <a class="collapse-item" href="#" onclick="loadPage('../views/gestion_pagos.php')"><i
                                class="fas fa-search-dollar me-2 text-muted"></i>Auditar Pagos</a>
                        <?php endif; ?>"""

content = content.replace(auditar_pagos_find, auditar_pagos_replace)

# 2. Cuentas Destino: change `[3, 4, 6]` to `[4, 6]`.
content = content.replace("<?php if (tieneAcceso([3, 4, 6])): ?>", "<?php if (tieneAcceso([4, 6])): ?>")

# 3. Sistema y Ajustes: change outer wrapper `[3, 4]` to `[]` and `[4, 5, 6]` to `[]` -> Actually, just `[7]` so only Super Admin sees it?
# The user said: "el rol 4 ... pero no a las cosas de programacion ni nada, como la consola, landing comentarios y demas"
# The outer wrappers were `[3, 4]` and `[4, 5, 6]` making only `[4]` able to see it.
# Let's change BOTH of those outer wrappers into a single `[7]`.
# Wait, `[7]` will work perfectly because Role 7 bypasses, but since we removed Role 4 from the bypass, Role 4 won't see it if we specify `[7]`.
# Actually, I can just replace `<?php if (tieneAcceso([3, 4])): ?>\n            <?php if (tieneAcceso([4, 5, 6])): ?>` with `<?php if (false): // Solo Super Admin ?>` No, `if (tieneAcceso([]))` works perfectly since only Role 7 returns true for `tieneAcceso([])`.
# Let's just use regex to replace the first `[3, 4]` block related to collapseAjustes.
ajustes_find = r"<\?php if \(tieneAcceso\(\[3, 4\]\)\): \?>\s*<\?php if \(tieneAcceso\(\[4, 5, 6\]\)\): \?>"
ajustes_replace = r"<?php if (tieneAcceso([])): // Vacío para que solo el Rol 7 (SUPER_ADMIN) pase el bypass ?>"
content = re.sub(ajustes_find, ajustes_replace, content)

# But wait, there are two `<?php endif; ?>` at the end of Sistema y Ajustes. If I collapse the two opening ifs into one, I need to remove one endif.
# Let's just replace `[3, 4]` with `[]` and `[4, 5, 6]` with `[]`.
content = re.sub(r"<\?php if \(tieneAcceso\(\[3, 4\]\)\): \?>\s*<\?php if \(tieneAcceso\(\[4, 5, 6\]\)\): \?>", 
                 r"<?php if (tieneAcceso([])): ?>\n            <?php if (tieneAcceso([])): ?>", content)

with open(path, "w", encoding="utf-8") as f:
    f.write(content)

print("perfil.php updated!")
