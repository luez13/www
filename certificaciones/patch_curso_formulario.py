import re

with open(r"C:\laragon\www\certificaciones\views\curso_formulario.php", "r", encoding="utf-8") as f:
    content = f.read()

costo_block = """<div class="col-md-4 mb-3">
                        <label class="form-label">Costo:</label>
                        <input class="form-control" type="number" name="costo"
                            value="<?= htmlspecialchars($curso_editar['costo']) ?>" step="0.01" min="0">
                    </div>"""

# Remove Costo from Detalles Académicos
content = content.replace(costo_block, "")

# Insert Costo in the first general block, near Nivel del curso
target = """<div class="col-md-4 mb-3">
                        <label class="form-label">Nivel del curso:</label>
                        <select class="form-select" name="nivel_curso" required>
                            <option value="Básico" <?= (isset($curso_editar['nivel_curso']) && $curso_editar['nivel_curso'] == 'Básico') ? 'selected' : '' ?>>Básico</option>
                            <option value="Intermedio" <?= (isset($curso_editar['nivel_curso']) && $curso_editar['nivel_curso'] == 'Intermedio') ? 'selected' : '' ?>>
                                Intermedio</option>
                            <option value="Avanzado" <?= (isset($curso_editar['nivel_curso']) && $curso_editar['nivel_curso'] == 'Avanzado') ? 'selected' : '' ?>>
                                Avanzado</option>
                        </select>
                    </div>
                    <?php endif; ?>"""

insert_costo = """<div class="col-md-4 mb-3">
                        <label class="form-label">Nivel del curso:</label>
                        <select class="form-select" name="nivel_curso" required>
                            <option value="Básico" <?= (isset($curso_editar['nivel_curso']) && $curso_editar['nivel_curso'] == 'Básico') ? 'selected' : '' ?>>Básico</option>
                            <option value="Intermedio" <?= (isset($curso_editar['nivel_curso']) && $curso_editar['nivel_curso'] == 'Intermedio') ? 'selected' : '' ?>>
                                Intermedio</option>
                            <option value="Avanzado" <?= (isset($curso_editar['nivel_curso']) && $curso_editar['nivel_curso'] == 'Avanzado') ? 'selected' : '' ?>>
                                Avanzado</option>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Costo Arancelario (Dejar en 0 si es gratuito, o usar decimales para $):</label>
                        <input class="form-control" type="number" name="costo"
                            value="<?= isset($curso_editar['costo']) ? htmlspecialchars($curso_editar['costo']) : '0' ?>" step="0.01" min="0">
                    </div>"""

content = content.replace(target, insert_costo)

with open(r"C:\laragon\www\certificaciones\views\curso_formulario.php", "w", encoding="utf-8") as f:
    f.write(content)
