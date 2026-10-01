import re

with open(r"C:\laragon\www\certificaciones\views\gestionar_materias.php", "r", encoding="utf-8") as f:
    content = f.read()

target = r'<label class="form-check-label font-weight-bold text-danger" for="exento_prelacion">.*?<i class="fas fa-unlock-alt me-1"></i> Eximir de secuencia prelatoria\? \(Estudiantes podrn pagar en cualquier momento\).*?</label>'

replacement = """<label class="form-check-label font-weight-bold" style="color: #d9534f; opacity: 0.85;" for="exento_prelacion">
                                <i class="fas fa-unlock-alt me-1"></i> Eximir de secuencia prelatoria? (Ej: Otros o Trabajo Especial de Grado)
                            </label>"""

content = re.sub(target, replacement, content, flags=re.DOTALL)

with open(r"C:\laragon\www\certificaciones\views\gestionar_materias.php", "w", encoding="utf-8") as f:
    f.write(content)

print("Checklabel fixed")
