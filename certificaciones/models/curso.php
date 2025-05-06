<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
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
public function crear($nombre, $descripcion, $tiempo_asignado, $inicio_mes, $tipo_curso, $limite_inscripciones, $dias_clase, $horario_inicio, $horario_fin, $nivel_curso, $costo, $conocimientos_previos, $requerimientos_implemento, $desempeno_al_concluir, $user_id) {
    // Verificar si el usuario existe en la base de datos
    $stmt = $this->db->prepare('SELECT * FROM cursos.usuarios WHERE id = :user_id');
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        echo '<p>El usuario no existe</p>';
        return;
    }

    // Insertar los datos en la base de datos y obtener el ID del curso recién creado
    try {
       $usuario_s=$_SESSION['user_id'];
        $stmt = $this->db->prepare('INSERT INTO cursos.cursos (nombre_curso, descripcion, tiempo_asignado, inicio_mes, tipo_curso, limite_inscripciones, dias_clase, horario_inicio, horario_fin, nivel_curso, costo, conocimientos_previos, requerimientos_implemento, desempeno_al_concluir, promotor) VALUES (:nombre_curso, :descripcion, :tiempo_asignado, :inicio_mes, :tipo_curso, :limite_inscripciones, :dias_clase, :horario_inicio, :horario_fin, :nivel_curso, :costo, :conocimientos_previos, :requerimientos_implemento, :desempeno_al_concluir, :promotor) RETURNING id_curso');
        $stmt->execute([
            'nombre_curso' => $nombre,
            'descripcion' => $descripcion,
            'tiempo_asignado' => $tiempo_asignado,
            'inicio_mes' => $inicio_mes,
            'tipo_curso' => $tipo_curso,
            'limite_inscripciones' => $limite_inscripciones,
            'dias_clase' => $dias_clase,
            'horario_inicio' => $horario_inicio,
            'horario_fin' => $horario_fin,
            'nivel_curso' => $nivel_curso,
            'costo' => $costo,
            'conocimientos_previos' => $conocimientos_previos,
            'requerimientos_implemento' => $requerimientos_implemento,
            'desempeno_al_concluir' => $desempeno_al_concluir,
            'promotor' => $usuario_s
        ]);
        $curso_id = $stmt->fetchColumn(); // Obtener el ID del curso recién creado
        return $curso_id;
    } catch (PDOException $e) {
        // Mostrar un mensaje de error al usuario
         "<p>Ha ocurrido un error al crear el curso:  $nombre, $descripcion, $tiempo_asignado, $inicio_mes, $tipo_curso, $limite_inscripciones, $dias_clase, $horario_inicio, $horario_fin, $nivel_curso, $costo, $conocimientos_previos, $requerimientos_implemento, $desempeno_al_concluir, $user_id" . $e->getMessage() . "</p>";
        return null;
    }
}
    public function crearModulo($curso_id, $nombre_modulo, $contenido, $actividad, $instrumento, $numero) {
        try {
            // Guardar la información del módulo para depuración
            file_put_contents('debug_modulos_clase.json', json_encode(['curso_id' => $curso_id, 'nombre_modulo' => $nombre_modulo, 'contenido' => $contenido, 'actividad' => $actividad, 'instrumento' => $instrumento, 'numero' => $numero], JSON_PRETTY_PRINT), FILE_APPEND);

            $stmt = $this->db->prepare('INSERT INTO cursos.modulos (id_curso, nombre_modulo, contenido, actividad, instrumento, numero) VALUES (:id_curso, :nombre_modulo, :contenido, :actividad, :instrumento, :numero)');
            $stmt->execute([
                'id_curso' => $curso_id,
                'nombre_modulo' => $nombre_modulo,
                'contenido' => $contenido,
                'actividad' => $actividad,
                'instrumento' => $instrumento,
                'numero' => $numero
            ]);
        } catch (PDOException $e) {
            // Mostrar un mensaje de error al usuario
            echo '<p>Ha ocurrido un error al crear el módulo: ' . $e->getMessage() . '</p>';
        }
    }

    public function editar($id_curso, $nombre_curso, $descripcion, $tiempo_asignado, $inicio_mes, $tipo_curso, $limite_inscripciones, $dias_clase_pg, $horario_inicio, $horario_fin, $nivel_curso, $costo, $conocimientos_previos, $modulos, $requerimientos_implemento, $desempeno_al_concluir, $horas_cronologicas, $fecha_finalizacion, $firma_digital, $autorizacion = null) {
        try {
            // Convertir firma_digital a una representación adecuada para SQL
            $firma_digital = $firma_digital ? 'true' : 'false';
    
            $sql = 'UPDATE cursos.cursos SET nombre_curso = :nombre_curso, descripcion = :descripcion, tiempo_asignado = :tiempo_asignado, inicio_mes = :inicio_mes, tipo_curso = :tipo_curso, limite_inscripciones = :limite_inscripciones, dias_clase = :dias_clase, horario_inicio = :horario_inicio, horario_fin = :horario_fin, nivel_curso = :nivel_curso, costo = :costo, conocimientos_previos = :conocimientos_previos, requerimientos_implemento = :requerimientos_implemento, desempeno_al_concluir = :desempeno_al_concluir, horas_cronologicas = :horas_cronologicas, fecha_finalizacion = :fecha_finalizacion, firma_digital = :firma_digital';
            $params = [
                'nombre_curso' => $nombre_curso,
                'descripcion' => $descripcion,
                'tiempo_asignado' => $tiempo_asignado,
                'inicio_mes' => $inicio_mes,
                'tipo_curso' => $tipo_curso,
                'limite_inscripciones' => $limite_inscripciones,
                'dias_clase' => $dias_clase_pg,
                'horario_inicio' => $horario_inicio,
                'horario_fin' => $horario_fin,
                'nivel_curso' => $nivel_curso,
                'costo' => $costo,
                'conocimientos_previos' => $conocimientos_previos,
                'requerimientos_implemento' => $requerimientos_implemento,
                'desempeno_al_concluir' => $desempeno_al_concluir,
                'horas_cronologicas' => $horas_cronologicas,
                'fecha_finalizacion' => $fecha_finalizacion,
                'firma_digital' => $firma_digital,
                'id_curso' => $id_curso
            ];
    
            if ($autorizacion !== null) {
                $sql .= ', autorizacion = :autorizacion';
                $params['autorizacion'] = $autorizacion;
            }
    
            $sql .= ' WHERE id_curso = :id_curso';
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
    
            foreach ($modulos as $modulo) {
                $contenido = $modulo['contenido'];
                if (strpos($contenido, '[[') !== false) {
                    $contenido = trim($contenido, '[]');
                    $contenido = '[' . str_replace('][', '][', $contenido) . ']';
                } else {
                    $contenido = str_replace(['[', ']'], '', $contenido);
                }
                $stmt = $this->db->prepare('UPDATE cursos.modulos SET nombre_modulo = :nombre_modulo, contenido = :contenido, numero = :numero, actividad = :actividad, instrumento = :instrumento WHERE id_modulo = :id_modulo');
                $stmt->execute([
                    'nombre_modulo' => $modulo['nombre_modulo'],
                    'contenido' => $contenido,
                    'numero' => $modulo['numero'],
                    'actividad' => $modulo['actividad'],
                    'instrumento' => $modulo['instrumento'],
                    'id_modulo' => $modulo['id_modulo']
                ]);
            }
        } catch (PDOException $e) {
            echo var_dump([
                'nombre_curso' => $nombre_curso,
                'descripcion' => $descripcion,
                'tiempo_asignado' => $tiempo_asignado,
                'inicio_mes' => $inicio_mes,
                'tipo_curso' => $tipo_curso,
                'limite_inscripciones' => $limite_inscripciones,
                'dias_clase' => $dias_clase_pg,
                'horario_inicio' => $horario_inicio,
                'horario_fin' => $horario_fin,
                'nivel_curso' => $nivel_curso,
                'costo' => $costo,
                'conocimientos_previos' => $conocimientos_previos,
                'requerimientos_implemento' => $requerimientos_implemento,
                'desempeno_al_concluir' => $desempeno_al_concluir,
                'horas_cronologicas' => $horas_cronologicas,
                'fecha_finalizacion' => $fecha_finalizacion,
                'firma_digital' => $firma_digital,
                'id_curso' => $id_curso,
                'autorizacion' => $autorizacion
            ]) . '<p>Ha ocurrido un error al editar el curso: ' . $e->getMessage() . '</p>';
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

    // Para cada curso, obtener los módulos asociados
    foreach ($cursos as &$curso) {
        $stmt_modulos = $this->db->prepare('SELECT * FROM cursos.modulos WHERE id_curso = :id_curso');
        $stmt_modulos->execute(['id_curso' => $curso['id_curso']]);
        $curso['modulos'] = $stmt_modulos->fetchAll(PDO::FETCH_ASSOC);
    }

    // Devolver el array con los datos de los cursos y sus módulos
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

    // Preparar la consulta SQL para obtener los módulos del curso
    $stmt_modulos = $this->db->prepare('SELECT * FROM cursos.modulos WHERE id_curso = :id_curso');
    // Ejecutar la consulta con el id del curso
    $stmt_modulos->execute(['id_curso' => $id_curso]);
    // Obtener los resultados como un array asociativo
    $modulos = $stmt_modulos->fetchAll(PDO::FETCH_ASSOC);

    // Añadir los módulos al array del curso
    $curso['modulos'] = $modulos;

    // Devolver el array con los datos del curso y los módulos
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

// Crear un método para iniciar un curso
public function iniciar($id_curso) {
    // Cambiar el estado del curso en la base de datos
    try {
        $stmt = $this->db->prepare('UPDATE cursos.cursos SET estado = :estado WHERE id_curso = :id');
        $stmt->execute(['estado' => true, 'id' => $id_curso]);
    } catch (PDOException $e) {
        // Mostrar un mensaje de error al usuario
        echo '<p>Ha ocurrido un error al iniciar el curso: ' . $e->getMessage() . '</p>';
    }
}

public function tiene_inscritos_o_aprobados($id_curso) {
    // Verificar si hay usuarios inscritos
    $stmt = $this->db->prepare('SELECT COUNT(*) FROM cursos.certificaciones WHERE curso_id = :id_curso');
    $stmt->execute(['id_curso' => $id_curso]);
    $inscritos = $stmt->fetchColumn();

    // Verificar si hay usuarios que han aprobado el curso
    $stmt = $this->db->prepare('SELECT COUNT(*) FROM cursos.certificaciones WHERE curso_id = :id_curso AND completado = true');
    $stmt->execute(['id_curso' => $id_curso]);
    $aprobados = $stmt->fetchColumn();

    return $inscritos > 0 || $aprobados > 0;
}

public function obtener_pagado($curso_id, $id_usuario) {
    $query = "SELECT pago FROM cursos.certificaciones WHERE curso_id = :id_curso AND id_usuario = :id_usuario";
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':id_curso', $curso_id, PDO::PARAM_INT);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? (bool)$result['pago'] : false;
}

public function actualizar_pagado($id_curso, $id_usuario, $pagado) {
    $sql = "UPDATE cursos.certificaciones SET pago = :pagado WHERE curso_id = :id_curso AND id_usuario = :id_usuario";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':pagado', $pagado, PDO::PARAM_BOOL);
    $stmt->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    return $stmt->execute();
}

