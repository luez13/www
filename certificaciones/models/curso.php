<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Crear la clase Curso
class Curso {
    // Propiedad para guardar la conexión PDO real, no el objeto DB completo.
    private $pdo;

    // El constructor ahora extrae la conexión PDO y la guarda.
    public function __construct(DB $db_wrapper) {
        // Asumimos que tu clase DB tiene un método getConn() que devuelve la conexión PDO.
        $this->pdo = $db_wrapper->getConn(); 
    }

    public function crearCursoCompleto(
        $nombre, $descripcion, $tiempo_asignado, $inicio_mes, $tipo_curso, 
        $limite_inscripciones, $dias_clase, $horario_inicio, $horario_fin, 
        $nivel_curso, $costo, $conocimientos_previos, $requerimientos_implemento, 
        $desempeno_al_concluir, $promotor_id, $modulos = [], $configuracion_firmas = []
    ) {
        // Iniciamos la transacción. O todo se guarda, o nada se guarda.
        $this->pdo->beginTransaction();

        try {
            // PASO 1: Insertar el curso principal y obtener su nuevo ID
            $sql_curso = 'INSERT INTO cursos.cursos (nombre_curso, descripcion, tiempo_asignado, inicio_mes, tipo_curso, limite_inscripciones, dias_clase, horario_inicio, horario_fin, nivel_curso, costo, conocimientos_previos, requerimientos_implemento, desempeno_al_concluir, promotor) VALUES (:nombre_curso, :descripcion, :tiempo_asignado, :inicio_mes, :tipo_curso, :limite_inscripciones, :dias_clase, :horario_inicio, :horario_fin, :nivel_curso, :costo, :conocimientos_previos, :requerimientos_implemento, :desempeno_al_concluir, :promotor) RETURNING id_curso';
            
            $stmt_curso = $this->pdo->prepare($sql_curso);
            $stmt_curso->execute([
                'nombre_curso' => $nombre, 'descripcion' => $descripcion,
                'tiempo_asignado' => $tiempo_asignado, 'inicio_mes' => $inicio_mes,
                'tipo_curso' => $tipo_curso, 'limite_inscripciones' => $limite_inscripciones,
                'dias_clase' => $dias_clase, 'horario_inicio' => $horario_inicio,
                'horario_fin' => $horario_fin, 'nivel_curso' => $nivel_curso,
                'costo' => $costo, 'conocimientos_previos' => $conocimientos_previos,
                'requerimientos_implemento' => $requerimientos_implemento,
                'desempeno_al_concluir' => $desempeno_al_concluir, 'promotor' => $promotor_id
            ]);
            $curso_id = $stmt_curso->fetchColumn();

            if (!$curso_id) {
                // Si por alguna razón no se pudo crear el curso, cancelamos todo.
                throw new Exception("No se pudo obtener el ID del curso recién creado.");
            }

            // PASO 2: Insertar los módulos asociados al nuevo ID del curso
            $sql_modulo = 'INSERT INTO cursos.modulos (id_curso, nombre_modulo, contenido, actividad, instrumento, numero) VALUES (:id_curso, :nombre_modulo, :contenido, :actividad, :instrumento, :numero)';
            $stmt_modulo = $this->pdo->prepare($sql_modulo);

            foreach ($modulos as $modulo) {
                $stmt_modulo->execute([
                    ':id_curso' => $curso_id,
                    ':nombre_modulo' => $modulo['nombre_modulo'],
                    ':contenido' => $modulo['contenido'],
                    ':actividad' => $modulo['actividad'],
                    ':instrumento' => $modulo['instrumento'],
                    ':numero' => $modulo['numero']
                ]);
            }

            // PASO 3: Insertar la configuración de firmas por defecto
            $sql_insert_firma = "INSERT INTO cursos.cursos_config_firmas (id_curso, id_posicion, id_cargo_firmante, usar_promotor_curso) VALUES (:id_curso, :id_posicion, :id_cargo_firmante, :usar_promotor_curso)";
            $stmt_insert = $this->pdo->prepare($sql_insert_firma);

            foreach ($configuracion_firmas as $id_posicion => $config) {
                $id_cargo = !empty($config['id_cargo_firmante']) ? $config['id_cargo_firmante'] : null;
                $usar_promotor = isset($config['usar_promotor_curso']) ? true : false;

                if ($id_cargo !== null || $usar_promotor) {
                    $stmt_insert->execute([
                        ':id_curso' => $curso_id,
                        ':id_posicion' => $id_posicion,
                        ':id_cargo_firmante' => $id_cargo,
                        ':usar_promotor_curso' => $usar_promotor ? 'true' : 'false'
                    ]);
                }
            }

            // Si todos los pasos fueron exitosos, confirmamos y guardamos todo.
            $this->pdo->commit();
            return true; // Devolvemos true para indicar éxito

        } catch (Exception $e) {
            // Si algo falló en cualquier punto, revertimos todos los cambios.
            $this->pdo->rollBack();
            error_log("Error al crear curso completo: " . $e->getMessage());
            return false; // Devolvemos false para indicar el fallo
        }
    }

