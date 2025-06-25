<?php
// Incluir el archivo model.php en config
include '../config/model.php';

// Incluir el archivo curso.php en models
include '../models/curso.php';

// Incluir el archivo autenticacion.php en controllers
include '../controllers/autenticacion.php';

// Crear una instancia de la clase DB
$db = new DB();

// Crear una instancia de la clase Curso
$curso = new Curso($db);

function validar_curso(
    $nombre_curso, $descripcion, $promotor, $tiempo_asignado, $inicio_mes, 
    $tipo_curso, $limite_inscripciones, $dias_clase, $horario_inicio, 
    $horario_fin, $nivel_curso, $costo, $conocimientos_previos, 
    $requerimientos_implemento, $desempeno_al_concluir, $modulos, 
    $horas_cronologicas, $fecha_finalizacion, $firma_digital
) {
    
    // --- Bloque 1: Validación de campos vacíos ---
    $campos_vacios = [];
    if (empty($nombre_curso)) $campos_vacios[] = 'nombre_curso';
    if (empty($descripcion)) $campos_vacios[] = 'descripcion';
    if (empty($promotor)) $campos_vacios[] = 'promotor';
    if (empty($tiempo_asignado)) $campos_vacios[] = 'tiempo_asignado';
    if (empty($inicio_mes)) $campos_vacios[] = 'inicio_mes';
    if (empty($tipo_curso)) $campos_vacios[] = 'tipo_curso';
    if (empty($limite_inscripciones)) $campos_vacios[] = 'limite_inscripciones';
    if ($dias_clase === '{}') $campos_vacios[] = 'dias_clase';
    if (empty($horario_inicio)) $campos_vacios[] = 'horario_inicio';
    if (empty($horario_fin)) $campos_vacios[] = 'horario_fin';
    if (empty($nivel_curso)) $campos_vacios[] = 'nivel_curso';
    if (empty($conocimientos_previos)) $campos_vacios[] = 'conocimientos_previos';
    if (empty($requerimientos_implemento)) $campos_vacios[] = 'requerimientos_implemento';
    if (empty($desempeno_al_concluir)) $campos_vacios[] = 'desempeno_al_concluir';
    if (empty($modulos)) $campos_vacios[] = 'modulos';

    if (!empty($campos_vacios)) {
        error_log("Validación fallida por campos vacíos: " . implode(', ', $campos_vacios));
        return false;
    }

    // --- Bloque 2: Validación de campos numéricos ---
    $invalid_fields = [];
    if (!is_numeric($tiempo_asignado)) $invalid_fields['tiempo_asignado'] = $tiempo_asignado;
    if (!is_numeric($limite_inscripciones)) $invalid_fields['limite_inscripciones'] = $limite_inscripciones;
    if (!is_numeric($costo) && $costo !== null) $invalid_fields['costo'] = $costo;
    if ($costo < 0) $invalid_fields['costo_negativo'] = $costo;
    if (!is_numeric($horas_cronologicas)) $invalid_fields['horas_cronologicas'] = $horas_cronologicas;

    if (!empty($invalid_fields)) {
        error_log("Validación fallida por campos no numéricos: " . json_encode($invalid_fields));
        return false;
    }

    // --- Bloque 3: Validación de formatos de fecha y hora ---
    $invalid_format = [];
    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $inicio_mes)) $invalid_format['inicio_mes'] = $inicio_mes;
    if (!preg_match("/^\d{2}:\d{2}(:\d{2})?$/", $horario_inicio)) $invalid_format['horario_inicio'] = $horario_inicio;
    if (!preg_match("/^\d{2}:\d{2}(:\d{2})?$/", $horario_fin)) $invalid_format['horario_fin'] = $horario_fin;
    // La fecha de finalización viene con formato de datetime-local
    if (!preg_match("/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(:\d{2})?$/", $fecha_finalizacion) && !preg_match("/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/", $fecha_finalizacion)) {
        $invalid_format['fecha_finalizacion'] = $fecha_finalizacion;
    }

    if (!empty($invalid_format)) {
        error_log("Validación fallida por formato inválido: " . json_encode($invalid_format));
        return false;
    }

    // ✅ CORRECCIÓN ESTRUCTURAL: Si pasamos todas las validaciones anteriores, devolvemos true al final.
    return true;
}

