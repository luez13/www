import re

# 1. FIX editar_cursos.php
path_editar = r"C:\laragon\www\certificaciones\public\editar_cursos.php"
with open(path_editar, "r", encoding="utf-8") as f:
    content = f.read()

content = content.replace("!esPerfil3($user_id) && !esPerfil4($user_id)", "!tieneAcceso([3, 4])")

with open(path_editar, "w", encoding="utf-8") as f:
    f.write(content)

# 2. FIX curso_controlador.php
path_ctrl = r"C:\laragon\www\certificaciones\controllers\curso_controlador.php"
with open(path_ctrl, "r", encoding="utf-8") as f:
    content = f.read()

content = content.replace("esPerfil3($user_id) || esPerfil4($user_id)", "tieneAcceso([3, 4])")

with open(path_ctrl, "w", encoding="utf-8") as f:
    f.write(content)

print("Permissions updated for Super Admin in Proposals!")
