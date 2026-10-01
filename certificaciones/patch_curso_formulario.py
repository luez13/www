import re

with open(r"C:\laragon\www\certificaciones\views\curso_formulario.php", "r", encoding="utf-8") as f:
    content = f.read()

# Remove Costo from Detalles Academicos
content = re.sub(r'<div class="col-md-4 mb-3" id="container_costo">.*?</div>', '', content, flags=re.DOTALL)
content = re.sub(r'<div class="col-md-6 mb-3" id="container_costo">.*?</div>', '', content, flags=re.DOTALL)
content = re.sub(r'<div class="col-md-\d+ mb-3" id="container_costo">.*?</div>', '', content, flags=re.DOTALL)
# Also just in case it doesn't have an ID
content = re.sub(r'<div class="col-md-\d+ mb-3">\s*<label class="form-label">Costo:</label>.*?</div>', '', content, flags=re.DOTALL)


# Put Costo next to Tipo Curso
target_tipo = r'(<select class="form-select" name="tipo_curso" id="tipo_curso" required>.*?</select>\s*</div>)'
replacement_tipo = r'\1\n<div class="col-md-4 mb-3"><label class="form-label">Costo (Obligatorio):</label><input class="form-control" type="number" name="costo" value="<?= isset($curso_editar[\'costo\']) ? htmlspecialchars($curso_editar[\'costo\']) : \'\' ?>" step="0.01" min="0" required></div>'
content = re.sub(target_tipo, replacement_tipo, content, flags=re.DOTALL)


# Remove PNFA block showing "Terminos del PNFA" inside curso_formulario.php
# Because PNFA no longer has "modulos" created at the same time
target_pnfa_modulos = r'<\?php \$is_pnfa = \(isset\(\$curso_editar\[\'tipo_curso\'\]\) && \$curso_editar\[\'tipo_curso\'\] === \'PNFA\'\); \?>\s*<\?php if \(\$_SESSION\[\'es_academico\'\]\): \?>\s*<div class="card shadow mb-4">[\s\S]*?</div>\s*</div>\s*<\?php endif; \?>'
content = re.sub(target_pnfa_modulos, '', content, flags=re.DOTALL)
# wait, if $_SESSION['es_academico'] is true, they need to see "Modulos del Curso" for diplomados!
# Let's check how modulos block was constructed. It was:
# <?php $is_pnfa = ...
# <?php if ($_SESSION['es_academico']): ?>
# <div class="card shadow mb-4"> ...
# We should just make it so "Modulos del Curso" ONLY shows if NOT PNFA.
