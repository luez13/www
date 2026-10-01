<?php
require 'config/model.php'; 
$db = new DB(); 
$conn = $db->getConn(); 
$stmt = $conn->query("INSERT INTO cursos.extensiones (nombre_extension, es_academico, activa) VALUES ('Unidad de Grado', FALSE, TRUE)"); 
echo "Done";
