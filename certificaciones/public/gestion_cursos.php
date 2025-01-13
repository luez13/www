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
$cursoModel = new Curso($db);

// Obtener el id del usuario de la sesión
$user_id = $_SESSION['user_id'];

echo '<div class="main-content">';

if (isset($_GET['action']) && $_GET['action'] == 'crear') {
// Mostrar formulario para crear un nuevo curso
echo '<h3>Crear un nuevo curso</h3>';
echo '<form id="crearCursoForm" method="post">';
echo '<input type="hidden" name="action" value="crear">';
echo '<p>Nombre del curso: <input type="text" name="nombre_curso" required></p>';
echo '<p>Descripción: <textarea name="descripcion" required></textarea></p>';
echo '<p>Semanas: <input type="number" name="tiempo_asignado" min="1" required></p>';
echo '<p>Fecha de inicio: <input type="date" name="inicio_mes" required></p>';
echo '<p>Tipo de curso: <select name="tipo_curso" required>';
echo '<option value="masterclass">MasterClass</option>';
echo '<option value="talleres">Talleres</option>';
echo '<option value="curso">Cursos</option>';
echo '<option value="seminarios">Seminarios</option>';
echo '<option value="diplomados">Diplomados</option>';
echo '<option value="congreso">Congreso</option>';
echo '<option value="charla">Charla</option>';
echo '</select></p>';    
echo '<p>Límite de inscripciones: <input type="number" name="limite_inscripciones" min="1" required></p>';
echo '<p>Días de clase:</p>';
echo '<p><input type="checkbox" name="dias_clase[]" value="Lunes"> Lunes</p>';
echo '<p><input type="checkbox" name="dias_clase[]" value="Martes"> Martes</p>';
echo '<p><input type="checkbox" name="dias_clase[]" value="Miércoles"> Miércoles</p>';
echo '<p><input type="checkbox" name="dias_clase[]" value="Jueves"> Jueves</p>';
echo '<p><input type="checkbox" name="dias_clase[]" value="Viernes"> Viernes</p>';
echo '<p><input type="checkbox" name="dias_clase[]" value="Sábado"> Sábado</p>';
echo '<p><input type="checkbox" name="dias_clase[]" value="Domingo"> Domingo</p>';
echo '<p>Horario de inicio: <input type="time" name="horario_inicio" required></p>';
echo '<p>Horario de fin: <input type="time" name="horario_fin" required></p>';
echo '<p>Nivel del curso: <select name="nivel_curso" required>';
echo '<option value="introductorio">Introductorio</option>';
echo '<option value="medio">Medio</option>';
echo '<option value="avanzado">Avanzado</option>';
echo '</select></p>';
echo '<p>Costo: <input type="number" name="costo" step="0.01" min="0"></p>';
echo '<p>Conocimientos previos: <textarea name="conocimientos_previos" required></textarea></p>';
echo '<p>Requerimientos e implementos: <textarea name="requerimientos_implementos" required></textarea></p>';
echo '<p>Desempeño al concluir: <textarea name="desempeño_al_concluir" required></textarea></p>';
echo '<form id="cursoForm" method="post">';
echo '<p>Número de módulos: <input type="number" id="numero_modulos" name="numero_modulos" min="1" required onblur="addModuleFields()"></p>';

echo '<div id="moduleContainer">
        <!-- Los campos de los módulos se agregarán aquí -->
    </div>';
echo '<p><input type="submit" value="Crear curso"></p>';
echo '</form>';
}
elseif (isset($_GET['action']) && $_GET['action'] == 'ver') {
// Obtener los cursos creados por el usuario
$cursos = $cursoModel->obtener_contenido($user_id);

// Mostrar una tabla con los cursos creados por el usuario
echo '<h3>Cursos creados por ti</h3>';
echo '<div class="table-responsive">';
echo '<table class="table table-striped">';
echo '<thead class="thead-dark">';
echo '<tr>';
echo '<th>Nombre</th>';
echo '<th>Descripción</th>';
echo '<th>Semanas</th>';
echo '<th>Fecha de inicio</th>';
echo '<th>Tipo de curso</th>';
echo '<th>Límite de inscripciones</th>';
echo '<th>Días de clase</th>';
echo '<th>Horario de inicio</th>';
echo '<th>Horario de fin</th>';
echo '<th>Nivel del curso</th>';
echo '<th>Costo</th>';
echo '<th>Conocimientos previos</th>';
echo '<th>Requerimientos e implementos</th>';
echo '<th>Desempeño al concluir</th>';
echo '<th>Estado</th>';
echo '<th>Opciones</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

foreach ($cursos as $curso) {
    echo '<tr>';
    echo '<td>' . $curso['nombre_curso'] . '</td>';
    echo '<td>' . $curso['descripcion'] . '</td>';
    echo '<td>' . $curso['tiempo_asignado'] . '</td>';
    echo '<td>' . $curso['inicio_mes'] . '</td>';
    echo '<td>' . $curso['tipo_curso'] . '</td>';
    echo '<td>' . $curso['limite_inscripciones'] . '</td>';
    echo '<td>' . $curso['dias_clase'] . '</td>';
    echo '<td>' . $curso['horario_inicio'] . '</td>';
    echo '<td>' . $curso['horario_fin'] . '</td>';
    echo '<td>' . $curso['nivel_curso'] . '</td>';
    echo '<td>' . $curso['costo'] . '</td>';
    echo '<td>' . $curso['conocimientos_previos'] . '</td>';
    echo '<td>' . $curso['requerimientos_implemento'] . '</td>';
    echo '<td>' . $curso['desempeno_al_concluir'] . '</td>';
    echo '<td>' . ($curso['estado'] ? 'Activo' : 'Finalizado') . '</td>';
    echo '<td>';
    echo '<div class="btn-group-vertical" role="group">';
    echo '<button class="btn btn-secondary mb-1" onclick="loadPage(\'../views/curso_formulario.php\', {id_curso: ' . $curso['id_curso'] . '})">Editar</button>';

    echo '<button class="btn btn-dark mb-1" onclick="loadPage(\'../public/detalles_curso.php\', {id: ' . $curso['id_curso'] . '})">Detalles del curso</button>';

    $estado = $curso['estado'] ? 'Finalizar' : 'Iniciar';
    $action = $curso['estado'] ? 'finalizar' : 'iniciar';
    echo '<button class="btn btn-success mb-1" onclick="cambiarEstadoCurso(' . $curso['id_curso'] . ', \'' . $action . '\')">' . $estado . '</button>';

    // Mostrar el botón de eliminar solo si no hay inscritos o aprobados

        echo '<button class="btn btn-danger" onclick="eliminarCurso(' . $curso['id_curso'] . ')">Eliminar</button>';


    // Botón para mostrar/ocultar módulos
    echo '<button class="btn btn-info mb-1" data-bs-toggle="collapse" data-bs-target="#modulos-' . $curso['id_curso'] . '">Módulos</button>';

    echo '</div>';
    echo '</td>';
    echo '</tr>';

    // Mostrar los módulos del curso en un contenedor colapsable
    if (!empty($curso['modulos'])) {
        echo '<tr>';
        echo '<td colspan="16">';
        echo '<div id="modulos-' . $curso['id_curso'] . '" class="collapse">';
        echo '<h4>Módulos del curso</h4>';
        echo '<ul class="list-group">';
        foreach ($curso['modulos'] as $modulo) {
            echo '<li class="list-group-item">';
            echo '<strong>Modulo' . $modulo['numero'] .  '</strong> <br>';
            echo '<strong>Nombre:</strong> ' . $modulo['nombre_modulo'] . '<br>';
            echo '<strong>Contenido:</strong> ' . $modulo['contenido'] . '<br>';
            echo '<strong>Actividad:</strong> ' . $modulo['actividad'] . '<br>';
            echo '<strong>Instrumento:</strong> ' . $modulo['instrumento'] . '<br>';
            echo '</li>';
        }
        echo '</ul>';
        echo '</div>';
        echo '</td>';
        echo '</tr>';
    }
}
echo '</tbody>';
echo '</table>';
echo '</div>';
}

