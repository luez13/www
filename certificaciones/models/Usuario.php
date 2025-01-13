<?php
class Usuario {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function obtenerUsuarios($limit, $offset) {
        $stmt = $this->db->prepare('SELECT id, nombre, cedula, correo FROM cursos.usuarios LIMIT :limit OFFSET :offset');
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarUsuarios() {
        $stmt = $db->prepare('SELECT COUNT(*) FROM cursos.usuarios');
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function buscarUsuarios($busqueda, $limit, $offset) {
        $stmt = $this->db->prepare('SELECT id, nombre, cedula, correo FROM cursos.usuarios WHERE nombre LIKE :busqueda OR correo LIKE :busqueda LIMIT :limit OFFSET :offset');
        $busqueda = "%$busqueda%";
        $stmt->bindParam(':busqueda', $busqueda, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>