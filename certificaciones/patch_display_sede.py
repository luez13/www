import re

with open(r"C:\laragon\www\certificaciones\models\Pago.php", "r", encoding="utf-8") as f:
    content = f.read()

# Make sure obtenerCuentas joins extensiones to get the name
old_obtener = """public function obtenerCuentas()
    {
        $sql = "SELECT * FROM cursos.cuentas_bancarias ORDER BY id_cuenta DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }"""
new_obtener = """public function obtenerCuentas()
    {
        $sql = "SELECT c.*, e.nombre_extension 
                FROM cursos.cuentas_bancarias c
                LEFT JOIN cursos.extensiones e ON c.id_extension = e.id_extension 
                ORDER BY c.id_cuenta DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }"""
content = content.replace(old_obtener, new_obtener)

with open(r"C:\laragon\www\certificaciones\models\Pago.php", "w", encoding="utf-8") as f:
    f.write(content)

with open(r"C:\laragon\www\certificaciones\views\gestion_cuentas_bancarias.php", "r", encoding="utf-8") as f:
    content2 = f.read()

# Add Sede column to header
old_th = """<th style="width: 15%;">Tipo / Nro.</th>"""
new_th = """<th style="width: 10%;">Sede</th>\n                              <th style="width: 15%;">Tipo / Nro.</th>"""
content2 = content2.replace(old_th, new_th)

# Add Sede column to rows
old_td = """<td class="align-middle text-left">
                                        <span class="badge badge-info mb-1"><?= h($c['tipo_cuenta']) ?></span><br>"""
new_td = """<td class="align-middle font-weight-bold text-primary">
                                        <?= $c['id_extension'] ? h($c['nombre_extension']) : 'Global' ?>
                                    </td>
                                    <td class="align-middle text-left">
                                        <span class="badge badge-info mb-1"><?= h($c['tipo_cuenta']) ?></span><br>"""
content2 = content2.replace(old_td, new_td)

with open(r"C:\laragon\www\certificaciones\views\gestion_cuentas_bancarias.php", "w", encoding="utf-8") as f:
    f.write(content2)
