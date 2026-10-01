import re

with open(r"C:\laragon\www\certificaciones\public\gestion_cursos.php", "r", encoding="utf-8") as f:
    content = f.read()

# First, remove costo from anywhere else
content = re.sub(r'<div class="col-md-6 mb-3" id="container_costo">.*?</div>', '', content, flags=re.DOTALL)

# Add Costo next to the first block (like Nivel del curso)
target_nivel = r'(<select class="form-select" name="nivel_curso" required>.*?</select>\s*</div>)'
replacement_nivel = r'\1\n<div class="col-md-4 mb-3"><label class="form-label">Costo (Obligatorio):</label><input class="form-control" type="number" name="costo" step="0.01" min="0" required></div>'

content = re.sub(target_nivel, replacement_nivel, content, flags=re.DOTALL)

with open(r"C:\laragon\www\certificaciones\public\gestion_cursos.php", "w", encoding="utf-8") as f:
    f.write(content)

print("gestion_cursos.php Costo updated")
