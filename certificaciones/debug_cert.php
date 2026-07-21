<?php
require_once __DIR__ . '/config/model.php';
$db = new DB();
$stmt = $db->getConn()->query("SELECT c.id_curso, c.nombre_curso FROM cursos.certificaciones ce JOIN cursos.cursos c ON ce.curso_id = c.id_curso WHERE ce.id_usuario = 2");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