    public function editar($id_curso, $nombre_curso, $descripcion, $tiempo_asignado, $inicio_mes, $tipo_curso, $limite_inscripciones, $promotor, $dias_clase_pg, $horario_inicio, $horario_fin, $nivel_curso, $costo, $conocimientos_previos, $modulos, $requerimientos_implemento, $desempeno_al_concluir, $horas_cronologicas, $fecha_finalizacion, $firma_digital, $autorizacion = null, $configuracion_firmas = []) {

        // Restauramos la transacción para un guardado seguro
        $this->pdo->beginTransaction();

        try {
            // PASO 1: Actualizar la tabla principal 'cursos'
            $params = [
                ':nombre_curso' => $nombre_curso, ':descripcion' => $descripcion, ':promotor' => $promotor,
                ':tiempo_asignado' => $tiempo_asignado, ':inicio_mes' => $inicio_mes, ':tipo_curso' => $tipo_curso,
                ':limite_inscripciones' => $limite_inscripciones, ':dias_clase' => $dias_clase_pg, ':horario_inicio' => $horario_inicio,
                ':horario_fin' => $horario_fin, ':nivel_curso' => $nivel_curso, ':costo' => $costo,
                ':conocimientos_previos' => $conocimientos_previos, ':requerimientos_implemento' => $requerimientos_implemento,
                ':desempeno_al_concluir' => $desempeno_al_concluir, ':horas_cronologicas' => $horas_cronologicas,
                ':fecha_finalizacion' => $fecha_finalizacion, 
                ':firma_digital' => $firma_digital ? 'true' : 'false',
                ':id_curso' => $id_curso
            ];

            $sql = "UPDATE cursos.cursos SET 
                        nombre_curso = :nombre_curso, descripcion = :descripcion, promotor = :promotor, tiempo_asignado = :tiempo_asignado, 
                        inicio_mes = :inicio_mes, tipo_curso = :tipo_curso, limite_inscripciones = :limite_inscripciones, 
                        dias_clase = :dias_clase, horario_inicio = :horario_inicio, horario_fin = :horario_fin, 
                        nivel_curso = :nivel_curso, costo = :costo, conocimientos_previos = :conocimientos_previos, 
                        requerimientos_implemento = :requerimientos_implemento, desempeno_al_concluir = :desempeno_al_concluir, 
                        horas_cronologicas = :horas_cronologicas, fecha_finalizacion = :fecha_finalizacion, firma_digital = :firma_digital";
            
            if ($autorizacion !== null) {
                $sql .= ', autorizacion = :autorizacion';
                $params[':autorizacion'] = $autorizacion;
            }

            $sql .= ' WHERE id_curso = :id_curso';
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            // PASO 2: Actualizar los módulos
            foreach ($modulos as $modulo) {
                $stmt_modulo = $this->pdo->prepare('UPDATE cursos.modulos SET nombre_modulo = :nombre_modulo, contenido = :contenido, numero = :numero, actividad = :actividad, instrumento = :instrumento WHERE id_modulo = :id_modulo');
                $stmt_modulo->execute(['nombre_modulo' => $modulo['nombre_modulo'], 'contenido' => $modulo['contenido'], 'numero' => $modulo['numero'], 'actividad' => $modulo['actividad'], 'instrumento' => $modulo['instrumento'], 'id_modulo' => $modulo['id_modulo']]);
            }

            // PASO 3: Actualizar las firmas
            $stmt_delete = $this->pdo->prepare("DELETE FROM cursos.cursos_config_firmas WHERE id_curso = :id_curso");
            $stmt_delete->execute([':id_curso' => $id_curso]);

            $sql_insert_firma = "INSERT INTO cursos.cursos_config_firmas (id_curso, id_posicion, id_cargo_firmante, usar_promotor_curso) VALUES (:id_curso, :id_posicion, :id_cargo_firmante, :usar_promotor_curso)";
            $stmt_insert = $this->pdo->prepare($sql_insert_firma);

            foreach ($configuracion_firmas as $id_posicion => $config) {
                $id_cargo = !empty($config['id_cargo_firmante']) ? $config['id_cargo_firmante'] : null;
                $usar_promotor = isset($config['usar_promotor_curso']) ? true : false;

                if ($id_cargo !== null || $usar_promotor) {
                    $stmt_insert->execute([
                        ':id_curso' => $id_curso,
                        ':id_posicion' => $id_posicion,
                        ':id_cargo_firmante' => $id_cargo,
                        ':usar_promotor_curso' => $usar_promotor ? 'true' : 'false'
                    ]);
                }
            }
            
            // Si todo fue exitoso, guardamos permanentemente los cambios.
            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            // Si algo falló, revertimos todos los cambios para no dejar datos corruptos.
            $this->pdo->rollBack();
            error_log("Error al editar curso: " . $e->getMessage()); // Registrar el error en el log del servidor
            // Devolvemos el mensaje de error para que el controlador lo muestre
            echo "Ha ocurrido un error al editar el curso: " . $e->getMessage();
            return false;
        }
    }

