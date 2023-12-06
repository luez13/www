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
        // Verificar si el usuario existe en la base de datos
        $stmt = $this->db->prepare('SELECT * FROM cursos.usuarios WHERE id = :user_id');
        $stmt->execute(['user_id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            echo '<p>El usuario no existe</p>';
            return;
        }

        // Insertar los datos en la base de datos
        try {
            $stmt = $this->db->prepare('INSERT INTO cursos.cursos (nombre_curso, descripcion, duracion, periodo, modalidad, tipo_evaluacion, tipo_curso, limite_inscripciones, promotor) VALUES (:nombre_curso, :descripcion, :duracion, :periodo, :modalidad, :tipo_evaluacion, :tipo_curso, :limite_inscripciones, :promotor)');
            $stmt->execute(['nombre_curso' => $nombre, 'descripcion' => $descripcion, 'duracion' => $duracion, 'periodo' => $periodo, 'modalidad' => $modalidad, 'tipo_evaluacion' => $tipo_evaluacion, 'tipo_curso' => $tipo_curso, 'limite_inscripciones' => $limite_inscripciones, 'promotor' => $user_id]);
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
    public function obtener_contenido($user_id) {
        // Preparar la consulta SQL para obtener los cursos creados por el usuario
        $stmt = $this->db->prepare('SELECT * FROM cursos.cursos WHERE promotor = :promotor');
        // Ejecutar la consulta con el id del usuario
        $stmt->execute(['promotor' => $user_id]);
        // Obtener el resultado como un array asociativo
        $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Devolver el array con los datos de los cursos
        return $cursos;
}
}
?>