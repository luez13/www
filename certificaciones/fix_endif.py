import re

path = r"C:\laragon\www\certificaciones\public\perfil.php"
with open(path, "r", encoding="utf-8") as f:
    content = f.read()

# Replace the double endif before the hr
content = re.sub(r"<\?php endif; \?>\s*<\?php endif; \?>\s*<hr class=\"sidebar-divider d-none d-md-block\">", 
                 "<?php endif; ?>\n\n        <hr class=\"sidebar-divider d-none d-md-block\">", content)

with open(path, "w", encoding="utf-8") as f:
    f.write(content)

print("perfil.php endif fixed!")
