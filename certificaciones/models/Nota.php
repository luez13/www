<?php
// models/Nota.php

class Nota
{
    private $conn;

    public function __construct($pdo)
    {
        $this->conn = $pdo;
    }

    // 1. Obtener el Plan de Evaluación de una materia
    public function getPlanEvaluacion($id_materia)
    {
        $sql = "SELECT * FROM cursos.actividades_config 
                WHERE id_materia_bimestre = :id_materia 
                ORDER BY id_actividad_config ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id_materia' => $id_materia]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. Guardar el Plan de Evaluación (Borra lo anterior y crea nuevo - Simplificación)
    // En models/Nota.php

    public function guardarPlanEvaluacion($id_materia, $actividades)
    {
        try {
            $this->conn->beginTransaction();

            // 1. Validar la suma del 100%
            $total = 0;
            foreach ($actividades as $act)
                $total += $act['porcentaje'];

            // Pequeña tolerancia por decimales flotantes, aunque lo ideal es entero
            if (abs($total - 100) > 0.1) {
                throw new Exception("La ponderación total debe sumar exactamente 100% (Suma actual: $total%)");
            }

            // 2. Obtener IDs actuales en la base de datos para esta materia
            $stmt_ids = $this->conn->prepare("SELECT id_actividad_config FROM cursos.actividades_config WHERE id_materia_bimestre = :id");
            $stmt_ids->execute(['id' => $id_materia]);
            $ids_en_db = $stmt_ids->fetchAll(PDO::FETCH_COLUMN);

            // 3. Procesar las actividades que vienen del formulario
            $ids_recibidos = [];

            $sql_update = "UPDATE cursos.actividades_config SET nombre_actividad = :nombre, ponderacion_porcentaje = :porc WHERE id_actividad_config = :id_act";
            $stmt_update = $this->conn->prepare($sql_update);

            $sql_insert = "INSERT INTO cursos.actividades_config (id_materia_bimestre, nombre_actividad, ponderacion_porcentaje) VALUES (:id_mat, :nombre, :porc)";
            $stmt_insert = $this->conn->prepare($sql_insert);

            foreach ($actividades as $act) {
                // Si trae ID, es una edición
                if (!empty($act['id']) && is_numeric($act['id'])) {
                    $ids_recibidos[] = $act['id'];
                    $stmt_update->execute([
                        'nombre' => $act['nombre'],
                        'porc' => $act['porcentaje'],
                        'id_act' => $act['id']
                    ]);
                }
                // Si NO trae ID, es una inserción nueva
                else {
                    $stmt_insert->execute([
                        'id_mat' => $id_materia,
                        'nombre' => $act['nombre'],
                        'porc' => $act['porcentaje']
                    ]);
                }
            }

            // 4. Eliminar SOLO las actividades que estaban en la BD pero no llegaron en el formulario
            // OJO: Esto borrará las notas asociadas a ESAS actividades específicas por el CONSTRAINT CASCADE (si existe)
            // o fallará si no hay cascada (protegiendo los datos).

            $ids_a_borrar = array_diff($ids_en_db, $ids_recibidos);

            if (!empty($ids_a_borrar)) {
                // Convertir array a string separado por comas para el IN (seguro porque son integers)
                $in_query = implode(',', array_map('intval', $ids_a_borrar));

                // Opcional: Verificar si tienen notas antes de borrar (Si quieres ser muy estricto)
                /* $check = $this->conn->query("SELECT COUNT(*) FROM cursos.notas_participante WHERE id_actividad_config IN ($in_query)");
                if ($check->fetchColumn() > 0) throw new Exception("No se pueden eliminar actividades que ya tienen notas cargadas.");
                */

                $this->conn->exec("DELETE FROM cursos.actividades_config WHERE id_actividad_config IN ($in_query)");
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    // 3. Obtener Notas Detalladas de una Materia
    public function getNotasDetalladas($id_materia)
    {
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
        foreach ($notas_raw as $n)
            $mapa[$n['id_usuario']][$n['id_actividad_config']] = $n['calificacion_obtenida'];

        foreach ($alumnos as &$alum) {
            $alum['notas_actividad'] = isset($mapa[$alum['id']]) ? $mapa[$alum['id']] : [];
        }

        return $alumnos;
    }

    // 4. Guardar Notas Detalladas
    public function guardarNotasDetalladas($notas)
    {
        // $notas es array [id_usuario][id_actividad] = valor
        $sql = "INSERT INTO cursos.notas_participante (id_usuario, id_actividad_config, calificacion_obtenida)
                VALUES (:user, :act, :val)
                ON CONFLICT (id_usuario, id_actividad_config) 
                DO UPDATE SET calificacion_obtenida = EXCLUDED.calificacion_obtenida";
        $stmt = $this->conn->prepare($sql);

        foreach ($notas as $user_id => $actividades) {
            foreach ($actividades as $act_id => $valor) {
                if ($valor === '')
                    continue;
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
    public function getPromediosMaterias($id_curso)
    {
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
        foreach ($res as $r) {
            $val = $r['promedio_calculado'] !== null ? (float) $r['promedio_calculado'] : 0.0;
            $salida[$r['id_usuario']][$r['id_materia_bimestre']] = number_format($val, 2);
        }
        return $salida;
    }
}
?>