<?php
require 'config/model.php';
$db = new DB();
$conn = $db->getConn();

try {
    $stmt = $conn->query("SELECT * FROM cursos.extensiones");
    $extensiones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Extensiones:\n";
    print_r($extensiones);

    $stmt = $conn->query("SELECT * FROM cursos.usuarios_extensiones LIMIT 5");
    $ue = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Usuarios_Extensiones:\n";
    print_r($ue);
    
    // Check columns
    $tables = ['cursos', 'cuentas_bancarias', 'comprobantes_pago', 'cargos'];
    foreach ($tables as $t) {
        $stmt = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_schema = 'cursos' AND table_name = '$t' AND column_name = 'id_extension'");
        if ($stmt->fetch()) {
            echo "Table $t HAS id_extension.\n";
        } else {
            echo "Table $t MISSING id_extension.\n";
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
