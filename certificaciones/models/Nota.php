<?php
// models/Nota.php

class Nota {
    private $conn;

    public function __construct($pdo) {
        $this->conn = $pdo;
    }

    // 1. Obtener el Plan de Evaluación de una materia
    public function getPlanEvaluacion($id_materia) {
        $sql = "SELECT * FROM cursos.actividades_config 
                WHERE id_materia_bimestre = :id_materia 
                ORDER BY id_actividad_config ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id_materia' => $id_materia]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. Guardar el Plan de Evaluación (Borra lo anterior y crea nuevo - Simplificación)
    public function guardarPlanEvaluacion($id_materia, $actividades) {
        try {
            $this->conn->beginTransaction();

            // A. Validar que sume 100% (o 20 pts, según lógica, usaremos 100%)
            $total = 0;
            foreach($actividades as $act) $total += $act['porcentaje'];
            if($total != 100) throw new Exception("La ponderación total debe sumar exactamente 100% (Suma actual: $total%)");

            // B. Limpiar plan anterior (Cuidado: esto borra notas asociadas por la FK cascade)
            // En producción idealmente se edita, pero para este MVP recreamos
            // OJO: Si ya hay notas cargadas, esto es peligroso. 
            // Para simplificar: Asumimos que si editan el plan, resetean las notas.
            $stmt_del = $this->conn->prepare("DELETE FROM cursos.actividades_config WHERE id_materia_bimestre = :id");
            $stmt_del->execute(['id' => $id_materia]);

            // C. Insertar nuevas actividades
            $sql = "INSERT INTO cursos.actividades_config (id_materia_bimestre, nombre_actividad, ponderacion_porcentaje) 
                    VALUES (:id, :nombre, :porc)";
            $stmt = $this->conn->prepare($sql);

            foreach($actividades as $act) {
                $stmt->execute([
                    'id' => $id_materia,
                    'nombre' => $act['nombre'],
                    'porc' => $act['porcentaje']
                ]);
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    // 3. Obtener Notas Detalladas de una Materia
    public function getNotasDetalladas($id_materia) {
        // Obtener alumnos
        // NOTA: Asumimos que id_curso viene del join con materia
        $sql_users = "SELECT u.id, u.nombre, u.apellido, u.cedula 
                      FROM cursos.usuarios u
                      JOIN cursos.certificaciones c ON u.id = c.id_usuario
                      JOIN cursos.materias_bimestre m ON c.curso_id = m.id_curso
                      WHERE m.id_materia_bimestre = :id_materia
                      ORDER BY u.apellido";
        $stmt = $this->conn->prepare($sql_users);
        $stmt->execute(['id_materia' => $id_materia]);
        $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener notas
        $sql_notas = "SELECT np.id_usuario, np.id_actividad_config, np.calificacion_obtenida
                      FROM cursos.notas_participante np
                      JOIN cursos.actividades_config ac ON np.id_actividad_config = ac.id_actividad_config
                      WHERE ac.id_materia_bimestre = :id_materia";
        $stmt_n = $this->conn->prepare($sql_notas);
        $stmt_n->execute(['id_materia' => $id_materia]);
        $notas_raw = $stmt_n->fetchAll(PDO::FETCH_ASSOC);

        // Mapear notas
        $mapa = [];
        foreach($notas_raw as $n) $mapa[$n['id_usuario']][$n['id_actividad_config']] = $n['calificacion_obtenida'];

        foreach($alumnos as &$alum) {
            $alum['notas_actividad'] = isset($mapa[$alum['id']]) ? $mapa[$alum['id']] : [];
        }

        return $alumnos;
    }

    // 4. Guardar Notas Detalladas
    public function guardarNotasDetalladas($notas) {
        // $notas es array [id_usuario][id_actividad] = valor
        $sql = "INSERT INTO cursos.notas_participante (id_usuario, id_actividad_config, calificacion_obtenida)
                VALUES (:user, :act, :val)
                ON CONFLICT (id_usuario, id_actividad_config) 
                DO UPDATE SET calificacion_obtenida = EXCLUDED.calificacion_obtenida";
        $stmt = $this->conn->prepare($sql);

        foreach($notas as $user_id => $actividades) {
            foreach($actividades as $act_id => $valor) {
                if($valor === '') continue;
                $stmt->execute([
                    'user' => $user_id,
                    'act' => $act_id,
                    'val' => $valor
                ]);
            }
        }
        return true;
    }
    
    // 5. Obtener promedios generales (para la vista principal)
    public function getPromediosMaterias($id_curso) {
        // Esta consulta calcula el promedio ponderado real basado en las actividades
        $sql = "SELECT 
                    u.id as id_usuario, 
                    m.id_materia_bimestre,
                    SUM(np.calificacion_obtenida * (ac.ponderacion_porcentaje / 100)) as promedio_calculado
                FROM cursos.usuarios u
                JOIN cursos.certificaciones c ON u.id = c.id_usuario
                JOIN cursos.materias_bimestre m ON c.curso_id = m.id_curso
                LEFT JOIN cursos.actividades_config ac ON m.id_materia_bimestre = ac.id_materia_bimestre
                LEFT JOIN cursos.notas_participante np ON u.id = np.id_usuario AND ac.id_actividad_config = np.id_actividad_config
                WHERE m.id_curso = :id_curso
                GROUP BY u.id, m.id_materia_bimestre";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id_curso' => $id_curso]);
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $salida = [];
        foreach($res as $r) {
            $salida[$r['id_usuario']][$r['id_materia_bimestre']] = number_format($r['promedio_calculado'], 2);
        }
        return $salida;
    }
}
?>