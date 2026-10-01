import re

with open(r"C:\laragon\www\certificaciones\public\perfil.php", "r", encoding="utf-8") as f:
    content = f.read()

# Original block has:
#           <?php if (tieneAcceso([3, 4, 5, 6])): ?>
#             <hr class="sidebar-divider">
#             <div class="sidebar-heading">Institucional</div>
#             ...
#                 <div id="collapseComunidad" ...
#             ...
#           <li class="nav-item">
#               <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAjustes"
#                   aria-expanded="true" aria-controls="collapseAjustes">
#                   <i class="fas fa-fw fa-cogs"></i>
#                   <span>Sistema y Ajustes</span>
#               </a>

# I will wrap the nav-item for Sistema y Ajustes inside `if (tieneAcceso([4, 5, 6])):`
target = """            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAjustes\""""

replacement = """          <?php if (tieneAcceso([4, 5, 6])): ?>
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAjustes\""""

content = content.replace(target, replacement)

target_end = """                            <a class="collapse-item" href="#" onclick="loadPage('../views/usuarios.php')"><i
                                    class="fas fa-users me-2"></i> Usuarios y Permisos</a>
                            <?php endif; ?>
                        </div>
                    </div>
            </li>"""

replacement_end = """                            <a class="collapse-item" href="#" onclick="loadPage('../views/usuarios.php')"><i
                                    class="fas fa-users me-2"></i> Usuarios y Permisos</a>
                            <?php endif; ?>
                        </div>
                    </div>
            </li>
          <?php endif; ?>"""

content = content.replace(target_end, replacement_end)

with open(r"C:\laragon\www\certificaciones\public\perfil.php", "w", encoding="utf-8") as f:
    f.write(content)
