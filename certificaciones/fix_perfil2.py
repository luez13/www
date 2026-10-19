import os

path = r"C:\laragon\www\certificaciones\public\perfil.php"

with open(path, "r", encoding="utf-8") as f:
    lines = f.readlines()

for i, line in enumerate(lines):
    if "<?php if (tieneAcceso([4, 5, 6])):" in line and i < len(lines)-1 and "Tesorer" in lines[i+1]:
        lines[i] = line.replace("tieneAcceso([4, 5, 6])", "tieneAcceso([3, 4, 5, 6])")
    
    if "<?php if (tieneAcceso([4, 5])):" in line and i < len(lines)-1 and "Taquilla F" in lines[i+1]:
        lines[i] = line.replace("tieneAcceso([4, 5])", "tieneAcceso([3, 4, 5])")

with open(path, "w", encoding="utf-8") as f:
    f.writelines(lines)

print("perfil.php hard fixed")
