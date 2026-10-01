import re

with open(r"C:\laragon\www\certificaciones\controllers\gestion_materia.php", "r", encoding="utf-8") as f:
    content = f.read()

target = "'docente_id'          => (!empty($_POST['docente_id']) ? $_POST['docente_id'] : \n$_SESSION['user_id']),"
replacement = "'docente_id'          => (!empty($_POST['docente_id']) ? $_POST['docente_id'] : null),"
content = content.replace(target, replacement)

# also if it's slightly different
target2 = "'docente_id'          => (!empty($_POST['docente_id']) ? $_POST['docente_id'] : $_SESSION['user_id']),"
content = content.replace(target2, replacement)

with open(r"C:\laragon\www\certificaciones\controllers\gestion_materia.php", "w", encoding="utf-8") as f:
    f.write(content)

print("gestion_materia updated")
