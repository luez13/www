<?php
require 'config/model.php';
$db = new DB();
$conn = $db->getConn();
$conn->exec("ALTER TABLE cursos.materias_bimestre ALTER COLUMN docente_id DROP NOT NULL");
echo "Constraint dropped locally";
