<?php
// controllers/buscar_usuarios_ajax.php
require_once '../config/model.php';

// Verificar sesión
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($busqueda) < 3) {
    echo json_encode([]); // Retornar vacío si hay menos de 3 caracteres para no saturar
    exit;
}

$db = new DB();
$conn = $db->getConn();

try {
    // Buscamos por Nombre, Apellido o Cédula
    // Hacemos JOIN con roles para mostrar el rol del usuario
    $sql = "SELECT u.id, u.nombre, u.apellido, u.cedula, u.titulo, r.nombre_rol 
            FROM cursos.usuarios u
            LEFT JOIN cursos.roles r ON u.id_rol = r.id_rol
            WHERE u.nombre ILIKE :b 
               OR u.apellido ILIKE :b 
               OR u.cedula ILIKE :b 
            LIMIT 20"; // Limitamos a 20 resultados para no saturar

    $stmt = $conn->prepare($sql);
    $stmt->execute(['b' => '%' . $busqueda . '%']);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($usuarios);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>