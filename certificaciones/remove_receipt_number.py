import re

path = r"C:\laragon\www\certificaciones\public\recibo_pago.php"

with open(path, "r", encoding="utf-8") as f:
    content = f.read()

# Replace <title>
content = re.sub(r"<title>Recibo de Pago #<\?= str_pad\(\$pago\['id_comprobante'\], 6, '0', STR_PAD_LEFT\) \?></title>", 
                 "<title>Comprobante de Pago</title>", content)

# Remove Nº Recibo line
find_str = "<strong>Nº Recibo:</strong> REC-<?= str_pad($pago['id_comprobante'], 8, '0', STR_PAD_LEFT) ?><br>"
content = content.replace(find_str, "")

with open(path, "w", encoding="utf-8") as f:
    f.write(content)

print("Nº Recibo removido exitosamente.")
