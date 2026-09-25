<?php
require_once __DIR__ . '/config/model.php';
try {
    $db = new DB();
    $conn = $db->getConn();
    $conn->exec("INSERT INTO cursos.usuarios_extensiones (id_usuario, id_extension) VALUES (2, 2) ON CONFLICT DO NOTHING;");
    $conn->exec("INSERT INTO cursos.usuarios_extensiones (id_usuario, id_extension) VALUES (43, 2) ON CONFLICT DO NOTHING;");
    echo "Extension assigned.";
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
