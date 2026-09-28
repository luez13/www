<?php
require 'config/model.php'; 
$db = new DB(); 
$r = $db->getConn()->query('SELECT * FROM cursos.extensiones')->fetchAll(PDO::FETCH_ASSOC); 
print_r($r);
