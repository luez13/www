<?php
require 'config/model.php';
$db = new DB();
$pdo = $db->getConn();
$stmt = $pdo->query("SELECT id, nombre, id_extension FROM cursos.usuarios WHERE correo = 'luisdavid@gmail.com'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
