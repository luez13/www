<?php
require 'config/model.php'; 
$db = new DB(); 
$r = $db->getConn()->query('SELECT nota, completado, tomo, folio FROM cursos.certificaciones WHERE curso_id = 5 OR curso_id = 139 LIMIT 10')->fetchAll(PDO::FETCH_ASSOC); 
print_r($r);
