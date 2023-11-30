<?php
// Iniciar la sesión
session_start();

// Conectar a la base de datos
$db = new PDO('pgsql:host=localhost;dbname=certificaciones_DB', 'postgres', '0000');

// Obtener el id del usuario de la sesión
$user_id = $_SESSION['user_id'];

// Consultar la base de datos para obtener los cursos creados por el usuario
$stmt = $db->prepare("SELECT * FROM cursos.cursos WHERE promotor = :promotor");
$stmt->execute(['promotor' => $user_id]);
$cursos_creados = $stmt->fetchAll();

// Mostrar los cursos creados por el usuario
foreach ($cursos_creados as $curso) {
    echo '<h4>' . $curso['nombre_curso'] . '</h4>';
    echo '<p>' . $curso['descripcion'] . '</p>';

    // Consultar la base de datos para obtener los usuarios inscritos en el curso
    $stmt = $db->prepare("SELECT * FROM cursos.usuarios WHERE id IN (SELECT id_usuario FROM cursos.certificaciones WHERE id_curso = :id_curso)");
    $stmt->execute(['id_curso' => $curso['id_curso']]);
    $usuarios_inscritos = $stmt->fetchAll();

    // Mostrar los usuarios inscritos en el curso
    foreach ($usuarios_inscritos as $usuario) {
        echo '<p>Usuario inscrito: ' . $usuario['nombre'] . '</p>';

        // Si el tipo de evaluación es 'evaluada', mostrar un formulario para asignar una nota al usuario
        if ($curso['tipo_evaluacion']) {
            echo '<form method="POST" action="asignar_nota.php">';
            echo '<input type="hidden" name="id_usuario" value="' . $usuario['id'] . '">';
            echo '<input type="hidden" name="id_curso" value="' . $curso['id_curso'] . '">';
            echo 'Nota: <input type="number" name="nota">';
            echo '<button type="submit">Asignar nota</button>';
            echo '</form>';
        }
    }

    // Mostrar un formulario para finalizar el curso
    echo '<form method="POST" action="curso_acciones.php">';
    echo '<input type="hidden" name="action" value="finalizar">';
    echo '<input type="hidden" name="id_curso" value="' . $curso['id_curso'] . '">';
    echo '<button type="submit">Finalizar Curso</button>';
    echo '</form>';
}
?>