<?php
require 'C:/laragon/www/certificaciones/config/model.php';
$db = new DB();
$stmt = $db->prepare("UPDATE cursos.comprobantes_pago SET estado = 'Comprobado' WHERE estado = 'Aprobado'");
$stmt->execute();
echo 'OK';
