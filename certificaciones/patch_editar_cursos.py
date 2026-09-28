import re

with open(r"C:\laragon\www\certificaciones\public\editar_cursos.php", "r", encoding="utf-8") as f:
    content = f.read()

# Hide Notas/Actas buttons
old_buttons = """<a href="#" class="btn btn-info btn-icon-split"
                                                    onclick="loadPage('../views/gestionar_notas.php', { id_curso: <?= $curso['id_curso'] ?> }); return false;">
                                                    <span class="icon text-white-50"><i class="fas fa-fw fa-calculator"></i></span>
                                                    <span class="text">Notas/Calificaciones</span>
                                                </a>
                                                <a href="#" class="btn btn-success btn-icon-split"
                                                    onclick="loadPage('../views/admin_actas.php', { id_curso: <?= $curso['id_curso'] ?> }); return false;">
                                                    <span class="icon text-white-50"><i
                                                            class="fas fa-fw fa-file-signature"></i></span>
                                                    <span class="text">Acta de Cierre Final</span>
                                                </a>"""

new_buttons = """<?php if ($_SESSION['es_academico']): ?>
                                                <a href="#" class="btn btn-info btn-icon-split"
                                                    onclick="loadPage('../views/gestionar_notas.php', { id_curso: <?= $curso['id_curso'] ?> }); return false;">
                                                    <span class="icon text-white-50"><i class="fas fa-fw fa-calculator"></i></span>
                                                    <span class="text">Notas/Calificaciones</span>
                                                </a>
                                                <a href="#" class="btn btn-success btn-icon-split"
                                                    onclick="loadPage('../views/admin_actas.php', { id_curso: <?= $curso['id_curso'] ?> }); return false;">
                                                    <span class="icon text-white-50"><i
                                                            class="fas fa-fw fa-file-signature"></i></span>
                                                    <span class="text">Acta de Cierre Final</span>
                                                </a>
                                                <?php endif; ?>"""

content = content.replace(old_buttons, new_buttons)

# Extract Costo from es_academico block
# Costo is currently in "Detalles Académicos"
# Let's move it to "Información General" right after "Nivel del curso"

# First, remove it from Detalles Académicos
costo_block = """<div class="col-md-4 mb-3">
                                                <label class="form-label">Costo</label>
                                                <input type="number" class="form-control" name="costo"
                                                    value="<?= h($curso['costo']) ?>" step="0.01">
                                            </div>"""

content = content.replace(costo_block, "")

# Now insert it into "Información General"
# It has this block:
target_block = """<div class="col-md-6 mb-3">
                                                <label class="form-label">Nivel del curso</label>
                                                <input type="text" class="form-control" name="nivel_curso"
                                                    value="<?= h($curso['nivel_curso']) ?>" required>
                                            </div>
                                            <?php endif; ?>"""

insert_costo = """<div class="col-md-6 mb-3">
                                                <label class="form-label">Nivel del curso</label>
                                                <input type="text" class="form-control" name="nivel_curso"
                                                    value="<?= h($curso['nivel_curso']) ?>" required>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Costo Arancelario (Dejar en 0 si es gratuito, o usar decimales para $)</label>
                                                <input type="number" class="form-control" name="costo"
                                                    value="<?= h($curso['costo']) ?>" step="0.01">
                                            </div>"""

content = content.replace(target_block, insert_costo)

with open(r"C:\laragon\www\certificaciones\public\editar_cursos.php", "w", encoding="utf-8") as f:
    f.write(content)
