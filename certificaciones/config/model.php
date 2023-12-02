<?php
// Definir las constantes de configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'certificaciones_DB');
define('DB_USER', 'postgres');
define('DB_PASS', '0000');

// Crear la clase DB
class DB {
    // Crear una propiedad para guardar la conexión
    private $conn;

    // Crear el constructor de la clase
    public function __construct() {
        // Intentar conectar a la base de datos usando PDO
        try {
            $this->conn = new PDO('pgsql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
        } catch (PDOException $e) {
            // Mostrar un mensaje de error al usuario
            echo '<p>Ha ocurrido un error al conectar a la base de datos: ' . $e->getMessage() . '</p>';
        }
    }

    // Crear un método para preparar una sentencia SQL
    public function prepare($sql) {
        // Usar el método prepare de PDO y devolver el resultado
        return $this->conn->prepare($sql);
    }
}
?>