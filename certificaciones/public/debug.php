<?php
session_start();
echo "<h3>Estado actual de la sesión en el navegador:</h3><pre>";
var_export($_SESSION);
echo "</pre>";

require '../config/model.php';
$db = new DB();
$r = $db->getConn()->query("SELECT id_extension, nombre_extension, es_academico FROM cursos.extensiones")->fetchAll(PDO::FETCH_ASSOC);
echo "<h3>Lo que PHP ve realmente en la Base de Datos:</h3><pre>";
var_export($r);
echo "</pre>";
?>