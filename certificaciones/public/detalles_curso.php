<?php
// Incluir el archivo model.php en config
include '../config/model.php';

// Definir la clase fondo-negro para el fondo negro y el texto blanco 
echo '<style> .fondo-negro { 
background-color: #ffffff !important; /* Fondo negro */ color: #000000; /* Texto blanco para contraste */ 
} </style>';

// Incluir el archivo header.php en views
include '../views/header.php';

// Aplicar la clase fondo-negro al body 
echo '<script>document.body.classList.add("fondo-negro");</script>';

// Incluir el archivo curso.php en models
include '../models/curso.php';

// Crear una instancia de la clase DB
$db = new DB();

// Crear una instancia de la clase Curso
$curso = new Curso($db);

// Obtener el id del curso del parámetro de la URL
$id_curso = $_GET['id'];

// Obtener el id del usuario de la sesión
$user_id = $_SESSION['user_id'];

// Obtener el rol del usuario de la sesión
$user_role = $_SESSION['id_rol'];

// Obtener el id del promotor del curso
$curso_info = $curso->obtener_curso($id_curso);
$promotor_id = $curso_info['promotor'];

// Verificar si el usuario tiene permiso para ver la página
if (!in_array($user_role, [3, 4]) && $user_id != $promotor_id) {
    echo '<p>No tienes permiso para ver esta página.</p>';
    include '../views/footer.php';
    exit;
}

echo '<div class="main-content">';
// Validar el id del curso
if (is_numeric($id_curso) && $id_curso > 0) {
    // Obtener los usuarios inscritos en el curso usando el método de la clase Curso
    $usuarios = $curso->obtener_estudiantes($id_curso);

    // Verificar que la variable $curso_info no sea nula
    if (isset($curso_info)) { 
        // Aquí puedes acceder a las propiedades del curso, como $curso_info['nombre_curso'], $curso_info['descripcion'], etc.
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
        echo '<th>Nota</th>'; // Añadir columna para la nota
        echo '<th>Completado</th>'; // Añadir columna para el estado de finalización
        if (in_array($user_role, [3, 4])) {
            echo '<th>Pagado</th>'; // Añadir columna para el estado de pago
        }
        echo '<th><button id="actualizar-tomos-folios" class="btn btn-primary">Actualizar Tomos y Folios</button></th>';
        echo '</tr>';
        foreach ($usuarios as $usuario) {
            $nota = $curso->obtener_nota($id_curso, $usuario['id']);
            $completado = $curso->obtener_completado($id_curso, $usuario['id']);
            $pagado = $curso->obtener_pagado($id_curso, $usuario['id']);
            $tomo = $curso->obtener_tomo($id_curso, $usuario['id']);
            $folio = $curso->obtener_folio($id_curso, $usuario['id']);
            echo '<tr>';
            echo '<td>' . $usuario['nombre'] . '</td>';
            echo '<td>' . $usuario['apellido'] . '</td>';
            echo '<td>' . $usuario['cedula'] . '</td>';
            echo '<td>' . $usuario['correo'] . '</td>';
            echo '<td class="nota" data-id-usuario="' . $usuario['id'] . '">' . $nota . '</td>';
            echo '<td><input type="checkbox" class="completado" data-id-curso="' . $id_curso . '" data-id-usuario="' . $usuario['id'] . '"' . ($completado ? ' checked' : '') . '>';
            echo '<span> Si está marcado, el curso está completado. Si no está marcado, el curso no está completado.</span></td>';
            if (in_array($user_role, [3, 4])) {
                echo '<td><input type="checkbox" class="pagado" data-id-curso="' . $id_curso . '" data-id-usuario="' . $usuario['id'] . '"' . ($pagado ? ' checked' : '') . '>';
                echo '<span> Si está marcado, el curso está pagado. Si no está marcado, el curso no está pagado.</span></td>';
                echo '<td>';
                echo '<input type="number" class="tomo" data-id-curso="' . $id_curso . '" data-id-usuario="' . $usuario['id'] . '" value="' . $tomo . '" min="0" placeholder="Tomo">';
                echo '<input type="number" class="folio" data-id-curso="' . $id_curso . '" data-id-usuario="' . $usuario['id'] . '" value="' . $folio . '" min="0" placeholder="Folio">';
                echo '</td>';
            }
            echo '</tr>';
        }
        echo '</table>';

        // Mostrar los cupos disponibles
        $cupos_disponibles = $curso_info['limite_inscripciones'] - count($usuarios);
        echo '<p>Cupos disponibles: ' . $cupos_disponibles . '</p>';

        // Mostrar un formulario para asignar la nota a cada usuario
        echo '<h3>Asignar nota</h3>';
        foreach ($usuarios as $usuario) {
            echo '<form class="asignar-nota" data-id-usuario="' . $usuario['id'] . '" action="../controllers/asignar_nota.php" method="post">';
            echo '<input type="hidden" name="action" value="asignar_nota">';
            echo '<input type="hidden" name="id_usuario" value="' . $usuario['id'] . '">';
            echo '<input type="hidden" name="id_curso" value="' . $id_curso . '">';
            echo '<label for="nota">Nota para ' . $usuario['nombre'] . ':</label>';
            echo '<input type="number" id="nota" name="nota" min="0" max="100">';
            echo '<input type="submit" value="Asignar nota">';
            echo '</form>';
            echo '<br>';
        }
        echo '<br>';
        echo '<br>';
        echo '<br>';
        echo '<br>';
    } else {
        // Si el curso no existe o no fue creado por el usuario, mostrar un mensaje de error
        echo '<p>El curso solicitado no existe o no lo has creado tú.</p>';
    }
} else {
    // Si el id del curso es inválido, mostrar un mensaje de error
    echo '<p>El id del curso es inválido.</p>';
}
echo '</div>';
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

    $('.pagado').change(function() {
        var id_curso = $(this).data('id-curso');
        var id_usuario = $(this).data('id-usuario');
        var pagado = $(this).is(':checked') ? 1 : 0;

        $.ajax({
            url: '../controllers/actualizar_estado.php',
            type: 'POST',
            data: {
                id_curso: id_curso,
                id_usuario: id_usuario,
                pagado: pagado
            },
            success: function(response) {
                console.log(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    });

    $('form.asignar-nota').submit(function(event) {
        event.preventDefault();
        var form = $(this);
        var id_usuario = form.data('id-usuario');
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            success: function(response) {
                var data = JSON.parse(response);
                if (data.status === 'success') {
                    alert(data.message);
                    $('td.nota[data-id-usuario="' + id_usuario + '"]').text(form.find('input[name="nota"]').val());
                } else {
                    alert(data.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    });

$(document).ready(function(){
$('#actualizar-tomos-folios').click(function() {
    $('.tomo, .folio').each(function() {
        var id_curso = $(this).data('id-curso');
        var id_usuario = $(this).data('id-usuario');
        var tomo = $('.tomo[data-id-usuario="' + id_usuario + '"]').val();
        var folio = $('.folio[data-id-usuario="' + id_usuario + '"]').val();

        tomo = tomo !== '' ? tomo : null;
        folio = folio !== '' ? folio : null;

        $.ajax({
            url: '../controllers/actualizar_estado.php',
            type: 'POST',
            data: {
                id_curso: id_curso,
                id_usuario: id_usuario,
                tomo: tomo,
                folio: folio
            },
            success: function(response) {
                console.log(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    });

    alert('Todos los tomos y folios han sido actualizados');
});
});
});
</script>