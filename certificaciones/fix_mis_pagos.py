import re

path_mis_pagos = r"C:\laragon\www\certificaciones\views\mis_pagos.php"

with open(path_mis_pagos, "r", encoding="utf-8") as f:
    content = f.read()

# Fix Javascript hiding global accounts
find_js1 = r"if \(!idExt \|\| cuenta\.getAttribute\('data-id-extension'\) === idExt\) \{"
replace_js1 = r"if (!idExt || !cuenta.getAttribute('data-id-extension') || cuenta.getAttribute('data-id-extension') === idExt) {"
content = re.sub(find_js1, replace_js1, content)

find_js2 = r"if \(!idExt \|\| opt\.getAttribute\('data-id-extension'\) === idExt\) \{"
replace_js2 = r"if (!idExt || !opt.getAttribute('data-id-extension') || opt.getAttribute('data-id-extension') === idExt) {"
content = re.sub(find_js2, replace_js2, content)

with open(path_mis_pagos, "w", encoding="utf-8") as f:
    f.write(content)

print("mis_pagos.php updated!")
