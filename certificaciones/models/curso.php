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
            $stmt = $this->db->prepare('UPDATE cursos.cursos SET nombre_curso = :nombre, descripcion = :descripcion, duracion = :duracion, periodo = :periodo, modalidad = :modalidad, tipo_evaluacion = :tipo_evaluacion, tipo_curso = :tipo_curso, limite_inscripciones = :limite_inscripciones WHERE id_curso = :id');
            $tipo_evaluacion = $tipo_evaluacion === 'Con nota' ? true : false;
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
            $stmt = $this->db->prepare('DELETE FROM cursos.cursos WHERE id_curso = :id');
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
            $stmt = $this->db->prepare('UPDATE cursos.cursos SET estado = :estado WHERE id_curso = :id');
            $stmt->execute(['estado' => 'FALSE', 'id' => $id_curso]);
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

    // Definir el método obtener_curso
    public function obtener_curso($id_curso) {
        // Preparar la consulta SQL para obtener los detalles del curso
        $stmt = $this->db->prepare('SELECT * FROM cursos.cursos WHERE id_curso = :id_curso');
        // Ejecutar la consulta con el id del curso
        $stmt->execute(['id_curso' => $id_curso]);
        // Obtener el resultado como un array asociativo
        $curso = $stmt->fetch(PDO::FETCH_ASSOC);
        // Devolver el array con los datos del curso
        return $curso;
    }

// Crear una función para obtener los usuarios inscritos en un curso
public function obtener_estudiantes($id_curso) {
    // Crear un array vacío para almacenar los usuarios
    $usuarios = array();
    // Preparar la consulta SQL para obtener los usuarios inscritos en el curso
    $sql = "SELECT u.id, u.nombre, u.apellido, u.cedula, u.correo FROM cursos.usuarios u
    INNER JOIN cursos.certificaciones c ON u.id = c.id_usuario
    WHERE c.curso_id = :curso_id";
    // Ejecutar la consulta usando el método prepare y execute de la clase DB
    $stmt = $this->db->prepare($sql);
    // Ejecutar la consulta con un array asociativo que contenga los valores para cada marcador de posición
    $stmt->execute(array(':curso_id' => $id_curso));
    // Recorrer los resultados usando el método fetch de la clase DB
    while ($row = $stmt->fetch()) {
        // Crear un array asociativo con los datos del usuario
        $usuario = array(
            'id' => $row['id'],
            'nombre' => $row['nombre'],
            'apellido' => $row['apellido'],
            'cedula' => $row['cedula'],
            'correo' => $row['correo']
        );
        // Agregar el usuario al array de usuarios
        array_push($usuarios, $usuario);
    }
    // Devolver el array de usuarios
    return $usuarios;
}
public function obtener_nota($id_curso, $id_usuario) {
    $stmt = $this->db->prepare("SELECT nota FROM cursos.certificaciones WHERE curso_id = :id_curso AND id_usuario = :id_usuario");
    $stmt->execute([':id_curso' => $id_curso, ':id_usuario' => $id_usuario]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['nota'];
}

public function obtener_completado($id_curso, $id_usuario) {
    $stmt = $this->db->prepare("SELECT completado FROM cursos.certificaciones WHERE curso_id = :id_curso AND id_usuario = :id_usuario");
    $stmt->execute([':id_curso' => $id_curso, ':id_usuario' => $id_usuario]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['completado'];
}

public function actualizar_completado($id_curso, $id_usuario, $completado) {
    $sql = "UPDATE cursos.certificaciones SET completado = :completado WHERE curso_id = :id_curso AND id_usuario = :id_usuario";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':completado', $completado, PDO::PARAM_BOOL);
    $stmt->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    return $stmt->execute();
}
}
?>