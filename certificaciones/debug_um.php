<?php
require_once __DIR__ . '/config/model.php';
$db = new DB();
$stmt = $db->getConn()->query("SELECT m.nombre_materia, um.nota_regular, um.nota_recuperativa, um.estado FROM cursos.usuario_materias um JOIN cursos.materias_bimestre m ON um.id_materia_bimestre = m.id_materia_bimestre WHERE um.id_usuario = 2");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
