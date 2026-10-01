import re

with open(r"C:\laragon\www\certificaciones\public\gestion_cursos.php", "r", encoding="utf-8") as f:
    content = f.read()

# 1. Move Costo out of section_detalles_academicos to be permanently visible under the Tipo de Curso dropdown or similar.
# Let's find a good spot. "tipo_curso" is likely in a row. Let's find "tipo_curso"
target_costo = """                     <div class="col-md-6 mb-3" id="container_costo">
                        <label class="form-label">Costo:</label>
                        <input class="form-control" type="number" name="costo" step="0.01" min="0">
                    </div>"""
content = content.replace(target_costo, "")

target_costo_2 = """                     <div class="col-md-6 mb-3" id="container_costo">
                        <label class="form-label">Costo:</label>
                        <input class="form-control" type="number" name="costo" step="0.01" min="0">
                    </div>"""
content = re.sub(r'<div class="col-md-6 mb-3" id="container_costo">.*?</div>', '', content, flags=re.DOTALL)


# Let's put Costo right after "tipo_curso"
# Let's find where "tipo_curso" is
target_tipo = r'(<select class="form-select" name="tipo_curso" id="tipo_curso" required>.*?</select>\s*</div>)'
replacement_tipo = r'\1\n<div class="col-md-4 mb-3"><label class="form-label">Costo (Obligatorio):</label><input class="form-control" type="number" name="costo" step="0.01" min="0" required></div>'
content = re.sub(target_tipo, replacement_tipo, content, flags=re.DOTALL)

# 2. Modify JS so PNFA does NOT show modulos or detalles academicos
js_target = """            if (val === 'PNFA') {
                sec.style.display = 'block';
                toggleDetalles(true);
                numModulosInput.disabled = false;
                numModulosInput.required = true;
                title.innerHTML = '<i class="fas fa-layer-group me-2"></i>Trminos del PNFA';
                label.innerHTML = 'Nmero de trminos (Mx 12 + Otro):';
                window.isPNFA = true;
                if (numModulosInput.value) numModulosInput.dispatchEvent(new Event('blur'));
            }"""

js_target_regex = r"if \(val === 'PNFA'\) \{[\s\S]*?else if \(isAcad\) \{"

js_replacement = """if (val === 'PNFA') {
                sec.style.display = 'none';
                toggleDetalles(false);
                numModulosInput.disabled = true;
                numModulosInput.required = false;
                window.isPNFA = true;
            } else if (isAcad) {"""

content = re.sub(js_target_regex, js_replacement, content)

with open(r"C:\laragon\www\certificaciones\public\gestion_cursos.php", "w", encoding="utf-8") as f:
    f.write(content)

print("gestion_cursos.php updated")
