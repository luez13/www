import re

# Update mis_pagos.php
with open(r"C:\laragon\www\certificaciones\views\mis_pagos.php", "r", encoding="utf-8") as f:
    content = f.read()

content = content.replace('minlength="4"', 'minlength="6"')
content = content.replace('pattern="[0-9]{4,}"', 'pattern="[0-9]{6,}"')
content = content.replace('menos 4 d', 'menos 6 d')
content = content.replace('minimo 4', 'minimo 6')

with open(r"C:\laragon\www\certificaciones\views\mis_pagos.php", "w", encoding="utf-8") as f:
    f.write(content)


# Update gestion_pagos.php
with open(r"C:\laragon\www\certificaciones\views\gestion_pagos.php", "r", encoding="utf-8") as f:
    content = f.read()

content = content.replace('minlength="4"', 'minlength="6"')
content = content.replace('pattern="[0-9]{4,}"', 'pattern="[0-9]{6,}"')

with open(r"C:\laragon\www\certificaciones\views\gestion_pagos.php", "w", encoding="utf-8") as f:
    f.write(content)
