<?php
// Definir las constantes de configuración de la base de datos
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'uptaivir_certificaciones');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'postgres');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', '0000');
}

// Crear la clase DB solo si no está definida
if (!class_exists('DB')) {
    class DB {

        // Crear una propiedad para guardar la conexión
        private $conn;

        // Crear el constructor de la clase
        public function __construct() {
            // Intentar conectar a la base de datos usando PDO
            try {
                $this->conn = new PDO('pgsql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Verificar si la sesión no está iniciada antes de llamar a session_start()
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                if (isset($_SESSION['user_id'])) {
                    $user_id = $_SESSION['user_id'];
                    $query = "SET myapp.current_user_id = '$user_id'";
                    $this->conn->exec($query);
                }

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

            public function getConn() {
            return $this->conn;
        }
    }
}
?>