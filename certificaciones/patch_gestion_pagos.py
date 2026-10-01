import re

with open(r"C:\laragon\www\certificaciones\views\gestion_pagos.php", "r", encoding="utf-8") as f:
    content = f.read()

# 1. Add Fecha Aprob header
target_header = '<th class="text-left">Gestionado Por</th>'
replacement_header = '<th class="text-left">Gestionado Por</th>\n                                <th>Fecha Gestión</th>'
content = content.replace(target_header, replacement_header)

# 2. Add Fecha Gestion column in loop
target_td = """                                    <td class="text-left" style="max-width: 150px; white-space: normal;">
                                        <?php if (!empty($comp['admin_nombre'])): ?>
                                            <small class="text-secondary d-block"><i class="fas fa-user-shield me-1"></i><?= h($comp['admin_nombre'] . ' ' . $comp['admin_apellido']) ?></small>
                                        <?php else: ?>
                                            <span class="text-muted"><i class="fas fa-minus"></i></span>
                                        <?php endif; ?>
                                    </td>"""

replacement_td = """                                    <td class="text-left" style="max-width: 150px; white-space: normal;">
                                        <?php if (!empty($comp['admin_nombre'])): ?>
                                            <small class="text-secondary d-block"><i class="fas fa-user-shield me-1"></i><?= h($comp['admin_nombre'] . ' ' . $comp['admin_apellido']) ?></small>
                                        <?php else: ?>
                                            <span class="text-muted"><i class="fas fa-minus"></i></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= !empty($comp['fecha_gestion']) ? date('d/m/Y H:i', strtotime($comp['fecha_gestion'])) : '--' ?></small>
                                    </td>"""
content = content.replace(target_td, replacement_td)

# 3. Remove the Registrar Pago Manual button and modal
content = re.sub(r'<button[^>]*?data-bs-target="#modalPagoManual"[^>]*?>.*?</button>', '', content, flags=re.DOTALL)
content = re.sub(r'<div class="modal fade" id="modalPagoManual" tabindex="-1".*?</div>\s*</div>\s*</div>\s*</div>', '', content, flags=re.DOTALL)

with open(r"C:\laragon\www\certificaciones\views\gestion_pagos.php", "w", encoding="utf-8") as f:
    f.write(content)

print("gestion_pagos.php updated")
