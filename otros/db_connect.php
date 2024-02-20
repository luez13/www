<?php
$host = "localhost";
$db   = "pruebas";
$user = "root";
$pass = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log($e->getMessage());
    die('Error al conectar con la base de datos');
}
?>