<?php
// Incluir el archivo model.php en config
include '../config/model.php';

// Incluir el archivo curso.php en models
include '../models/curso.php';

// Crear una instancia de la clase DB
$db = new DB();

// Crear una instancia de la clase Curso
$curso = new Curso($db);

// Crear una función para validar los datos de creación o edición de curso
function validar_curso($nombre, $descripcion, $tiempo_asignado, $inicio_mes, $tipo_curso, $limite_inscripciones, $dias_clase, $horario_inicio, $horario_fin, $nivel_curso, $costo, $conocimientos_previos) {
    // Verificar que los datos no estén vacíos
    if (empty($nombre) || empty($descripcion) || empty($tiempo_asignado) || empty($inicio_mes) || empty($tipo_curso) || empty($limite_inscripciones) || empty($dias_clase) || empty($horario_inicio) || empty($horario_fin) || empty($nivel_curso) || empty($costo) || empty($conocimientos_previos)) {
        return false;
    }
    // Verificar que los campos numéricos sean válidos
    if (!is_numeric($tiempo_asignado) || !is_numeric($limite_inscripciones) || !is_numeric($costo)) {
        return false;
    }
    // Verificar que las fechas y horas sean válidas
    if (!preg_match("/\d{4}-\d{2}-\d{2}/", $inicio_mes) || !preg_match("/\d{2}:\d{2}/", $horario_inicio) || !preg_match("/\d{2}:\d{2}/", $horario_fin)) {
        return false;
    }
    // Si todo está bien, devolver true
    return true;
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
        if (validar_curso($nombre_curso, $descripcion, $tiempo_asignado, $inicio_mes, $tipo_curso, $limite_inscripciones, $dias_clase, $horario_inicio, $horario_fin, $nivel_curso, $costo, $conocimientos_previos, $requerimientos_implementos, $desempeño_al_concluir)) {
            // Obtener el id del usuario de la sesión
            $promotor = $_SESSION['user_id'];
            // Crear el curso usando el método de la clase Curso
            $curso_id = $curso->crear($nombre_curso, $descripcion, $tiempo_asignado, $inicio_mes, $tipo_curso, $limite_inscripciones, $dias_clase, $horario_inicio, $horario_fin, $nivel_curso, $costo, $conocimientos_previos, $requerimientos_implementos, $desempeño_al_concluir, $promotor);
            
            // Insertar los módulos
            for ($i = 0; $i < count($nombre_modulo); $i++) {
                $curso->crearModulo($curso_id, $nombre_modulo[$i], $contenido[$i], $actividad[$i], $instrumento[$i], $i + 1);
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
            $dias_clase = $_POST['dias_clase'];
            $horario_inicio = $_POST['horario_inicio'];
            $horario_fin = $_POST['horario_fin'];
            $nivel_curso = $_POST['nivel_curso'];
            $costo = $_POST['costo'];
            $conocimientos_previos = $_POST['conocimientos_previos'];
        
            // Obtener los datos de los módulos
            $modulos = [];
            for ($i = 0; $i < count($_POST['id_modulo']); $i++) {
                $modulos[] = [
                    'id_modulo' => $_POST['id_modulo'][$i],
                    'nombre_modulo' => $_POST['nombre_modulo'][$i],
                    'contenido' => $_POST['contenido_modulo'][$i],
                    'numero' => $_POST['numero_modulo'][$i],
                    'actividad' => $_POST['actividad_modulo'][$i],
                    'instrumento' => $_POST['instrumento_modulo'][$i]
                ];
            }
        
            // Validar los datos
            if (validar_curso($nombre_curso, $descripcion, $tiempo_asignado, $inicio_mes, $tipo_curso, $limite_inscripciones, $dias_clase, $horario_inicio, $horario_fin, $nivel_curso, $costo, $conocimientos_previos)) {
                // Editar el curso usando el método de la clase Curso
                $curso->editar($id_curso, $nombre_curso, $descripcion, $tiempo_asignado, $inicio_mes, $tipo_curso, $limite_inscripciones, $dias_clase, $horario_inicio, $horario_fin, $nivel_curso, $costo, $conocimientos_previos, $modulos);
                // Devolver mensaje de éxito
                echo '<script>
                        alert("El curso se ha editado correctamente");
                        window.location.href = "../public/perfil.php";
                      </script>';
            } else {
                // Devolver mensaje de error
                echo '<script>
                        alert("Los datos del curso son inválidos");
                        window.location.href = "../public/perfil.php";
                      </script>';
            }
            break;
    case 'eliminar':
        // Obtener el id del curso del formulario
        $id_curso = $_POST['id_curso'];
        // Verificar si hay usuarios inscritos o que hayan aprobado el curso
        if (!$curso->tiene_inscritos_o_aprobados($id_curso)) {
            // Eliminar el curso
            $curso->eliminar($id_curso);
            // Devolver mensaje de éxito
            echo 'El curso se ha eliminado correctamente';
        } else {
            // Devolver mensaje de error
            echo 'No se puede eliminar el curso porque hay usuarios inscritos o que han aprobado el curso';
        }
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