echo '</div>';
include '../views/footer.php';
?>

<script src="../models/module_processing.js"></script>
<script>
    function eliminarCurso(id_curso) {
        // Primera confirmación
        const primeraConfirmacion = confirm("¿Estás seguro de que deseas eliminar este curso?");
        if (primeraConfirmacion) {
            // Segunda confirmación
            const segundaConfirmacion = confirm("Esta acción no se puede deshacer. ¿Estás realmente seguro?");
            if (segundaConfirmacion) {
                // Proceder con la eliminación si ambas confirmaciones son positivas
                $.ajax({
                    url: '../controllers/curso_controlador.php',
                    type: 'POST',
                    data: { id_curso: id_curso, action: 'eliminar' },
                    success: function(response) {
                        alert(response);
                        window.location.href = '../public/perfil.php?seccion=ver_postulaciones';
                    },
                    error: function() {
                        alert('Error al eliminar el curso.');
                    }
                });
            }
        }
    }

    document.getElementById('crearCursoForm').onsubmit = function(e) {
        e.preventDefault();
        combineContentsBeforeSubmit(); // Asegurarnos de combinar contenidos antes de enviar
        const formData = new FormData(this);
        $.ajax({
            url: '../controllers/curso_controlador.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                alert(response);
                window.location.href = '../public/perfil.php?seccion=ver_postulaciones';
            },
            error: function() {
                alert('Error al crear el curso.');
            }
        });
    };

    function cambiarEstadoCurso(id_curso, action) {
        $.ajax({
            url: '../controllers/curso_controlador.php',
            type: 'POST',
            data: { id_curso: id_curso, action: action },
            success: function(response) {
                alert(response);
                window.location.href = '../public/perfil.php?seccion=ver_postulaciones';
            },
            error: function() {
                alert('Error al cambiar el estado del curso.');
            }
        });
    }

    document.getElementById('numero_modulos').onblur = addModuleFields;
</script>