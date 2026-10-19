import re
import os

path = r"C:\laragon\www\certificaciones\public\perfil.php"

with open(path, "r", encoding="utf-8") as f:
    content = f.read()

# 1. Update "Comunidad y Tesoreria" menu access
# Current Tesoreria y Pagos header has `[3, 4, 5, 6]` wrapper.
# Auditar Pagos is not wrapped inside. It just shows for everyone in the `[3, 4, 5, 6]` block.
# We need to wrap Auditar Pagos with `<?php if (tieneAcceso([4, 5, 6])): ?>` so Role 3 cannot see it.
auditar_pagos_str = """                        <h6 class="collapse-header text-success">Tesorería y Pagos:</h6>
                        <a class="collapse-item" href="#" onclick="loadPage('../views/gestion_pagos.php')"><i
                                class="fas fa-search-dollar me-2 text-muted"></i>Auditar Pagos</a>"""
# Wait, let's make sure about the exact text by checking the previous output
# It was:
# <h6 class="collapse-header text-success">Tesorería y Pagos:</h6>
# <a class="collapse-item" href="#" onclick="loadPage('../views/gestion_pagos.php')"><i
#         class="fas fa-search-dollar me-2 text-muted"></i>Auditar Pagos</a>

# 2. Update "Cuentas Destino". Currently `<?php if (tieneAcceso([3, 4, 6])): ?>`. Change to `[4, 6]`.

# 3. Update "Sistema y Ajustes". Currently wrapped in `[3, 4]` and `[4, 5, 6]`. Change to just `[7]`.