    public function eliminar($id_curso) {
        try {
            // Primero eliminar referencias en certificaciones
            $stmt = $this->pdo->prepare('DELETE FROM cursos.certificaciones WHERE curso_id = :id');
            $stmt->execute(['id' => $id_curso]);
    
            // Luego eliminar el curso
            $stmt = $this->pdo->prepare('DELETE FROM cursos.cursos WHERE id_curso = :id');
            $stmt->execute(['id' => $id_curso]);
    
        } catch (PDOException $e) {
            echo '<p>Error al eliminar el curso: ' . $e->getMessage() . '</p>';
        }
    }    

    // Crear un método para finalizar un curso
    public function finalizar($id_curso) {
        // Cambiar el estado del curso a finalizado en la base de datos
        try {
            $stmt = $this->pdo->prepare('UPDATE cursos.cursos SET estado = :estado WHERE id_curso = :id');
            $stmt->execute(['estado' => 'FALSE', 'id' => $id_curso]);
        } catch (PDOException $e) {
            // Mostrar un mensaje de error al usuario
            echo '<p>Ha ocurrido un error al finalizar el curso: ' . $e->getMessage() . '</p>';
        }
    }
    

// Definir el método obtener_contenido
public function obtener_contenido($user_id) {
    // Preparar la consulta SQL para obtener los cursos creados por el usuario
    $stmt = $this->pdo->prepare('SELECT * FROM cursos.cursos WHERE promotor = :promotor');
    // Ejecutar la consulta con el id del usuario
    $stmt->execute(['promotor' => $user_id]);
    // Obtener el resultado como un array asociativo
    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Para cada curso, obtener los módulos asociados
    foreach ($cursos as &$curso) {
        $stmt_modulos = $this->pdo->prepare('SELECT * FROM cursos.modulos WHERE id_curso = :id_curso');
        $stmt_modulos->execute(['id_curso' => $curso['id_curso']]);
        $curso['modulos'] = $stmt_modulos->fetchAll(PDO::FETCH_ASSOC);
    }

    // Devolver el array con los datos de los cursos y sus módulos
    return $cursos;
}

// Definir el método obtener_curso
public function obtener_curso($id_curso) {
    // Preparar la consulta SQL para obtener los detalles del curso
    $stmt = $this->pdo->prepare('SELECT * FROM cursos.cursos WHERE id_curso = :id_curso');
    // Ejecutar la consulta con el id del curso
    $stmt->execute(['id_curso' => $id_curso]);
    // Obtener el resultado como un array asociativo
    $curso = $stmt->fetch(PDO::FETCH_ASSOC);

    // Preparar la consulta SQL para obtener los módulos del curso
    $stmt_modulos = $this->pdo->prepare('SELECT * FROM cursos.modulos WHERE id_curso = :id_curso');
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
    $stmt = $this->pdo->prepare($sql);
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
    $stmt = $this->pdo->prepare("SELECT nota FROM cursos.certificaciones WHERE curso_id = :id_curso AND id_usuario = :id_usuario");
    $stmt->execute([':id_curso' => $id_curso, ':id_usuario' => $id_usuario]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['nota'];
}

public function obtener_completado($id_curso, $id_usuario) {
    $stmt = $this->pdo->prepare("SELECT completado FROM cursos.certificaciones WHERE curso_id = :id_curso AND id_usuario = :id_usuario");
    $stmt->execute([':id_curso' => $id_curso, ':id_usuario' => $id_usuario]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['completado'];
}

public function actualizar_completado($id_curso, $id_usuario, $completado) {
    $sql = "UPDATE cursos.certificaciones SET completado = :completado WHERE curso_id = :id_curso AND id_usuario = :id_usuario";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindParam(':completado', $completado, PDO::PARAM_BOOL);
    $stmt->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    return $stmt->execute();
}

// Crear un método para iniciar un curso
public function iniciar($id_curso) {
    // Cambiar el estado del curso en la base de datos
    try {
        $stmt = $this->pdo->prepare('UPDATE cursos.cursos SET estado = :estado WHERE id_curso = :id');
        $stmt->execute(['estado' => true, 'id' => $id_curso]);
    } catch (PDOException $e) {
        // Mostrar un mensaje de error al usuario
        echo '<p>Ha ocurrido un error al iniciar el curso: ' . $e->getMessage() . '</p>';
    }
}

public function tiene_inscritos_o_aprobados($id_curso) {
    // Verificar si hay usuarios inscritos
    $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM cursos.certificaciones WHERE curso_id = :id_curso');
    $stmt->execute(['id_curso' => $id_curso]);
    $inscritos = $stmt->fetchColumn();

    // Verificar si hay usuarios que han aprobado el curso
    $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM cursos.certificaciones WHERE curso_id = :id_curso AND completado = true');
    $stmt->execute(['id_curso' => $id_curso]);
    $aprobados = $stmt->fetchColumn();

    return $inscritos > 0 || $aprobados > 0;
}

public function obtener_pagado($curso_id, $id_usuario) {
    $query = "SELECT pago FROM cursos.certificaciones WHERE curso_id = :id_curso AND id_usuario = :id_usuario";
    $stmt = $this->pdo->prepare($query);
    $stmt->bindParam(':id_curso', $curso_id, PDO::PARAM_INT);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? (bool)$result['pago'] : false;
}

public function actualizar_pagado($id_curso, $id_usuario, $pagado) {
    $sql = "UPDATE cursos.certificaciones SET pago = :pagado WHERE curso_id = :id_curso AND id_usuario = :id_usuario";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindParam(':pagado', $pagado, PDO::PARAM_BOOL);
    $stmt->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    return $stmt->execute();
}

// Método para obtener el curso por valor único en PostgreSQL
public function obtener_curso_por_valor_unico($valor_unico) {
    $stmt = $this->pdo->prepare('
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
    $stmt = $this->pdo->prepare('
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


public function obtenerDatosCompletosCertificado($valor_unico) {
    // 1. Obtiene los datos principales (sin cambios aquí)
    $datos_principales = $this->obtener_datos_certificacion($valor_unico);
    if (!$datos_principales) { return null; }

    $id_curso = $datos_principales['id_curso'];
    $id_promotor_curso = $datos_principales['promotor'];

    // Obtenemos las configuraciones de firma (sin cambios aquí)
    $sql_firmas = "
        SELECT ccf.id_cargo_firmante, ccf.usar_promotor_curso, pf.codigo_posicion, pf.pagina
        FROM cursos.cursos_config_firmas AS ccf
        JOIN cursos.posiciones_firma AS pf ON ccf.id_posicion = pf.id_posicion
        WHERE ccf.id_curso = :id_curso
    ";
    $stmt_firmas = $this->pdo->prepare($sql_firmas);
    $stmt_firmas->execute([':id_curso' => $id_curso]);
    $configuraciones = $stmt_firmas->fetchAll(PDO::FETCH_ASSOC);

    $firmantes_procesados = [];
    foreach ($configuraciones as $config) {
        $firmante_info = [
            'posicion_codigo' => $config['codigo_posicion'],
            'pagina'          => $config['pagina'],
            'nombre'          => '[Firmante no asignado]',
            'titulo'          => '', // <-- CAMBIO: Añadido para estandarizar
            'cargo'           => '',
            'firma_base64'    => null,
        ];
        
        $data_firmante = null;

        if ($config['usar_promotor_curso'] && $id_promotor_curso) {
            // --- CAMBIO CLAVE AQUÍ: AÑADIMOS 'cargo' A LA CONSULTA ---
            $stmt_user = $this->pdo->prepare("SELECT nombre, apellido, firma_digital, titulo, cargo FROM cursos.usuarios WHERE id = :id");
            $stmt_user->execute([':id' => $id_promotor_curso]);
            $data_firmante = $stmt_user->fetch(PDO::FETCH_ASSOC);
            
            // Si encontramos al promotor, establecemos su cargo para el certificado como "Facilitador"
            if ($data_firmante) {
                // Guardamos el cargo real del usuario, pero para el certificado usamos 'Facilitador'
                $data_firmante['nombre_cargo_certificado'] = 'Facilitador';
            }

        } elseif ($config['id_cargo_firmante']) {
            // Esta parte busca otros firmantes desde la tabla 'cargos', la dejamos igual
            $stmt_cargo = $this->pdo->prepare("SELECT nombre, apellido, nombre_cargo, titulo, firma_digital FROM cursos.cargos WHERE id_cargo = :id");
            $stmt_cargo->execute([':id' => $config['id_cargo_firmante']]);
            $data_firmante = $stmt_cargo->fetch(PDO::FETCH_ASSOC);
            if ($data_firmante) {
                $data_firmante['nombre_cargo_certificado'] = $data_firmante['nombre_cargo'];
            }
        }
        
        if ($data_firmante) {
            // --- CAMBIO: ESTRUCTURAMOS LOS DATOS DE FORMA MÁS LIMPIA ---
            $firmante_info['nombre'] = trim($data_firmante['nombre'] . ' ' . $data_firmante['apellido']);
            $firmante_info['titulo'] = $data_firmante['titulo'] ?? '';
            // Usamos el cargo definido para el certificado ('Facilitador' para el promotor)
            $firmante_info['cargo']  = $data_firmante['nombre_cargo_certificado']; 
            
            $ruta_desde_db = $data_firmante['firma_digital'];

            // Lógica de la firma digital (sin cambios aquí)
            if (!empty($datos_principales['firma_digital']) && $datos_principales['firma_digital'] === true && !empty($ruta_desde_db)) {
                $nombre_archivo = basename($ruta_desde_db);
                $ruta_relativa_final = 'public/assets/firmas/' . $nombre_archivo;
                $ruta_absoluta = dirname(__DIR__) . '/' . $ruta_relativa_final;

                if (file_exists($ruta_absoluta)) {
                    $imageData = file_get_contents($ruta_absoluta);
                    $imageType = mime_content_type($ruta_absoluta);
                    $firmante_info['firma_base64'] = 'data:' . $imageType . ';base64,' . base64_encode($imageData);
                } else {
                    $firmante_info['cargo'] .= ' (Firma no encontrada)';
                }
            }
        }
        $firmantes_procesados[] = $firmante_info;
    }

    $datos_principales['firmantes'] = $firmantes_procesados;
    
    // Obtenemos los módulos (sin cambios)
    $sql_modulos = "SELECT nombre_modulo, numero FROM cursos.modulos WHERE id_curso = :id_curso ORDER BY numero ASC";
    $stmt_modulos = $this->pdo->prepare($sql_modulos);
    $stmt_modulos->execute([':id_curso' => $id_curso]);
    $datos_principales['modulos'] = $stmt_modulos->fetchAll(PDO::FETCH_ASSOC);

    return $datos_principales;
}

public function obtener_datos_constancia_por_curso($id_curso) {
    $stmt = $this->pdo->prepare('
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
    $stmt = $this->pdo->prepare("SELECT tomo FROM cursos.certificaciones WHERE curso_id = :id_curso AND id_usuario = :id_usuario");
    $stmt->execute([':id_curso' => $id_curso, ':id_usuario' => $id_usuario]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['tomo'];
}

public function obtener_folio($id_curso, $id_usuario) {
    $stmt = $this->pdo->prepare("SELECT folio FROM cursos.certificaciones WHERE curso_id = :id_curso AND id_usuario = :id_usuario");
    $stmt->execute([':id_curso' => $id_curso, ':id_usuario' => $id_usuario]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['folio'];
}

public function actualizar_tomo_folio($id_curso, $id_usuario, $tomo, $folio) {
    $sql = "UPDATE cursos.certificaciones SET tomo = :tomo, folio = :folio WHERE curso_id = :id_curso AND id_usuario = :id_usuario";
    $stmt = $this->pdo->prepare($sql);

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