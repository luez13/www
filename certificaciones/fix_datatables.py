import re

path = r"C:\laragon\www\certificaciones\views\gestion_pagos.php"

with open(path, "r", encoding="utf-8") as f:
    content = f.read()

# I need to insert the `fecha_gestion` td
find_str = """                                        <?php if (!empty($comp['admin_nombre'])): ?>
                                            <small class="text-primary d-block font-weight-bold">
                                                <?= h($comp['admin_nombre'] . ' ' . $comp['admin_apellido']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-left">
                                        <strong><?= h($comp['banco_origen'] ? $comp['banco_origen'] : 'N/A') ?></strong>"""

replace_str = """                                        <?php if (!empty($comp['admin_nombre'])): ?>
                                            <small class="text-primary d-block font-weight-bold">
                                                <?= h($comp['admin_nombre'] . ' ' . $comp['admin_apellido']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-left">
                                        <?= isset($comp['fecha_gestion']) && $comp['fecha_gestion'] ? date('d/m/Y H:i', strtotime($comp['fecha_gestion'])) : 'N/A' ?>
                                    </td>
                                    <td class="text-left">
                                        <strong><?= h($comp['banco_origen'] ? $comp['banco_origen'] : 'N/A') ?></strong>"""

content = content.replace(find_str, replace_str)

with open(path, "w", encoding="utf-8") as f:
    f.write(content)

print("Fixed gestion_pagos.php TD count")
