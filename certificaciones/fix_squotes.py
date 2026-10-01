import re

with open(r"C:\laragon\www\certificaciones\public\perfil.php", "r", encoding="utf-8") as f:
    content = f.read()

content = content.replace(r"\'", "'")

with open(r"C:\laragon\www\certificaciones\public\perfil.php", "w", encoding="utf-8") as f:
    f.write(content)

print("perfil.php fixed quotes again")
