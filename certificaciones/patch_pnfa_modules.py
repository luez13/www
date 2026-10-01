import re

with open(r"C:\laragon\www\certificaciones\views\curso_formulario.php", "r", encoding="utf-8") as f:
    content = f.read()

# Replace condition for Fechas y Horarios
content = re.sub(
    r"<\?php if \(\$_SESSION\['es_academico'\] \|\| \(isset\(\$curso_editar\['tipo_curso'\]\) && \$curso_editar\['tipo_curso'\] === 'PNFA'\)\): \?>",
    r"<?php if ($_SESSION['es_academico']): ?>",
    content
)

# Replace condition for Modulos del Curso
content = re.sub(
    r"<\?php if \(\$_SESSION\['es_academico'\] \|\| \$is_pnfa\): \?>",
    r"<?php if ($_SESSION['es_academico']): ?>",
    content
)

with open(r"C:\laragon\www\certificaciones\views\curso_formulario.php", "w", encoding="utf-8") as f:
    f.write(content)
