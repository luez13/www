import re

with open(r"C:\laragon\www\certificaciones\controllers\gestion_materia.php", "r", encoding="utf-8") as f:
    content = f.read()

# 1. Bypass exception if not es_academico
old_exception = 'if (empty($_POST[\'docente_id\'])) throw new Exception("Debe seleccionar un docente.");'
new_exception = 'if (empty($_POST[\'docente_id\']) && (isset($_SESSION[\'es_academico\']) && $_SESSION[\'es_academico\'])) throw new Exception("Debe seleccionar un docente.");'
content = content.replace(old_exception, new_exception)

# 2. Inject user_id into docente_id if empty
old_docente = "'docente_id'          => $_POST['docente_id'],"
new_docente = "'docente_id'          => (!empty($_POST['docente_id']) ? $_POST['docente_id'] : $_SESSION['user_id']),"
content = content.replace(old_docente, new_docente)

with open(r"C:\laragon\www\certificaciones\controllers\gestion_materia.php", "w", encoding="utf-8") as f:
    f.write(content)
