import re

with open(r"C:\laragon\www\certificaciones\views\historial.php", "r", encoding="utf-8") as f:
    content = f.read()

content = content.replace("Mis Cursos en Progreso", "<?= $_SESSION['es_academico'] ? 'Mis Cursos en Progreso' : 'Mis Inscripciones Activas' ?>")
content = content.replace("Historial de Cursos Finalizados", "<?= $_SESSION['es_academico'] ? 'Historial de Cursos Finalizados' : 'Historial' ?>")

# For modular certificates in finalizados view:
content = content.replace("<?php if (count($materias_aprobadas) > 0): ?>", "<?php if (count($materias_aprobadas) > 0 && $_SESSION['es_academico']): ?>")

with open(r"C:\laragon\www\certificaciones\views\historial.php", "w", encoding="utf-8") as f:
    f.write(content)

print("historial.php updated")
