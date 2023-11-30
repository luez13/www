<?php
// Iniciar la sesión
session_start();

// Conectar a la base de datos
$db = new PDO('pgsql:host=localhost;dbname=certificaciones_DB', 'postgres', '0000');

// Comprobar si se ha recibido una acción por el método POST
if (isset($_POST['action'])) {
    // Asignar la acción a una variable
    $action = $_POST['action'];
    // Ejecutar la acción correspondiente
    switch ($action) {
        case 'actualizar_datos':
            // Actualizar los datos personales del usuario
            try {
                // Obtener los datos del formulario
                $nombre = $_POST['nombre'];
                $apellido = $_POST['apellido'];
                $correo = $_POST['correo'];
                $cedula = $_POST['cedula'];
                // Obtener el id del usuario de la sesión
                $user_id = $_SESSION['user_id'];
                // Actualizar los datos del usuario en la base de datos
                $stmt = $db->prepare('UPDATE cursos.usuarios SET nombre = :nombre, apellido = :apellido, correo = :correo, cedula = :cedula WHERE id = :id');
                $stmt->execute(['nombre' => $nombre, 'apellido' => $apellido, 'correo' => $correo, 'cedula' => $cedula, 'id' => $user_id]);
                // Mostrar un mensaje de éxito al usuario
                echo '<p>Los datos se han actualizado correctamente</p>';
            } catch (PDOException $e) {
                // Mostrar un mensaje de error al usuario
                echo '<p>Ha ocurrido un error al actualizar los datos: ' . $e->getMessage() . '</p>';
            }
            break;
        case 'inscribirse':
            // Inscribir al usuario en un curso
            try {
                // Obtener el id del curso del formulario
                $id_curso = $_POST['id_curso'];
                // Obtener el id del usuario de la sesión
                $user_id = $_SESSION['user_id'];
                // Generar un valor único para el certificado
                $valor_unico = uniqid();
                // Insertar una fila en la tabla certificaciones con los datos del usuario y el curso
                $stmt = $db->prepare('INSERT INTO cursos.certificaciones (id_usuario, id_curso, valor_unico, completado) VALUES (:id_usuario, :id_curso, :valor_unico, false)');
                $stmt->execute(['id_usuario' => $user_id, 'id_curso' => $id_curso, 'valor_unico' => $valor_unico]);
                // Mostrar un mensaje de éxito al usuario
                echo '<p>Te has inscrito en el curso con éxito</p>';
            } catch (PDOException $e) {
                // Mostrar un mensaje de error al usuario
                echo '<p>Ha ocurrido un error al inscribirte en el curso: ' . $e->getMessage() . '</p>';
            }
            break;
        case 'cancelar_inscripcion':
            // Cancelar la inscripción del usuario en un curso
            try {
                // Obtener el id del curso del formulario
                $id_curso = $_POST['id_curso'];
                // Obtener el id del usuario de la sesión
                $user_id = $_SESSION['user_id'];
                // Eliminar la fila de la tabla certificaciones que corresponda al usuario y al curso
                $stmt = $db->prepare('DELETE FROM cursos.certificaciones WHERE id_usuario = :id_usuario AND id_curso = :id_curso');
                $stmt->execute(['id_usuario' => $user_id, 'id_curso' => $id_curso]);
                // Mostrar un mensaje de éxito al usuario
                echo '<p>Has cancelado tu inscripción en el curso con éxito</p>';
            } catch (PDOException $e) {
                // Mostrar un mensaje de error al usuario
                echo '<p>Ha ocurrido un error al cancelar tu inscripción en el curso: ' . $e->getMessage() . '</p>';
            }
        case 'finalizar':
                // Obtener el id del curso del formulario
                $id_curso = $_POST['id_curso'];
    
                // Actualizar el estado del curso a finalizado (false) en la base de datos
                try {
                    $stmt = $db->prepare('UPDATE cursos.cursos SET estado = false WHERE id_curso = :id_curso');
                    $stmt->execute(['id_curso' => $id_curso]);
    
                    // Mostrar un mensaje de éxito al usuario
                    echo '<p>El curso ha sido finalizado con éxito</p>';
                } catch (PDOException $e) {
                    // Mostrar un mensaje de error al usuario
                    echo '<p>Ha ocurrido un error al finalizar el curso: ' . $e->getMessage() . '</p>';
                }
    
                // Redirigir al usuario a la página de gestión de cursos
                header('Location: gestion_cursos.php');
                break;
    
            default:
                // Mostrar un mensaje de error al usuario
                echo '<p>Acción inválida</p>';
                break;
        }
    }    