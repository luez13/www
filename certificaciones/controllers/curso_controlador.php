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

function validar_curso($nombre, $descripcion, $tiempo_asignado, $inicio_mes, $tipo_curso, $limite_inscripciones, $dias_clase, $horario_inicio, $horario_fin, $nivel_curso, $costo, $conocimientos_previos, $requerimientos_implementos, $desempeño_al_concluir, $contenidos) {
    $campos_vacios = [];
    if (empty($nombre)) $campos_vacios[] = 'nombre';
    if (empty($descripcion)) $campos_vacios[] = 'descripcion';
    if (empty($tiempo_asignado)) $campos_vacios[] = 'tiempo_asignado';
    if (empty($inicio_mes)) $campos_vacios[] = 'inicio_mes';
    if (empty($tipo_curso)) $campos_vacios[] = 'tipo_curso';
    if (empty($limite_inscripciones)) $campos_vacios[] = 'limite_inscripciones';
    if (empty($dias_clase)) $campos_vacios[] = 'dias_clase';
    if (empty($horario_inicio)) $campos_vacios[] = 'horario_inicio';
    if (empty($horario_fin)) $campos_vacios[] = 'horario_fin';
    if (empty($nivel_curso)) $campos_vacios[] = 'nivel_curso';
    if (empty($conocimientos_previos)) $campos_vacios[] = 'conocimientos_previos';
    if (empty($requerimientos_implementos)) $campos_vacios[] = 'requerimientos_implementos';
    if (empty($desempeño_al_concluir)) $campos_vacios[] = 'desempeño_al_concluir';
    if (empty($contenidos)) $campos_vacios[] = 'contenidos';

    if (!empty($campos_vacios)) {
        file_put_contents('validation_errors.json', json_encode([
            'missing_fields' => $campos_vacios,
            'course_data' => func_get_args()
        ], JSON_PRETTY_PRINT));
        return false;
    }

    $invalid_fields = [];

    if (!is_numeric($tiempo_asignado)) $invalid_fields['tiempo_asignado'] = $tiempo_asignado;
    if (!is_numeric($limite_inscripciones)) $invalid_fields['limite_inscripciones'] = $limite_inscripciones;
    if (!is_numeric($costo) && $costo !== null) $invalid_fields['costo'] = $costo;
    if ($costo < 0) $invalid_fields['costo_negative'] = $costo;

    if (!empty($invalid_fields)) {
        file_put_contents('validation_errors.json', json_encode([
            'invalid_numeric' => $invalid_fields,
            'course_data' => func_get_args()
        ], JSON_PRETTY_PRINT));
        return false;
    }

    $invalid_format = [];

    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $inicio_mes)) $invalid_format['inicio_mes'] = $inicio_mes;
    if (!preg_match("/^\d{2}:\d{2}(:\d{2})?$/", $horario_inicio)) $invalid_format['horario_inicio'] = $horario_inicio;
    if (!preg_match("/^\d{2}:\d{2}(:\d{2})?$/", $horario_fin)) $invalid_format['horario_fin'] = $horario_fin;

    if (!empty($invalid_format)) {
        file_put_contents('validation_errors.json', json_encode([
            'invalid_format' => $invalid_format,
            'course_data' => func_get_args()
        ], JSON_PRETTY_PRINT));
        return false;
    }

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
        // Obtener los datos del formulario
        $nombre_curso = $_POST['nombre_curso'];
        $descripcion = $_POST['descripcion'];
        $tiempo_asignado = $_POST['tiempo_asignado'];
        $inicio_mes = $_POST['inicio_mes'];
        $tipo_curso = $_POST['tipo_curso'];
        $limite_inscripciones = $_POST['limite_inscripciones'];
        $dias_clase = isset($_POST['dias_clase']) ? '{' . implode(',', $_POST['dias_clase']) . '}' : '{}';
        $horario_inicio = $_POST['horario_inicio'];
        $horario_fin = $_POST['horario_fin'];
        $nivel_curso = $_POST['nivel_curso'];
        $costo = $_POST['costo'];
        $conocimientos_previos = $_POST['conocimientos_previos'];
        $requerimientos_implementos = $_POST['requerimientos_implementos'];
        $desempeño_al_concluir = $_POST['desempeño_al_concluir'];
        $nombre_modulo = $_POST['nombre_modulo'];
        $contenido = $_POST['contenido'];
        $actividad = $_POST['actividad'];
        $instrumento = $_POST['instrumento'];

        // Validar los datos
        if (validar_curso($nombre_curso, $descripcion, $tiempo_asignado, $inicio_mes, $tipo_curso, $limite_inscripciones, $dias_clase, $horario_inicio, $horario_fin, $nivel_curso, $costo, $conocimientos_previos, $requerimientos_implementos, $desempeño_al_concluir, $contenido)) {
            // Obtener el id del usuario de la sesión
            $promotor = $_SESSION['user_id'];
            // Crear el curso usando el método de la clase Curso
            $curso_id = $curso->crear($nombre_curso, $descripcion, $tiempo_asignado, $inicio_mes, $tipo_curso, $limite_inscripciones, $dias_clase, $horario_inicio, $horario_fin, $nivel_curso, $costo, $conocimientos_previos, $requerimientos_implementos, $desempeño_al_concluir, $promotor);

            // Insertar los módulos
            foreach ($nombre_modulo as $i => $nombre) {
                $contenidos_comb = $contenido[$i]; // Usar el contenido combinado con la referencia del índice
                $curso->crearModulo($curso_id, $nombre, $contenidos_comb, $actividad[$i], $instrumento[$i], $i + 1);
            }                                                                                    

            // Devolver mensaje de éxito
            echo 'El curso y los módulos se han creado correctamente';
        } else {
            // Devolver mensaje de error
            echo 'Los datos del curso son inválidos';
        }
        break;       
        case 'editar':
            // Obtener los datos del formulario
            $id_curso = $_POST['id_curso'];
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
            if (validar_curso($nombre_curso, $descripcion, $tiempo_asignado, $inicio_mes, $tipo_curso, $limite_inscripciones, $dias_clase_pg, $horario_inicio, $horario_fin, $nivel_curso, $costo, $conocimientos_previos, $requerimientos_implemento, $desempeño_al_concluir, $modulos)) {
                // Editar el curso usando el método de la clase Curso
                if ($is_admin_or_authorizer) {
                    $curso->editar($id_curso, $nombre_curso, $descripcion, $tiempo_asignado, $inicio_mes, $tipo_curso, $limite_inscripciones, $dias_clase_pg, $horario_inicio, $horario_fin, $nivel_curso, $costo, $conocimientos_previos, $modulos, $requerimientos_implemento, $desempeño_al_concluir, $_SESSION['user_id']);
                } else {
                    $curso->editar($id_curso, $nombre_curso, $descripcion, $tiempo_asignado, $inicio_mes, $tipo_curso, $limite_inscripciones, $dias_clase_pg, $horario_inicio, $horario_fin, $nivel_curso, $costo, $conocimientos_previos, $modulos, $requerimientos_implemento, $desempeño_al_concluir);
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