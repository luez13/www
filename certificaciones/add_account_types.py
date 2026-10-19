import re

path = r"C:\laragon\www\certificaciones\views\gestion_cuentas_bancarias.php"
with open(path, "r", encoding="utf-8") as f:
    content = f.read()

find_str = """                                <option value="Transferencia Internacional">Transferencia Internacional</option>
                            </select>"""
replace_str = """                                <option value="Transferencia Internacional">Transferencia Internacional</option>
                                <option value="Caja Física (Efectivo)">Caja Física (Efectivo)</option>
                                <option value="Bóveda (Efectivo)">Bóveda (Efectivo)</option>
                            </select>"""

content = content.replace(find_str, replace_str)

with open(path, "w", encoding="utf-8") as f:
    f.write(content)

print("Added Caja and Boveda to options!")
