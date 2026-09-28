<?php
require 'config/model.php'; 
$db = new DB(); 
$r = $db->getConn()->query("SELECT pg_get_constraintdef(oid) FROM pg_constraint WHERE conrelid = 'cursos.cursos'::regclass")->fetchAll(PDO::FETCH_ASSOC); 
print_r($r);
$r2 = $db->getConn()->query("SELECT column_name, data_type, character_maximum_length FROM information_schema.columns WHERE table_schema = 'cursos' AND table_name = 'cursos'")->fetchAll(PDO::FETCH_ASSOC);
print_r($r2);