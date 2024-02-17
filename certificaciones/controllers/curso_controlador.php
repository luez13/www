<?php
// Incluir el archivo model.php en config
include '../config/model.php';

// Incluir el archivo header.php en views
include '../views/header.php';

// Incluir el archivo curso.php en models
include '../models/curso.php';

$user_id = $_SESSION['user_id'];

// Crear una instancia de la clase DB
$db = new DB();

// Crear una instancia de la clase Curso
$curso = new Curso($db);

// Crear una función para validar los datos de creación o edición de curso
function validar_curso($nombre, $descripcion, $duracion, $periodo, $modalidad, $tipo_evaluacion, $tipo_curso, $limite_inscripciones) {
    // Verificar que los datos no estén vacíos
    if (empty($nombre) || empty($descripcion) || empty($duracion) || empty($periodo) || empty($modalidad) || empty($tipo_evaluacion) || empty($tipo_curso) || empty($limite_inscripciones)) {
        return false;
    }
    // Verificar que la duración, el periodo y el límite de inscripciones sean numéricos
    if (!is_numeric($duracion) || !is_numeric($limite_inscripciones)) {
        return false;
    }
    // Verificar que la modalidad y el tipo de evaluación sean válidos
    if (!in_array($modalidad, ['Presencial', 'Virtual', 'Mixto']) || !in_array($tipo_evaluacion, ['Sin nota', 'Con nota'])) {
        return false;
    }
    // Verificar que periodo sea una fecha válida
    if (!preg_match("/\d{4}-\d{2}-\d{2}/", $periodo)) {
        return false;
    }
    // Si todo está bien, devolver true
    return true;
}

// // Crear una función para redirigir al usuario a la página de perfil
// function redirigir_perfil() {
//     // Usar la función header para enviar el encabezado de redirección
//     header('Location: ../public/perfil.php');
//     // Terminar la ejecución del script
//     exit();
// }

// Crear una función para redirigir al usuario a la página de gestión de cursos
function redirigir_gestion() {
    // Usar la función header para enviar el encabezado de redirección
    header('Location: ../public/gestion_cursos.php');
    // Terminar la ejecución del script
    exit();
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
            // Mostrar un mensaje de éxito al usuario
            echo '<p>El curso se ha creado correctamente</p>';
        } else {
            // Mostrar un mensaje de error al usuario
            echo '<p>Los datos del curso son inválidos</p>';
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
        $tipo_evaluacion = $_POST['tipo_evaluacion'];
        $tipo_curso = $_POST['tipo_curso'];
        $limite_inscripciones = $_POST['limite_inscripciones'];

        // Validar los datos
        if (validar_curso($nombre, $descripcion, $duracion, $periodo, $modalidad, $tipo_evaluacion, $tipo_curso, $limite_inscripciones)) {
            // Editar el curso usando el método de la clase Curso
            $curso->editar($id_curso, $nombre, $descripcion, $duracion, $periodo, $modalidad, $tipo_evaluacion, $tipo_curso, $limite_inscripciones);
            // Mostrar un mensaje de éxito al usuario
            echo '<p>El curso se ha editado correctamente</p>';
        } else {
            // Mostrar un mensaje de error al usuario
            echo '<p>Los datos del curso son inválidos</p>';
        }
        break;
    case 'eliminar':
        // Obtener el id del curso del formulario
        $id_curso = $_POST['id_curso'];
        // Eliminar el curso usando el método de la clase Curso
        $curso->eliminar($id_curso);
        // Mostrar un mensaje de éxito al usuario
        echo '<p>El curso se ha eliminado correctamente</p>';
        break;
    case 'finalizar':
        // Obtener el id del curso del formulario
        $id_curso = $_POST['id_curso'];
        // Finalizar el curso usando el método de la clase Curso
        $curso->finalizar($id_curso);
        // Mostrar un mensaje de éxito al usuario
        echo '<p>El curso se ha finalizado correctamente</p>';
        break;
    default:
        // Mostrar un mensaje de error al usuario
        echo '<p>Acción inválida</p>';
        break;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'editar_curso') {
    $id_curso = $_POST['id_curso'];
    $promotor = $_POST['promotor'];
    $modalidad = $_POST['modalidad'];
    $nombre_curso = $_POST['nombre_curso'];
    $descripcion = $_POST['descripcion'];
    $duracion = $_POST['duracion'];
    $periodo = $_POST['periodo'];
    $tipo_evaluacion = $_POST['tipo_evaluacion'];
    $tipo_curso = $_POST['tipo_curso'];
    $limite_inscripciones = $_POST['limite_inscripciones'];
    $estado = $_POST['estado'];

    // Verificar si el curso ya está autorizado
    $stmt = $db->prepare("SELECT autorizacion FROM cursos.cursos WHERE id_curso = :id_curso");
    $stmt->execute([':id_curso' => $id_curso]);
    $autorizacion_actual = $stmt->fetch(PDO::FETCH_ASSOC)['autorizacion'];

    $autorizacion = $_POST['autorizacion'] != 'no' ? $user_id : $autorizacion_actual;

    // Actualizar los datos del curso
    $db = new DB();
    $stmt = $db->prepare("UPDATE cursos.cursos SET promotor = :promotor, modalidad = :modalidad, nombre_curso = :nombre_curso, descripcion = :descripcion, duracion = :duracion, periodo = :periodo, tipo_evaluacion = :tipo_evaluacion, tipo_curso = :tipo_curso, autorizacion = :autorizacion, limite_inscripciones = :limite_inscripciones, estado = :estado WHERE id_curso = :id_curso");
    $stmt->bindParam(':promotor', $promotor);
    $stmt->bindParam(':modalidad', $modalidad);
    $stmt->bindParam(':nombre_curso', $nombre_curso);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':duracion', $duracion);
    $stmt->bindParam(':periodo', $periodo);
    $stmt->bindParam(':tipo_evaluacion', $tipo_evaluacion);
    $stmt->bindParam(':tipo_curso', $tipo_curso);
    $stmt->bindParam(':autorizacion', $autorizacion);
    $stmt->bindParam(':limite_inscripciones', $limite_inscripciones);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':id_curso', $id_curso);
    $stmt->execute();
    

    header('Location: ../public/editar_cursos.php'); // Redirige de nuevo a la página de cursos
}

// Incluir el archivo footer.php en views
include '../views/footer.php';
?>