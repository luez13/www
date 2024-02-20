<?php
// Incluir el archivo model.php en config
include '../config/model.php';

// Incluir el archivo header.php en views
include '../views/header.php';

// Crear una instancia de la clase DB
$db = new DB();

// Crear una función para validar los datos de inscripción
function validar_inscripcion($id_usuario, $curso_id) {
    // Verificar que los datos no estén vacíos
    if (empty($id_usuario) || empty($curso_id)) {
        return false;
    }
    // Verificar que los datos sean numéricos
    if (!is_numeric($id_usuario) || !is_numeric($curso_id)) {
        return false;
    }
    // Si todo está bien, devolver true
    return true;
}

// Crear una función para validar los datos de cancelación de inscripción
function validar_cancelacion($id_usuario, $curso_id) {
    // Verificar que los datos no estén vacíos
    if (empty($id_usuario) || empty($curso_id)) {
        return false;
    }
    // Verificar que los datos sean numéricos
    if (!is_numeric($id_usuario) || !is_numeric($curso_id)) {
        return false;
    }
    // Si todo está bien, devolver true
    return true;
}

// Crear una función para validar los datos de finalización de curso
function validar_finalizacion($id_usuario, $curso_id) {
    // Verificar que los datos no estén vacíos
    if (empty($id_usuario) || empty($curso_id)) {
        return false;
    }
    // Verificar que los datos sean numéricos
    if (!is_numeric($id_usuario) || !is_numeric($curso_id)) {
        return false;
    }
    // Si todo está bien, devolver true
    return true;
}

// // Crear una función para redirigir al usuario a la página de perfil
function redirigir_perfil() {
    // Usar la función header para enviar el encabezado de redirección
    header('Location: ../public/perfil.php');
    // Terminar la ejecución del script
    exit();
}

// Obtener la acción del formulario
$action = $_POST['action'];

echo '<div class="main-content">';

// Ejecutar la acción correspondiente
switch ($action) {
    case 'inscribirse':
        // Obtener los datos del formulario
        $id_usuario = $_POST['id_usuario'];
        $curso_id = $_POST['curso_id'];
        // Generar un hash
        $valor_unico = hash('sha256', $id_usuario . $curso_id . time());
        // Validar los datos
        if (validar_inscripcion($id_usuario, $curso_id)) {
            // Verificar si el usuario ya está inscrito en el curso
            $stmt = $db->prepare('SELECT * FROM cursos.certificaciones WHERE id_usuario = :id_usuario AND curso_id = :curso_id');
            $stmt->execute(['id_usuario' => $id_usuario, 'curso_id' => $curso_id]);
            $inscripcion = $stmt->fetch();
            if ($inscripcion) {
                // Mostrar un mensaje al usuario
                echo '<p>Ya estás inscrito en este curso.</p>';
            } else {
                // Insertar los datos en la base de datos
                try {
                    $stmt = $db->prepare('INSERT INTO cursos.certificaciones (id_usuario, curso_id, valor_unico, fecha_inscripcion, completado) VALUES (:id_usuario, :curso_id, :valor_unico, NOW(), false)');
                    $stmt->execute(['id_usuario' => $id_usuario, 'curso_id' => $curso_id, 'valor_unico' => $valor_unico]);
                    // Mostrar un mensaje de éxito al usuario
                    echo '<p>Te has inscrito correctamente en el curso</p>';
                } catch (PDOException $e) {
                    // Mostrar un mensaje de error al usuario
                    echo '<p>Ha ocurrido un error al inscribirte en el curso: ' . $e->getMessage() . '</p>';
                }
            }
        } else {
            // Mostrar un mensaje de error al usuario
            echo '<p>Los datos de inscripción son inválidos</p>';
        }
        break;         
        case 'cancelar_inscripcion':
            // Obtener los datos del formulario
            $id_usuario = $_POST['id_usuario'];
            $curso_id = $_POST['curso_id'];
            // Validar los datos
            if (validar_cancelacion($id_usuario, $curso_id)) {
                // Verificar si el curso está finalizado
                $stmt = $db->prepare('SELECT completado FROM cursos.certificaciones WHERE id_usuario = :id_usuario AND curso_id = :curso_id');
                $stmt->execute(['id_usuario' => $id_usuario, 'curso_id' => $curso_id]);
                $finalizado = $stmt->fetchColumn();
                if ($finalizado) {
                    // Mostrar un mensaje al usuario
                    echo '<p>No puedes cancelar tu inscripción porque el curso ya está finalizado.</p>';
                } else {
                    // Eliminar los datos de la base de datos
                    try {
                        $stmt = $db->prepare('DELETE FROM cursos.certificaciones WHERE id_usuario = :id_usuario AND curso_id = :curso_id');
                        $stmt->execute(['id_usuario' => $id_usuario, 'curso_id' => $curso_id]);
                        // Mostrar un mensaje de éxito al usuario
                        echo '<p>Has cancelado tu suscripción al curso</p>';
                    } catch (PDOException $e) {
                        // Mostrar un mensaje de error al usuario
                        echo '<p>Ha ocurrido un error al cancelar tu suscripción al curso: ' . $e->getMessage() . '</p>';
                    }
                }
            } else {
                // Mostrar un mensaje de error al usuario
                echo '<p>Los datos de cancelación son inválidos</p>';
            }
            break;        
    case 'finalizar_curso':
        // Obtener los datos del formulario
        $id_usuario = $_POST['id_usuario'];
        $curso_id = $_POST['curso_id'];
        // Validar los datos
        if (validar_finalizacion($id_usuario, $curso_id)) {
            // Actualizar los datos en la base de datos
            try {
                $stmt = $db->prepare('UPDATE cursos.certificaciones SET finalizado = true, fecha_finalizacion = NOW() WHERE id_usuario = :id_usuario AND curso_id = :curso_id');
                $stmt->execute(['id_usuario' => $id_usuario, 'curso_id' => $curso_id]);
                // Mostrar un mensaje de éxito al usuario
                echo '<p>Has finalizado el curso correctamente</p>';
            } catch (PDOException $e) {
                // Mostrar un mensaje de error al usuario
                echo '<p>Ha ocurrido un error al finalizar el curso: ' . $e->getMessage() . '</p>';
            }
        } else {
            // Mostrar un mensaje de error al usuario
            echo '<p>Los datos de finalización son inválidos</p>';
        }
        break;
    default:
        // Mostrar un mensaje de error al usuario
        echo '<p>Acción inválida</p>';
        break;
}

echo '</div>';
// Incluir el archivo footer.php en views
include '../views/footer.php';
?>