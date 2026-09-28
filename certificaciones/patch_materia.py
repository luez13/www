import re

with open(r"C:\laragon\www\certificaciones\models\Materia.php", "r", encoding="utf-8") as f:
    content = f.read()

# Replace docente binding
content = content.replace('$stmt->bindValue(\':docente\', $data[\'docente_id\'], PDO::PARAM_INT);',
                          'if (empty($data[\'docente_id\'])) {\n                $stmt->bindValue(\':docente\', null, PDO::PARAM_NULL);\n            } else {\n                $stmt->bindValue(\':docente\', $data[\'docente_id\'], PDO::PARAM_INT);\n            }')

with open(r"C:\laragon\www\certificaciones\models\Materia.php", "w", encoding="utf-8") as f:
    f.write(content)
