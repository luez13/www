import re

with open(r"C:\laragon\www\certificaciones\controllers\gestion_materia.php", "r", encoding="utf-8") as f:
    content = f.read()

# Replace validation
content = content.replace('if (empty($_POST[\'docente_id\'])) throw new Exception("Debe seleccionar un docente.");',
                          'if (empty($_POST[\'docente_id\']) && $_SESSION[\'es_academico\']) throw new Exception("Debe seleccionar un docente.");')

# Replace assignment
content = content.replace('\'docente_id\'          => $_POST[\'docente_id\'],',
                          '\'docente_id\'          => (!empty($_POST[\'docente_id\']) ? $_POST[\'docente_id\'] : NULL),')

with open(r"C:\laragon\www\certificaciones\controllers\gestion_materia.php", "w", encoding="utf-8") as f:
    f.write(content)

with open(r"C:\laragon\www\certificaciones\views\gestionar_materias.php", "r", encoding="utf-8") as f:
    content2 = f.read()

content2 = content2.replace("if ($('#docente_id').val() == '') { alert('Falta el docente'); return; }",
                            "if ($('#docente_id').length > 0 && $('#docente_id').is(':visible') && $('#docente_id').val() == '') { alert('Falta el docente'); return; }")

with open(r"C:\laragon\www\certificaciones\views\gestionar_materias.php", "w", encoding="utf-8") as f:
    f.write(content2)

