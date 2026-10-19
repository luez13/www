import re

path = r"C:\laragon\www\certificaciones\controllers\taquilla_controlador.php"
with open(path, "r", encoding="utf-8") as f:
    content = f.read()

# Fix the mess powershell made
content = re.sub(r" = \$_SESSION\['user_id'\];\s*if \(!isset\(\$_POST\['action'\]\)\) \{", 
                 r"\n$id_admin = $_SESSION['user_id'];\nif (!isset($_POST['action'])) {", content)

with open(path, "w", encoding="utf-8") as f:
    f.write(content)
