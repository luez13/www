<?php
require 'config/model.php';
$db = new DB();
$stmt = $db->getConn()->query('SELECT id_curso, nombre_curso, tipo_curso FROM cursos.cursos ORDER BY id_curso DESC LIMIT 5');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
