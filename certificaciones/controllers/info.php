<?php
// Desactiva la compresión de salida si estuviera activa
if (ini_get('zlib.output_compression')) {
    ini_set('zlib.output_compression', 'Off');
}

// 1. Mostrar información detallada de PHP
echo '<div style="margin-bottom: 30px;">';
echo '<h1>Información Completa de PHP</h1>';
phpinfo();
echo '</div>';

// --------------------------------------------------------------------------------
// 2. Información Clave (Resumen)
echo '<hr>';
echo '<div style="padding: 20px; background-color: #f8f9fa; border: 1px solid #dee2e6;">';
echo '<h2>Resumen de Entorno</h2>';
echo '<p><strong>Versión de PHP:</strong> ' . phpversion() . '</p>';

// Intentar determinar el SGBD y su versión (asume que usas MySQL o MariaDB y PDO)
if (extension_loaded('pdo_mysql')) {
    echo '<p><strong>Extensión PDO MySQL:</strong> Cargada</p>';
    // Nota: La versión específica de la base de datos generalmente requiere una conexión activa
    // Para obtenerla, necesitarías incluir la conexión a la DB.
    echo '<p><strong>Versión Estimada de la Base de Datos:</strong> Necesita conexión activa para un valor exacto.</p>';
} elseif (extension_loaded('mysqli')) {
    echo '<p><strong>Extensión MySQLi:</strong> Cargada</p>';
} else {
    echo '<p><strong>Extensión de Base de Datos:</strong> No se detectó MySQLi ni PDO MySQL.</p>';
}

echo '</div>';
// --------------------------------------------------------------------------------

// Si ya tienes una conexión a la DB activa ($db en tu código original), puedes añadir esto para ver la versión de DB:
/*
if (isset($db) && $db instanceof PDO) {
    try {
        $db_version = $db->query('SELECT VERSION()')->fetchColumn();
        echo '<p><strong>Versión Real de la Base de Datos:</strong> ' . $db_version . '</p>';
    } catch (PDOException $e) {
        echo '<p>No se pudo obtener la versión de la DB: ' . $e->getMessage() . '</p>';
    }
}
*/
?>