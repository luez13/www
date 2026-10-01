import re

with open(r"C:\laragon\www\certificaciones\public\perfil.php", "r", encoding="utf-8") as f:
    content = f.read()

target = """                <div id="collapseComunidad" class="collapse" aria-labelledby="headingComunidad"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded shadow-sm">

                        <?php if (tieneAcceso([3, 4])): ?>"""

replacement = """                <div id="collapseComunidad" class="collapse" aria-labelledby="headingComunidad"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded shadow-sm">

                        <?php if (tieneAcceso([3, 4])): ?>"""

# Let's search for "Sistema y Ajustes" block
target_ajustes = """            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAjustes"
                    aria-expanded="true" aria-controls="collapseAjustes">
                    <i class="fas fa-fw fa-cogs"></i>
                    <span>Sistema y Ajustes</span>
                </a>
                <div id="collapseAjustes" class="collapse" aria-labelledby="headingAjustes" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded shadow-sm">
                        <?php if (tieneAcceso([4])): ?>
                        <h6 class="collapse-header text-primary">Ajustes Generales:</h6>
                        <a class="collapse-item" href="#" onclick="loadPage('../views/admin_auditoria.php')"><i
                                class="fas fa-shield-alt me-2"></i> Auditora y Seguridad</a>
                        <a class="collapse-item" href="#" onclick="loadPage('../views/ajustes_sistema.php')"><i
                                class="fas fa-sliders-h me-2"></i> Ajustes del Sistema</a>
                        <a class="collapse-item" href="#" onclick="loadPage('../views/ajustes_landing.php')"><i
                                class="fas fa-laptop-house me-2"></i> Ajustes de Pgina Principal</a>
                        <?php endif; ?>

                        <?php if (tieneAcceso([4, 6])): ?>
                        <h6 class="collapse-header text-success mt-2">Gestin de Cuentas:</h6>
                        <a class="collapse-item" href="#" onclick="loadPage('../views/gestion_cuentas_bancarias.php')"><i
                                class="fas fa-piggy-bank me-2"></i> Cuentas Bancarias</a>
                        <?php endif; ?>

                        <?php if (tieneAcceso([4, 5])): ?>
                        <h6 class="collapse-header text-warning mt-2">Personal y Usuarios:</h6>
                        <a class="collapse-item" href="#" onclick="loadPage('../views/gestionar_cargos.php')"><i
                                class="fas fa-user-tie me-2"></i> Firmantes y Cargos</a>
                        <?php endif; ?>

                        <?php if (tieneAcceso([4])): ?>
                        <a class="collapse-item" href="#" onclick="loadPage('../views/usuarios.php')"><i
                                class="fas fa-users me-2"></i> Usuarios y Permisos</a>
                        <?php endif; ?>
                    </div>
                </div>
            </li>"""

replacement_ajustes = """          <?php if (tieneAcceso([4, 5, 6])): ?>
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAjustes"
                    aria-expanded="true" aria-controls="collapseAjustes">
                    <i class="fas fa-fw fa-cogs"></i>
                    <span>Sistema y Ajustes</span>
                </a>
                <div id="collapseAjustes" class="collapse" aria-labelledby="headingAjustes" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded shadow-sm">
                        <?php if (tieneAcceso([4])): ?>
                        <h6 class="collapse-header text-primary">Ajustes Generales:</h6>
                        <a class="collapse-item" href="#" onclick="loadPage('../views/admin_auditoria.php')"><i
                                class="fas fa-shield-alt me-2"></i> Auditora y Seguridad</a>
                        <a class="collapse-item" href="#" onclick="loadPage('../views/ajustes_sistema.php')"><i
                                class="fas fa-sliders-h me-2"></i> Ajustes del Sistema</a>
                        <a class="collapse-item" href="#" onclick="loadPage('../views/ajustes_landing.php')"><i
                                class="fas fa-laptop-house me-2"></i> Ajustes de Pgina Principal</a>
                        <?php endif; ?>

                        <?php if (tieneAcceso([4, 6])): ?>
                        <h6 class="collapse-header text-success mt-2">Gestin de Cuentas:</h6>
                        <a class="collapse-item" href="#" onclick="loadPage('../views/gestion_cuentas_bancarias.php')"><i
                                class="fas fa-piggy-bank me-2"></i> Cuentas Bancarias</a>
                        <?php endif; ?>

                        <?php if (tieneAcceso([4, 5])): ?>
                        <h6 class="collapse-header text-warning mt-2">Personal y Usuarios:</h6>
                        <a class="collapse-item" href="#" onclick="loadPage('../views/gestionar_cargos.php')"><i
                                class="fas fa-user-tie me-2"></i> Firmantes y Cargos</a>
                        <?php endif; ?>

                        <?php if (tieneAcceso([4])): ?>
                        <a class="collapse-item" href="#" onclick="loadPage('../views/usuarios.php')"><i
                                class="fas fa-users me-2"></i> Usuarios y Permisos</a>
                        <?php endif; ?>
                    </div>
                </div>
            </li>
          <?php endif; ?>"""

import urllib.parse
# Actually we can just do a regex replace to be safe.
# Find the exact text ignoring strange characters
start_idx = content.find('<li class="nav-item">\n                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAjustes"')
if start_idx != -1:
    end_idx = content.find('</li>', start_idx) + 5
    old_block = content[start_idx:end_idx]
    new_block = "<?php if (tieneAcceso([4, 5, 6])): ?>\n" + old_block + "\n<?php endif; ?>"
    content = content[:start_idx] + new_block + content[end_idx:]

with open(r"C:\laragon\www\certificaciones\public\perfil.php", "w", encoding="utf-8") as f:
    f.write(content)
