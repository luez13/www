<?php
require 'config/model.php';
$db = new DB();
$stmt = $db->getConn()->query('SELECT * FROM cursos.roles');
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($roles as $r) {
    echo $r['id_rol'] . ' - ' . $r['nombre_rol'] . "\n";
}
