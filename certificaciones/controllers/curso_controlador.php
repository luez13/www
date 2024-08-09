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
function validar_curso($nombre, $descripcion, $duracion, $periodo, $modalidad, $tipo_evaluacion, $tipo_curso, $limite_inscripciones) {
    // Verificar que los datos no estén vacíos
    if (empty($nombre) || empty($descripcion) || empty($duracion) || empty($periodo) || empty($modalidad) || !isset($tipo_evaluacion) || empty($tipo_curso) || empty($limite_inscripciones)) {
        return false;
    }
    // Verificar que la duración, el periodo y el límite de inscripciones sean numéricos
    if (!is_numeric($duracion) || !is_numeric($limite_inscripciones)) {
        return false;
    }
    // Verificar que la modalidad y el tipo de evaluación sean válidos
    if (!in_array($modalidad, ['Presencial', 'Virtual', 'Mixto']) || !is_bool($tipo_evaluacion)) {
        return false;
    }
    // Verificar que periodo sea una fecha válida
    if (!preg_match("/\d{4}-\d{2}-\d{2}/", $periodo)) {
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
        $duracion = $_POST['duracion'];
        $periodo = $_POST['periodo'];
        $modalidad = $_POST['modalidad'];
        $tipo_evaluacion = $_POST['tipo_evaluacion'] === 'true' ? true : false;
        $tipo_curso = $_POST['tipo_curso'];
        $limite_inscripciones = $_POST['limite_inscripciones'];
        // Validar los datos
        if (validar_curso($nombre_curso, $descripcion, $duracion, $periodo, $modalidad, $tipo_evaluacion, $tipo_curso, $limite_inscripciones)) {
            // Obtener el id del usuario de la sesión
            $promotor = $_SESSION['user_id'];
            // Crear el curso usando el método de la clase Curso
            $curso->crear($nombre_curso, $descripcion, $duracion, $periodo, $modalidad, $tipo_evaluacion, $tipo_curso, $limite_inscripciones, $promotor);
            // Devolver mensaje de éxito
            echo 'El curso se ha creado correctamente';
        } else {
            // Devolver mensaje de error
            echo 'Los datos del curso son inválidos';
        }
        break;
    case 'editar':
        // Obtener los datos del formulario
        $id_curso = $_POST['id_curso'];
        $nombre = $_POST['nombre_curso'];
        $descripcion = $_POST['descripcion'];
        $duracion = $_POST['duracion'];
        $periodo = $_POST['periodo'];
        $modalidad = $_POST['modalidad'];
        $tipo_evaluacion = $_POST['tipo_evaluacion'] === 'true' ? true : false;
        $tipo_curso = $_POST['tipo_curso'];
        $limite_inscripciones = $_POST['limite_inscripciones'];

        // Validar los datos
        if (validar_curso($nombre, $descripcion, $duracion, $periodo, $modalidad, $tipo_evaluacion, $tipo_curso, $limite_inscripciones)) {
            // Editar el curso usando el método de la clase Curso
            $curso->editar($id_curso, $nombre, $descripcion, $duracion, $periodo, $modalidad, $tipo_evaluacion, $tipo_curso, $limite_inscripciones);
            // Devolver mensaje de éxito
            echo 'El curso se ha editado correctamente';
        } else {
            // Devolver mensaje de error
            echo 'Los datos del curso son inválidos';
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