// Función para registrar mensajes en la consola
function registrar_log($mensaje) {
    echo '<script>console.log("' . addslashes($mensaje) . '");</script>';
}
// Obtener la acción del formulario
$action = $_POST['action'];

// Ejecutar la acción correspondiente
switch ($action) {
case 'crear':
    $nombre_curso = $_POST['nombre_curso'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $tiempo_asignado = $_POST['tiempo_asignado'] ?? '';
    $inicio_mes = $_POST['inicio_mes'] ?? '';
    $tipo_curso = $_POST['tipo_curso'] ?? '';
    $limite_inscripciones = $_POST['limite_inscripciones'] ?? '';
    $dias_clase_array = $_POST['dias_clase'] ?? [];
    $dias_clase_pg = '{' . implode(',', $dias_clase_array) . '}';
    $horario_inicio = $_POST['horario_inicio'] ?? '';
    $horario_fin = $_POST['horario_fin'] ?? '';
    $nivel_curso = $_POST['nivel_curso'] ?? '';
    $costo = $_POST['costo'] ?? 0;
    $conocimientos_previos = $_POST['conocimientos_previos'] ?? '';
    $requerimientos_implemento = $_POST['requerimientos_implementos'] ?? '';
    $desempeno_al_concluir = $_POST['desempeño_al_concluir'] ?? '';
    
    $configuracion_firmas = $_POST['config_firmas'] ?? [];

    $nombres_modulos = $_POST['nombre_modulo'] ?? [];
    $contenidos_modulos = $_POST['contenido'] ?? [];
    $actividades_modulos = $_POST['actividad'] ?? [];
    $instrumentos_modulos = $_POST['instrumento'] ?? [];
    
    $modulos_a_crear = [];
    foreach ($nombres_modulos as $i => $nombre) {
        $modulos_a_crear[] = [
            'nombre_modulo' => $nombre,
            'contenido' => $contenidos_modulos[$i] ?? '',
            'actividad' => $actividades_modulos[$i] ?? '',
            'instrumento' => $instrumentos_modulos[$i] ?? '',
            'numero' => $i + 1
        ];
    }

        $promotor_id = $_SESSION['user_id'];
        
        $resultado = $curso->crearCursoCompleto(
            $nombre_curso, $descripcion, $tiempo_asignado, $inicio_mes, $tipo_curso, 
            $limite_inscripciones, $dias_clase_pg, $horario_inicio, $horario_fin, 
            $nivel_curso, $costo, $conocimientos_previos, $requerimientos_implemento, 
            $desempeno_al_concluir, $promotor_id, $modulos_a_crear, $configuracion_firmas
        );

        if ($resultado) {
            // Simplemente devuelve un mensaje de texto plano
            echo "La propuesta de curso se ha creado correctamente.";
        } else {
            // Para errores, también un mensaje de texto plano
            http_response_code(400); // Opcional: envía un código de error HTTP
            echo "Ha ocurrido un error al crear la propuesta.";
        }
        exit();
        break;
        case 'editar':
            // Obtener los datos del formulario
            $id_curso = $_POST['id_curso'];
            $promotor = $_POST['promotor'];
            $configuracion_firmas = $_POST['config_firmas'] ?? [];
            $nombre_curso = $_POST['nombre_curso'];
            $descripcion = $_POST['descripcion'];
            $tiempo_asignado = $_POST['tiempo_asignado'];
            $inicio_mes = $_POST['inicio_mes'];
            $tipo_curso = $_POST['tipo_curso'];
            $limite_inscripciones = $_POST['limite_inscripciones'];
            $dias_clase = isset($_POST['dias_clase']) ? $_POST['dias_clase'] : [];
            if (!is_array($dias_clase)) {
                $dias_clase = str_replace(['{', '}'], '', $dias_clase);
                $dias_clase_array = explode(',', $dias_clase);
                $dias_clase_array = array_map('trim', $dias_clase_array);
                $dias_clase_pg = '{' . implode(',', $dias_clase_array) . '}';
            } else {
                $dias_clase_pg = '{' . implode(',', $dias_clase) . '}';
            }
            $horario_inicio = $_POST['horario_inicio'];
            $horario_fin = $_POST['horario_fin'];
            $nivel_curso = $_POST['nivel_curso'];
            $costo = $_POST['costo'];
            $conocimientos_previos = $_POST['conocimientos_previos'];
            $requerimientos_implemento = isset($_POST['requerimientos_implementos']) ? $_POST['requerimientos_implementos'] : null;
            $desempeño_al_concluir = isset($_POST['desempeño_al_concluir']) ? $_POST['desempeño_al_concluir'] : null;
            $horas_cronologicas = $_POST['horas_cronologicas'];
            $fecha_finalizacion = $_POST['fecha_finalizacion'];
            $firma_digital = isset($_POST['firma_digital']) ? true : false;
            $autorizacion = $_SESSION['user_id']; // Capturando el id del usuario actual para la autorización        
        
            // Definir $is_admin_or_authorizer
            $user_id = $_SESSION['user_id']; // Asegúrate de que esto se defina antes de su uso
            $is_admin_or_authorizer = esPerfil3($user_id) || esPerfil4($user_id);
        
            // Obtener los datos de los módulos
            if (!isset($_POST['contenido'])) {
                $_POST['contenido'] = []; // Inicializa como array vacío si no está definido
            }
        
            $nombre_modulo = $_POST['nombre_modulo'];
            $contenido = $_POST['contenido'];
            $actividad = $_POST['actividad_modulo'];
            $instrumento = $_POST['instrumento_modulo'];
            $numero_modulo = $_POST['numero_modulo'];
            $id_modulo = $_POST['id_modulo'];
        
            // Nuevos campos ocultos
            $numero_modulo_contenido = isset($_POST['numero_modulo_contenido']) ? $_POST['numero_modulo_contenido'] : [];
            $id_modulo_contenido = isset($_POST['id_modulo_contenido']) ? $_POST['id_modulo_contenido'] : [];
        
            // Agrupar contenidos por módulo
            $contenidos_modulo = [];
            foreach ($id_modulo as $id) {
                $contenidos_modulo[$id] = [];
            }
            foreach ($contenido as $index => $content) {
                if (isset($id_modulo_contenido[$index])) {
                    $modulo_id = $id_modulo_contenido[$index];
                    $contenidos_modulo[$modulo_id][] = $content;
                }
            }
        
            // Crear los datos de los módulos
            $modulos = [];
            foreach ($id_modulo as $i => $id) {
                if (empty($id)) {
                    // Crear el módulo ya que no tiene ID
                    $contenido_completo = isset($contenidos_modulo[$id]) ? implode('][', $contenidos_modulo[$id]) : '';
                    $contenido_completo = '[' . $contenido_completo . ']';
                    $curso->crearModulo($id_curso, $nombre_modulo[$i], $contenido_completo, $actividad[$i], $instrumento[$i], $numero_modulo[$i]);
                } else {
                    // Editar el módulo existente
                    $contenido_completo = isset($contenidos_modulo[$id]) ? implode('][', $contenidos_modulo[$id]) : '';
                    $contenido_completo = '[' . $contenido_completo . ']';
                    $modulos[] = [
                        'id_modulo' => $id,
                        'id_curso' => $id_curso,
                        'nombre_modulo' => $nombre_modulo[$i],
                        'contenido' => $contenido_completo,
                        'numero' => $numero_modulo[$i],
                        'actividad' => $actividad[$i],
                        'instrumento' => $instrumento[$i]
                    ];
                }
            }            
        
            // Guardar los datos para inspección
            file_put_contents('edit_debug_data.json', json_encode([
                'course_data' => $_POST,
                'modules_data' => $modulos,
                'contenidos_modulo' => $contenidos_modulo
            ], JSON_PRETTY_PRINT), LOCK_EX);

            // Inicializar variables si no están definidas
            $modulos = isset($modulos) ? $modulos : [];
            $desempeño_al_concluir = isset($_POST['desempeño_al_concluir']) ? $_POST['desempeño_al_concluir'] : null;

            // Validar los datos
            if (validar_curso($nombre_curso, $descripcion, $promotor, $tiempo_asignado, $inicio_mes, $tipo_curso, $limite_inscripciones, $dias_clase_pg, $horario_inicio, $horario_fin, $nivel_curso, $costo, $conocimientos_previos, $requerimientos_implemento, $desempeño_al_concluir, $modulos,$horas_cronologicas, $fecha_finalizacion, $firma_digital)) {
                // Editar el curso usando el método de la clase Curso
                if ($is_admin_or_authorizer) {
                    $curso->editar($id_curso, $nombre_curso, $descripcion, $tiempo_asignado, $inicio_mes, $tipo_curso, $limite_inscripciones, $promotor, $dias_clase_pg, $horario_inicio, $horario_fin, $nivel_curso, $costo, $conocimientos_previos, $modulos, $requerimientos_implemento, $desempeño_al_concluir, $horas_cronologicas, $fecha_finalizacion, $firma_digital, $_SESSION['user_id'], $configuracion_firmas);
                } else {
                    $curso->editar($id_curso, $nombre_curso, $descripcion, $tiempo_asignado, $inicio_mes, $tipo_curso, $limite_inscripciones, $promotor, $dias_clase_pg, $horario_inicio, $horario_fin, $nivel_curso, $costo, $conocimientos_previos, $modulos, $requerimientos_implemento, $desempeño_al_concluir, $horas_cronologicas, $fecha_finalizacion, $firma_digital, $configuracion_firmas);
                }
                // Devolver mensaje de éxito
                echo '<script>
                        alert("El curso se ha editado correctamente");
                        window.location.href = "../public/perfil.php";
                    </script>';
            } else {
                // Devolver mensaje de error con detalles
                $errorDetails = json_encode($_POST, JSON_HEX_APOS | JSON_HEX_QUOT);
                file_put_contents('edit_debug_data.json', json_encode([
                    'course_data' => $_POST,
                    'modules_data' => $modulos,
                    'contenidos_modulo' => $contenidos_modulo
                ], JSON_PRETTY_PRINT), LOCK_EX);
                echo '<script>
                        alert("Los datos del curso son inválidos: ' . addslashes($errorDetails) . '");
                        window.location.href = "../public/perfil.php";
                    </script>';
            }
            break;
        case 'eliminar':
            // Obtener el id del curso del formulario
            $id_curso = $_POST['id_curso'];
            // Eliminar el curso sin importar si hay usuarios inscritos o aprobados
            $curso->eliminar($id_curso);
            // Devolver mensaje de éxito
            echo 'El curso se ha eliminado correctamente';            
        break;
    case 'finalizar':
        // Obtener el id del curso del formulario
        $id_curso = $_POST['id_curso'];
        // Finalizar el curso usando el método de la clase Curso
        $curso->finalizar($id_curso);
        // Devolver mensaje de éxito
        echo 'El curso se ha finalizado correctamente';
        break;
    case 'iniciar':
        // Obtener el id del curso del formulario
        $id_curso = $_POST['id_curso'];
        // Iniciar el curso usando el método de la clase Curso
        $curso->iniciar($id_curso);
        // Devolver mensaje de éxito
        echo 'El curso se ha iniciado correctamente';
        break;
    default:
        // Devolver mensaje de error
        echo 'Acción inválida';
        break;
}
?>