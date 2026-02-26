<?php
// models/Materia.php

class Materia {
    private $conn;

    public function __construct($db_connection_pdo) {
        $this->conn = $db_connection_pdo;
    }

    // Obtiene todas las materias de un curso, ORDENADAS POR LAPSO
    public function getMateriasByCurso($id_curso) {
        // Ordenamos por lapso_academico primero, luego por id
        $sql = "SELECT m.*, u.nombre as nombre_docente, u.apellido as apellido_docente 
                FROM cursos.materias_bimestre m
                LEFT JOIN cursos.usuarios u ON m.docente_id = u.id
                WHERE m.id_curso = :id_curso 
                ORDER BY m.lapso_academico ASC, m.id_materia_bimestre ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMateriaById($id_materia) {
        // Incluimos lapso_academico en la selección
        $sql = "SELECT m.*, u.nombre || ' ' || u.apellido as nombre_docente 
                FROM cursos.materias_bimestre m
                LEFT JOIN cursos.usuarios u ON m.docente_id = u.id
                WHERE m.id_materia_bimestre = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id_materia, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

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
                            docente_id = :docente,
                            lapso_academico = :lapso
                        WHERE id_materia_bimestre = :id";
                
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':id', $id_materia, PDO::PARAM_INT);
                
            } else {
                // INSERT
                $sql = "INSERT INTO cursos.materias_bimestre 
                            (id_curso, nombre_materia, duracion_bimestres, total_horas, modalidad, docente_id, lapso_academico) 
                        VALUES 
                            (:id_curso, :nombre, :duracion, :horas, :modalidad, :docente, :lapso)";
                
                $stmt = $this->conn->prepare($sql);
                if ($id_materia === 0) { 
                    $stmt->bindValue(':id_curso', (int)$data['id_curso'], PDO::PARAM_INT);
                }
            }

            // Parámetros comunes
            $stmt->bindValue(':nombre', $data['nombre_materia']);
            $stmt->bindValue(':duracion', $data['duracion_bimestres']);
            $stmt->bindValue(':horas', (int)$data['total_horas']);
            $stmt->bindValue(':modalidad', $data['modalidad']);
            $stmt->bindValue(':docente', (int)$data['docente_id']);
            
            // Nuevo parámetro: Lapso (si no viene, por defecto es 1)
            $lapso = isset($data['lapso_academico']) ? (int)$data['lapso_academico'] : 1;
            $stmt->bindValue(':lapso', $lapso, PDO::PARAM_INT);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Error en saveMateria: " . $e->getMessage());
            return false;
        }
    }

    public function deleteMateria($id_materia) {
        $sql = "DELETE FROM cursos.materias_bimestre WHERE id_materia_bimestre = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id_materia, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>