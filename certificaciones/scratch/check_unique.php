<?php
include 'C:\laragon\www\certificaciones\config\model.php';
$db = new DB();
$pdo = $db->getConn();

$stmt = $pdo->query("
    SELECT conname, pg_get_constraintdef(c.oid)
    FROM pg_constraint c
    JOIN pg_namespace n ON n.oid = c.connamespace
    WHERE conrelid = 'cursos.usuarios'::regclass
");
$constraints = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($constraints);
?>
