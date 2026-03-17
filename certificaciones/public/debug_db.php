<?php
require_once __DIR__ . '/../config/model.php';
$db = new DB();
$stmt = $db->query("SELECT id_curso, nombre_curso, id_plantilla FROM cursos.cursos ORDER BY id_curso DESC LIMIT 10");
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt2 = $db->query("SELECT id_modulo, id_curso, nombre_modulo FROM cursos.modulos ORDER BY id_modulo DESC LIMIT 10");
$modulos = $stmt2->fetchAll(PDO::FETCH_ASSOC);

echo "<pre>";
echo "Últimos 10 Cursos:\n";
print_r($cursos);
echo "\nÚltimos 10 Módulos:\n";
print_r($modulos);
echo "</pre>";