// Método para obtener el curso por valor único en PostgreSQL
public function obtener_curso_por_valor_unico($valor_unico) {
    $stmt = $this->db->prepare('
        SELECT c.nombre_curso, c.descripcion, c.tipo_curso,
               c.tiempo_asignado, c.inicio_mes,
               c.estado, c.dias_clase,
               c.horario_inicio, c.horario_fin,
               c.nivel_curso, c.costo,
               c.conocimientos_previos,
               c.requerimientos_implemento,
               c.desempeno_al_concluir
        FROM cursos.cursos AS c
        JOIN cursos.certificaciones AS cert ON cert.curso_id = c.id_curso
        WHERE cert.valor_unico = :valor_unico
    ');
    $stmt->execute(['valor_unico' => $valor_unico]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function obtener_datos_certificacion($valor_unico) {
    $stmt = $this->db->prepare('
        SELECT c.id_curso, c.nombre_curso, c.descripcion, c.tipo_curso,
               c.tiempo_asignado, c.inicio_mes,
               c.estado, c.dias_clase,
               c.horario_inicio, c.horario_fin,
               c.nivel_curso, c.costo,
               c.conocimientos_previos, c.requerimientos_implemento,
               c.desempeno_al_concluir, c.horas_cronologicas, c.firma_digital,
               c.promotor, c.fecha_finalizacion,
               u.nombre AS nombre_estudiante, u.apellido AS apellido_estudiante, u.cedula,
               cert.fecha_inscripcion, cert.tomo, cert.folio,
               cert.nota, cert.completado
        FROM cursos.cursos AS c
        JOIN cursos.certificaciones AS cert ON cert.curso_id = c.id_curso
        JOIN cursos.usuarios AS u ON cert.id_usuario = u.id
        WHERE cert.valor_unico = :valor_unico
    ');
    $stmt->execute(['valor_unico' => $valor_unico]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function obtener_datos_constancia_por_curso($id_curso) {
    $stmt = $this->db->prepare('
        SELECT c.id_curso, c.nombre_curso, c.descripcion, c.tipo_curso,
               c.tiempo_asignado, c.inicio_mes, c.estado, c.dias_clase,
               c.horario_inicio, c.horario_fin, c.nivel_curso, c.costo,
               c.conocimientos_previos, c.requerimientos_implemento,
               c.desempeno_al_concluir, c.horas_cronologicas, c.fecha_finalizacion, c.firma_digital,
               c.promotor,
               u.nombre AS nombre_promotor, u.apellido AS apellido_promotor, u.correo, u.cedula,
               m.id_modulo, m.nombre_modulo
        FROM cursos.cursos AS c
        JOIN cursos.usuarios AS u ON c.promotor = u.id
        LEFT JOIN cursos.modulos AS m ON c.id_curso = m.id_curso
        WHERE c.id_curso = :id_curso
    ');
    $stmt->execute(['id_curso' => $id_curso]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function obtener_tomo($id_curso, $id_usuario) {
    $stmt = $this->db->prepare("SELECT tomo FROM cursos.certificaciones WHERE curso_id = :id_curso AND id_usuario = :id_usuario");
    $stmt->execute([':id_curso' => $id_curso, ':id_usuario' => $id_usuario]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['tomo'];
}

public function obtener_folio($id_curso, $id_usuario) {
    $stmt = $this->db->prepare("SELECT folio FROM cursos.certificaciones WHERE curso_id = :id_curso AND id_usuario = :id_usuario");
    $stmt->execute([':id_curso' => $id_curso, ':id_usuario' => $id_usuario]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['folio'];
}

public function actualizar_tomo_folio($id_curso, $id_usuario, $tomo, $folio) {
    $sql = "UPDATE cursos.certificaciones SET tomo = :tomo, folio = :folio WHERE curso_id = :id_curso AND id_usuario = :id_usuario";
    $stmt = $this->db->prepare($sql);

    // Verificar y manejar valores vacíos
    $tomo = !empty($tomo) ? (int)$tomo : null;
    $folio = !empty($folio) ? (int)$folio : null;

    // Manejar valores null
    if ($tomo === null) {
        $stmt->bindValue(':tomo', null, PDO::PARAM_NULL);
    } else {
        $stmt->bindValue(':tomo', $tomo, PDO::PARAM_INT);
    }

    if ($folio === null) {
        $stmt->bindValue(':folio', null, PDO::PARAM_NULL);
    } else {
        $stmt->bindValue(':folio', $folio, PDO::PARAM_INT);
    }

    $stmt->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);

    return $stmt->execute();
}

public function write_log($message) {
    $log_file = '../controllers/log.txt';
    $current_time = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$current_time] $message\n", FILE_APPEND);
}
}
?>