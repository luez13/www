<?php
// Crear la clase Curso
class Curso {
    // Crear una propiedad para guardar la instancia de la clase DB
    private $db;

    // Crear el constructor de la clase
    public function __construct($db) {
        // Asignar la instancia de la clase DB a la propiedad
        $this->db = $db;
    }

    // Crear un método para crear un curso
    public function crear($nombre, $descripcion, $duracion, $periodo, $modalidad, $tipo_evaluacion, $tipo_curso, $limite_inscripciones, $user_id) {
        // Insertar los datos en la base de datos
        try {
            $stmt = $this->db->prepare('INSERT INTO cursos.cursos (nombre, descripcion, duracion, periodo, modalidad, tipo_evaluacion, tipo_curso, limite_inscripciones, id_promotor, estado) VALUES (:nombre, :descripcion, :duracion, :periodo, :modalidad, :tipo_evaluacion, :tipo_curso, :limite_inscripciones, :id_promotor, :estado)');
            $stmt->execute(['nombre' => $nombre, 'descripcion' => $descripcion, 'duracion' => $duracion, 'periodo' => $periodo, 'modalidad' => $modalidad, 'tipo_evaluacion' => $tipo_evaluacion, 'tipo_curso' => $tipo_curso, 'limite_inscripciones' => $limite_inscripciones, 'id_promotor' => $user_id, 'estado' => 'Activo']);
        } catch (PDOException $e) {
            // Mostrar un mensaje de error al usuario
            echo '<p>Ha ocurrido un error al crear el curso: ' . $e->getMessage() . '</p>';
        }
    }

    // Crear un método para editar un curso
    public function editar($id_curso, $nombre, $descripcion, $duracion, $periodo, $modalidad, $tipo_evaluacion, $tipo_curso, $limite_inscripciones) {
        // Actualizar los datos en la base de datos
        try {
            $stmt = $this->db->prepare('UPDATE cursos.cursos SET nombre = :nombre, descripcion = :descripcion, duracion = :duracion, periodo = :periodo, modalidad = :modalidad, tipo_evaluacion = :tipo_evaluacion, tipo_curso = :tipo_curso, limite_inscripciones = :limite_inscripciones WHERE id = :id');
            $stmt->execute(['nombre' => $nombre, 'descripcion' => $descripcion, 'duracion' => $duracion, 'periodo' => $periodo, 'modalidad' => $modalidad, 'tipo_evaluacion' => $tipo_evaluacion, 'tipo_curso' => $tipo_curso, 'limite_inscripciones' => $limite_inscripciones, 'id' => $id_curso]);
        } catch (PDOException $e) {
            // Mostrar un mensaje de error al usuario
            echo '<p>Ha ocurrido un error al editar el curso: ' . $e->getMessage() . '</p>';
        }
    }

    // Crear un método para eliminar un curso
    public function eliminar($id_curso) {
        // Eliminar los datos de la base de datos
        try {
            $stmt = $this->db->prepare('DELETE FROM cursos.cursos WHERE id = :id');
            $stmt->execute(['id' => $id_curso]);
        } catch (PDOException $e) {
            // Mostrar un mensaje de error al usuario
            echo '<p>Ha ocurrido un error al eliminar el curso: ' . $e->getMessage() . '</p>';
        }
    }

    // Crear un método para finalizar un curso
    public function finalizar($id_curso) {
        // Cambiar el estado del curso a finalizado en la base de datos
        try {
            $stmt = $this->db->prepare('UPDATE cursos.cursos SET estado = :estado WHERE id = :id');
            $stmt->execute(['estado' => 'Finalizado', 'id' => $id_curso]);
        } catch (PDOException $e) {
            // Mostrar un mensaje de error al usuario
            echo '<p>Ha ocurrido un error al finalizar el curso: ' . $e->getMessage() . '</p>';
        }
    }

    // Definir el método obtener_contenido
    public function obtener_contenido($id_curso) {
        // Preparar la consulta SQL para obtener los datos del curso
        $stmt = $this->db->prepare('SELECT * FROM cursos.cursos WHERE id_curso = :id');
        // Ejecutar la consulta con el id del curso
        $stmt->execute(['id' => $id_curso]);
        // Obtener el resultado como un array asociativo
        $curso = $stmt->fetch(PDO::FETCH_ASSOC);
        // Devolver el array con los datos del curso
        return $curso;
    }
}
?>