<?php
require 'c:/laragon/www/certificaciones/config/model.php';
$db = new DB();
$pdo = $db->getConn();
$stmt = $pdo->query('SELECT ccf.id_curso, ccf.id_cargo_firmante, c.nombre_cargo, pf.codigo_posicion FROM cursos.cursos_config_firmas ccf JOIN cursos.posiciones_firma pf ON ccf.id_posicion = pf.id_posicion LEFT JOIN cursos.cargos c ON ccf.id_cargo_firmante = c.id_cargo;');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
