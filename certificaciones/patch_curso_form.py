import re

with open(r"C:\laragon\www\certificaciones\views\curso_formulario.php", "r", encoding="utf-8") as f:
    content = f.read()

content = content.replace('<label class="form-label">Tipo de curso:</label>', 
                          '<label class="form-label"><?= $_SESSION[\'es_academico\'] ? \'Tipo de curso:\' : \'Tipo de propuesta / concepto:\' ?></label>')

# Let's also check public/gestion_cursos.php for "Tipo de curso"
with open(r"C:\laragon\www\certificaciones\views\curso_formulario.php", "w", encoding="utf-8") as f:
    f.write(content)

print("curso_formulario.php updated")
