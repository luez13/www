import re

with open(r"C:\laragon\www\certificaciones\views\gestion_pagos.php", "r", encoding="utf-8") as f:
    content = f.read()

# Replace the buttons block with a PHP check
old_buttons = """                                            <?php if ($sec['estado_filtro'] === 'Pendiente'): ?>
                                                <button type="button" class="btn btn-sm btn-success shadow-sm"
                                                    onclick="actualizarEstadoPago(<?= $comp['id_comprobante'] ?>, 'Comprobado')"
                                                    title="Aprobar">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger shadow-sm"
                                                    onclick="actualizarEstadoPago(<?= $comp['id_comprobante'] ?>, 'Rechazado')"
                                                    title="Rechazar">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-warning shadow-sm"
                                                    onclick="actualizarEstadoPago(<?= $comp['id_comprobante'] ?>, 'Pendiente')"
                                                    title="Revertir a Pendiente">
                                                    <i class="fas fa-undo text-dark"></i>
                                                </button>
                                            <?php endif; ?>"""

new_buttons = """                                            <?php if (tieneAcceso([5, 6])): ?>
                                            <?php if ($sec['estado_filtro'] === 'Pendiente'): ?>
                                                <button type="button" class="btn btn-sm btn-success shadow-sm"
                                                    onclick="actualizarEstadoPago(<?= $comp['id_comprobante'] ?>, 'Comprobado')"
                                                    title="Aprobar">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger shadow-sm"
                                                    onclick="actualizarEstadoPago(<?= $comp['id_comprobante'] ?>, 'Rechazado')"
                                                    title="Rechazar">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-warning shadow-sm"
                                                    onclick="actualizarEstadoPago(<?= $comp['id_comprobante'] ?>, 'Pendiente')"
                                                    title="Revertir a Pendiente">
                                                    <i class="fas fa-undo text-dark"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php endif; ?>"""

content = content.replace(old_buttons, new_buttons)

with open(r"C:\laragon\www\certificaciones\views\gestion_pagos.php", "w", encoding="utf-8") as f:
    f.write(content)

print("gestion_pagos.php buttons hidden")
