<?php
class Sugerencia {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function agregarSugerencia($nombre, $apellido, $correo, $cedula, $sugerencia, $id_usuario = null) {
        try {
            $sql = "INSERT INTO cursos.buzon_sugerencias (nombre, apellido, correo, cedula, sugerencia, id_usuario) VALUES (:nombre, :apellido, :correo, :cedula, :sugerencia, :id_usuario)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':apellido', $apellido);
            $stmt->bindParam(':correo', $correo);
            $stmt->bindParam(':cedula', $cedula);
            $stmt->bindParam(':sugerencia', $sugerencia);
            $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            // Registrar el error
            error_log("Error al agregar sugerencia: " . $e->getMessage());
            return false;
        }
    }
}