<?php
require_once 'controllers/autenticacion.php';
session_start();
$_SESSION['rol_id'] = 7;
var_dump(tieneAcceso([1, 2, 3, 4, 5, 6]));
