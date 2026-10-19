import re

with open(r"C:\laragon\www\certificaciones\public\gestion_cursos.php", "r", encoding="utf-8") as f:
    content = f.read()

content = content.replace('<label class="form-label">Tipo de curso:</label>', 
                          '<label class="form-label"><?= $_SESSION[\'es_academico\'] ? \'Tipo de curso:\' : \'Tipo de propuesta / concepto:\' ?></label>')

with open(r"C:\laragon\www\certificaciones\public\gestion_cursos.php", "w", encoding="utf-8") as f:
    f.write(content)

print("gestion_cursos.php updated")
