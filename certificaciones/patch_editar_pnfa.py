import re

with open(r"C:\laragon\www\certificaciones\public\editar_cursos.php", "r", encoding="utf-8") as f:
    content = f.read()

# Replace condition for Fechas y Horarios
content = re.sub(
    r"<\?php if \(\$_SESSION\['es_academico'\] \|\| \(isset\(\$curso\['tipo_curso'\]\) && \$curso\['tipo_curso'\] === 'PNFA'\)\): \?>",
    r"<?php if ($_SESSION['es_academico']): ?>",
    content
)

# Replace condition for Modulos del Curso
content = re.sub(
    r"<\?php if \(\$_SESSION\['es_academico'\] \|\| \$is_pnfa\): \?>",
    r"<?php if ($_SESSION['es_academico']): ?>",
    content
)

with open(r"C:\laragon\www\certificaciones\public\editar_cursos.php", "w", encoding="utf-8") as f:
    f.write(content)
