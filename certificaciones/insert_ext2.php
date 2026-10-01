<?php
require 'config/model.php'; 
$db = new DB(); 
$conn = $db->getConn(); 
$stmt = $conn->query("INSERT INTO cursos.extensiones (id_extension, nombre_extension, es_academico, activa) VALUES ((SELECT coalesce(max(id_extension),0)+1 FROM cursos.extensiones), 'Unidad de Grado', FALSE, TRUE)"); 
echo "Done";
