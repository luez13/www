<?php
// Incluir el archivo model.php en config
include '../config/model.php';

// Incluir el archivo header.php en views
include '../views/header.php';

// Incluir el archivo curso.php en models
include '../models/curso.php';

// Crear una instancia de la clase DB
$db = new DB();

// Crear una instancia de la clase Curso
$curso = new Curso($db);

// Obtener el id del curso del parámetro de la URL
$id_curso = $_GET['id'];

// Validar el id del curso
if (is_numeric($id_curso) && $id_curso > 0) {
    // Obtener el id del usuario de la sesión
    $user_id = $_SESSION['user_id'];

    // Obtener los usuarios inscritos en el curso usando el método de la clase Curso
    $usuarios = $curso->obtener_estudiantes($id_curso);

    // Obtener el curso de la base de datos usando el método de la clase Curso
    $curso_info = $curso->obtener_curso($id_curso); // Aquí se asigna un valor a la variable $curso

    // Verificar que la variable $curso no sea nula
    if (isset($curso_info)) { 
        // Aquí puedes acceder a las propiedades del curso, como $curso->nombre_curso, $curso->descripcion, etc.
        echo '<h3>' . $curso_info['nombre_curso'] . '</h3>';
        echo '<p>' . $curso_info['descripcion'] . '</p>';        

        // Mostrar los usuarios inscritos
        echo '<h3>Usuarios inscritos</h3>';
        echo '<table>';
        echo '<tr>';
        echo '<th>Nombre</th>';
        echo '<th>Apellido</th>';
        echo '<th>Cédula</th>';
        echo '<th>Correo</th>';
        echo '</tr>';
        foreach ($usuarios as $usuario) {
            $nota = $curso->obtener_nota($id_curso, $usuario['id']);
            $completado = $curso->obtener_completado($id_curso, $usuario['id']);
            echo '<tr>';
            echo '<td>' . $usuario['nombre'] . '</td>';
            echo '<td>' . $usuario['apellido'] . '</td>';
            echo '<td>' . $usuario['cedula'] . '</td>';
            echo '<td>' . $usuario['correo'] . '</td>';
            echo '<td>' . $nota . '</td>'; // Mostrar la nota del usuario
            echo '<td><input type="checkbox" class="completado" data-id-curso="' . $id_curso . '" data-id-usuario="' . $usuario['id'] . '"' . ($completado ? ' checked' : '') . '>';
            echo '<span> Si está marcado, el curso está completado. Si no está marcado, el curso no está completado.</span></td>'; // Mostrar el estado de finalización del curso
            echo '</tr>';
        }
        echo '</table>';

        // Mostrar los cupos disponibles
        $cupos_disponibles = $curso_info['limite_inscripciones'] - count($usuarios);
        echo '<p>Cupos disponibles: ' . $cupos_disponibles . '</p>';

        // Si el curso tiene evaluación, mostrar un formulario para asignar la nota a cada usuario
        if ($curso_info['tipo_evaluacion']) {
            echo '<h3>Asignar nota</h3>';
            foreach ($usuarios as $usuario) {
                echo '<form action="../controllers/asignar_nota.php" method="post">';
                echo '<input type="hidden" name="action" value="asignar_nota">';
                echo '<input type="hidden" name="id_usuario" value="' . $usuario['id'] . '">';
                echo '<input type="hidden" name="id_curso" value="' . $id_curso . '">';
                echo '<label for="nota">Nota para ' . $usuario['nombre'] . ':</label>';
                echo '<input type="number" id="nota" name="nota" min="0" max="100">';
                echo '<input type="submit" value="Asignar nota">';
                echo '</form>';
            }
        }
    } else {
        // Si el curso no existe o no fue creado por el usuario, mostrar un mensaje de error
        echo '<p>El curso solicitado no existe o no lo has creado tú.</p>';
    }
} else {
    // Si el id del curso es inválido, mostrar un mensaje de error
    echo '<p>El id del curso es inválido.</p>';
}

// Incluir el archivo footer.php en views
include '../views/footer.php';
?>
<script>
$(document).ready(function(){
    $('.completado').change(function() {
        var id_curso = $(this).data('id-curso');
        var id_usuario = $(this).data('id-usuario');
        var completado = $(this).is(':checked') ? 1 : 0;

        $.ajax({
            url: '../controllers/actualizar_estado.php',
            type: 'POST',
            data: {
                id_curso: id_curso,
                id_usuario: id_usuario,
                completado: completado
            },
            success: function(response) {
                console.log(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    });
});
</script>