<?php
// Asegúrate de que las rutas sean correctas. 
require_once('../config/model.php'); 

$error_message = null;
$success_message = null;

try {
    // 1. Conexión a la base de datos
    $db_instance = new DB();
    $pdo_conn = $db_instance->getConn();

    // 2. Consulta SQL con el esquema 'curso.' (¡CORREGIDO!)
    $sql = "INSERT INTO cursos.posiciones_firma (id_posicion, codigo_posicion, descripcion_posicion, pagina)
            VALUES (:id_posicion, :codigo_posicion, :descripcion_posicion, :pagina)
            ON CONFLICT (id_posicion) DO NOTHING"; 
    
    // Si tu restricción de unicidad es por codigo_posicion, usa: 
    // ON CONFLICT (codigo_posicion) DO NOTHING

    $stmt = $pdo_conn->prepare($sql);

    // 3. Definición de los parámetros
    $params = [
        ':id_posicion' => 6,
        ':codigo_posicion' => 'P2_INF_CEN',
        ':descripcion_posicion' => 'Pie de página 2, Centrada',
        ':pagina' => 2
    ];

    // 4. Ejecutar la consulta
    $result = $stmt->execute($params);

    if ($result && $stmt->rowCount() > 0) {
        $success_message = "✅ La posición 'P2_INF_CEN' fue insertada exitosamente en el esquema `curso`.";
    } elseif ($result && $stmt->rowCount() === 0) {
        $success_message = "⚠️ La posición 'P2_INF_CEN' con ID 6 ya existía en el esquema `curso` y no se modificó (ON CONFLICT).";
    } else {
        $error_message = "❌ Error desconocido al ejecutar la consulta.";
    }

} catch (PDOException $e) {
    // 5. Manejo de errores de PDO (Base de Datos)
    $error_message = "❌ Error de conexión o SQL: " . $e->getMessage();
} catch (\Exception $e) {
    // 6. Manejo de otros errores
    $error_message = "❌ Error general: " . $e->getMessage();
}

// --- Vista HTML Sencilla ---
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Insertar Posición de Firma</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; }
        .btn-back { display: block; margin-top: 20px; padding: 10px; background-color: #007bff; color: white; text-align: center; text-decoration: none; border-radius: 5px; width: 200px; }
    </style>
</head>
<body>
    <h1>Herramienta: Inserción de Posición de Firma</h1>

    <?php if ($success_message): ?>
        <p class="success"><?= htmlspecialchars($success_message) ?></p>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <p class="error"><?= htmlspecialchars($error_message) ?></p>
        <p>Por favor, revisa la conexión en `config/model.php` y los permisos de la base de datos.</p>
    <?php endif; ?>

    <button onclick="window.history.back()" class="btn-back">Volver</button>
</body>
</html>