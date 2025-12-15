<?php
// models/Materia.php

class Materia {
    private $conn;

    public function __construct($db_connection_pdo) {
        $this->conn = $db_connection_pdo;
    }

    // Obtiene todas las materias de un curso (diplomado)
    public function getMateriasByCurso($id_curso) {
        // Hacemos JOIN con usuarios para traer el nombre del docente
        $sql = "SELECT m.*, u.nombre as nombre_docente, u.apellido as apellido_docente 
                FROM cursos.materias_bimestre m
                LEFT JOIN cursos.usuarios u ON m.docente_id = u.id
                WHERE m.id_curso = :id_curso 
                ORDER BY m.id_materia_bimestre ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtiene una materia por ID
    public function getMateriaById($id_materia) {
        $sql = "SELECT m.*, u.nombre || ' ' || u.apellido as nombre_docente 
                FROM cursos.materias_bimestre m
                LEFT JOIN cursos.usuarios u ON m.docente_id = u.id
                WHERE m.id_materia_bimestre = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id_materia, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Guarda o Actualiza una materia
    public function saveMateria($data) {
        $id_materia = isset($data['id_materia_bimestre']) ? (int)$data['id_materia_bimestre'] : 0;
        
        try {
            if ($id_materia > 0) {
                // UPDATE
                $sql = "UPDATE cursos.materias_bimestre SET 
                            nombre_materia = :nombre, 
                            duracion_bimestres = :duracion, 
                            total_horas = :horas, 
                            modalidad = :modalidad, 
                            docente_id = :docente
                        WHERE id_materia_bimestre = :id";
                
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':id', $id_materia, PDO::PARAM_INT);
                
            } else {
                // INSERT
                $sql = "INSERT INTO cursos.materias_bimestre 
                            (id_curso, nombre_materia, duracion_bimestres, total_horas, modalidad, docente_id) 
                        VALUES 
                            (:id_curso, :nombre, :duracion, :horas, :modalidad, :docente)";
                
                $stmt = $this->conn->prepare($sql);
                if ($id_materia === 0) { // Solo bindear id_curso en insert
                    $stmt->bindValue(':id_curso', (int)$data['id_curso'], PDO::PARAM_INT);
                }
            }

            // Parámetros comunes
            $stmt->bindValue(':nombre', $data['nombre_materia']);
            $stmt->bindValue(':duracion', $data['duracion_bimestres']); // Ej: "Bimestre 1"
            $stmt->bindValue(':horas', (int)$data['total_horas']);
            $stmt->bindValue(':modalidad', $data['modalidad']);
            $stmt->bindValue(':docente', (int)$data['docente_id']);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Error en saveMateria: " . $e->getMessage());
            return false;
        }
    }

    // Elimina una materia
    public function deleteMateria($id_materia) {
        $sql = "DELETE FROM cursos.materias_bimestre WHERE id_materia_bimestre = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id_materia, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>