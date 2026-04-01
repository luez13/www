<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config/model.php';
require_once 'models/curso.php';
$db = new DB();
$c = new Curso($db);
$res = $c->duplicar(3);
if ($res) { echo "Success: $res"; } else { echo "Failed!"; }
