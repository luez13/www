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

// Obtener el id del curso de la URL
$id_curso = $_GET['id_curso'];

// Usar el método de la clase Curso para obtener los datos del curso que se quiere editar
$curso_editar = $curso->obtener_curso($id_curso);

// Mostrar un formulario para editar el curso con los datos actuales
echo '<div class="main-content">';
echo '<h3>Editar curso</h3>';
echo '<form action="../controllers/curso_controlador.php" method="post">';
echo '<input type="hidden" name="action" value="editar">';
echo '<input type="hidden" name="id_curso" value="' . $id_curso . '">';
echo '<p>Nombre del curso: <input type="text" name="nombre_curso" value="' . htmlspecialchars($curso_editar['nombre_curso']) . '" required></p>';
echo '<p>Descripción: <textarea name="descripcion" required>' . htmlspecialchars($curso_editar['descripcion']) . '</textarea></p>';
echo '<p>Semanas: <input type="number" name="tiempo_asignado" value="' . htmlspecialchars($curso_editar['tiempo_asignado']) . '" min="1" required></p>';
echo '<p>Fecha de inicio: <input type="date" name="inicio_mes" value="' . htmlspecialchars($curso_editar['inicio_mes']) . '" required></p>';
echo '<p>Tipo de curso: <select name="tipo_curso" required>';
echo '<option value="masterclass"' . ($curso_editar['tipo_curso'] == 'masterclass' ? ' selected' : '') . '>Masterclass</option>';
echo '<option value="seminarios"' . ($curso_editar['tipo_curso'] == 'seminarios' ? ' selected' : '') . '>Seminarios</option>';
echo '<option value="diplomados"' . ($curso_editar['tipo_curso'] == 'diplomados' ? ' selected' : '') . '>Diplomados</option>';
echo '<option value="congreso"' . ($curso_editar['tipo_curso'] == 'congreso' ? ' selected' : '') . '>Congreso</option>';
echo '<option value="charla"' . ($curso_editar['tipo_curso'] == 'charla' ? ' selected' : '') . '>Charla</option>';
echo '<option value="taller"' . ($curso_editar['tipo_curso'] == 'taller' ? ' selected' : '') . '>taller</option>';
echo '<option value="curso"' . ($curso_editar['tipo_curso'] == 'curso' ? ' selected' : '') . '>Curso</option>';
echo '</select></p>';
echo '<p>Límite de inscripciones: <input type="number" name="limite_inscripciones" value="' . htmlspecialchars($curso_editar['limite_inscripciones']) . '" min="1" required></p>';
echo '<p>Días de clase:</p>';
$dias_clase = explode(',', trim($curso_editar['dias_clase'], '{}'));
$dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
foreach ($dias as $dia) {
    echo '<p><input type="checkbox" name="dias_clase[]" value="' . $dia . '"' . (in_array($dia, $dias_clase) ? ' checked' : '') . '> ' . $dia . '</p>';
}
echo '<p>Horario de inicio: <input type="time" name="horario_inicio" value="' . htmlspecialchars($curso_editar['horario_inicio']) . '" required></p>';
echo '<p>Horario de fin: <input type="time" name="horario_fin" value="' . htmlspecialchars($curso_editar['horario_fin']) . '" required></p>';
echo '<p>Nivel del curso: <select name="nivel_curso" required>';
echo '<option value="introductorio"' . ($curso_editar['nivel_curso'] == 'introductorio' ? ' selected' : '') . '>Introductorio</option>';
echo '<option value="medio"' . ($curso_editar['nivel_curso'] == 'medio' ? ' selected' : '') . '>Medio</option>';
echo '<option value="avanzado"' . ($curso_editar['nivel_curso'] == 'avanzado' ? ' selected' : '') . '>Avanzado</option>';
echo '</select></p>';
echo '<p>Costo: <input type="number" name="costo" value="' . htmlspecialchars($curso_editar['costo']) . '" step="0.01" required></p>';
echo '<p>Conocimientos previos: <textarea name="conocimientos_previos" required>' . htmlspecialchars($curso_editar['conocimientos_previos']) . '</textarea></p>';
echo '<p>Requerimientos e implementos: <textarea name="requerimientos_implementos" required>' . htmlspecialchars($curso_editar['requerimientos_implemento']) . '</textarea></p>';
echo '<p>Desempeño al concluir: <textarea name="desempeño_al_concluir" required>' . htmlspecialchars($curso_editar['desempeno_al_concluir']) . '</textarea></p>';

// Mostrar los módulos del curso
echo '<h4>Módulos del curso</h4>';
echo '<div id="moduleContainer">';

// Ajuste en el formulario para añadir campos ocultos
foreach ($curso_editar['modulos'] as $modulo) {
    echo '<div class="module">';
    echo '<input type="hidden" name="id_modulo[]" value="' . htmlspecialchars($modulo['id_modulo']) . '">';
    echo '<input type="hidden" name="id_curso_modulo[]" value="' . htmlspecialchars($id_curso) . '">';
    echo '<p>Nombre del módulo: <input type="text" name="nombre_modulo[]" value="' . htmlspecialchars($modulo['nombre_modulo']) . '" required></p>';
    echo '<div class="container-contenido">';
    $contenidos = explode('][', trim($modulo['contenido'], '[]'));
    foreach ($contenidos as $contenido) {
        echo '<textarea name="contenido[]" required>' . htmlspecialchars($contenido) . '</textarea>';
        // Añadir campo oculto para el número y ID del módulo
        echo '<input type="hidden" name="numero_modulo_contenido[]" value="' . htmlspecialchars($modulo['numero']) . '">';
        echo '<input type="hidden" name="id_modulo_contenido[]" value="' . htmlspecialchars($modulo['id_modulo']) . '">';
        echo '<button type="button" onclick="quitarContenido(this)">Quitar contenido</button>';
    }
    echo '</div>';
    echo '<button type="button" onclick="agregarContenido(this)">Agregar contenido</button>';
    echo '<p>Actividad: <input type="text" name="actividad_modulo[]" value="' . htmlspecialchars($modulo['actividad']) . '" required></p>';
    echo '<p>Instrumento: <input type="text" name="instrumento_modulo[]" value="' . htmlspecialchars($modulo['instrumento']) . '" required></p>';
    echo '<p>Número: <input type="number" name="numero_modulo[]" value="' . htmlspecialchars($modulo['numero']) . '" required></p>';
    echo '</div>';
}

echo '</div>'; // Fin de moduleContainer

// Botón para agregar nuevos módulos
echo '<button type="button" id="addModuleBtn" class="btn btn-secondary">Agregar Módulo</button>';

echo '<p><input type="submit" value="Editar curso"></p>';
echo '</form>';
echo '</div>';

// Incluir el archivo footer.php en views
include '../views/footer.php';
?>

<script>
    document.getElementById('addModuleBtn').addEventListener('click', addModuleFields);

    function agregarContenido(btn) {
        var containerContenido = btn.previousElementSibling;
        var newTextArea = document.createElement('textarea');
        newTextArea.name = 'contenido[]';
        newTextArea.placeholder = 'Contenido';
        newTextArea.required = true;
        var buttonQuitarContenido = document.createElement('button');
        buttonQuitarContenido.type = 'button';
        buttonQuitarContenido.textContent = 'Quitar contenido';
        buttonQuitarContenido.onclick = function() {
            containerContenido.removeChild(newTextArea);
            containerContenido.removeChild(buttonQuitarContenido);
        };
        containerContenido.appendChild(newTextArea);
        containerContenido.appendChild(buttonQuitarContenido);
    }

    function addModuleFields() {
        var container = document.getElementById('moduleContainer');
        var moduleCount = container.children.length;
        var moduleDiv = document.createElement('div');
        moduleDiv.className = 'module';
        moduleDiv.innerHTML = `
            <p>Nombre del módulo: <input type="text" name="nombre_modulo[]" required></p>
            <div class="container-contenido">
                <textarea name="contenido[]" placeholder="Contenido" required></textarea>
            </div>
            <button type="button" onclick="agregarContenido(this)">Agregar contenido</button>
            <p>Actividad: <input type="text" name="actividad_modulo[]" required></p>
            <p>Instrumento: <input type="text" name="instrumento_modulo[]" required></p>
            <p>Número: <input type="number" name="numero_modulo[]" required></p>
            <input type="hidden" name="id_modulo[]" value="">
        `;
        container.appendChild(moduleDiv);
    }

    function combineContentsBeforeSubmit() {
    var modules = document.getElementsByClassName('module');
    for (var i = 0; i < modules.length; i++) {
        var containerContenido = modules[i].getElementsByClassName('container-contenido')[0];
        var textareas = containerContenido.getElementsByTagName('textarea');
        var combinedContent = '';
        for (var j = 0; j < textareas.length; j++) {
            combinedContent += '[' + textareas[j].value + ']';
        }
        if (textareas.length > 0) {
            textareas[0].value = combinedContent;
            while (textareas.length > 1) {
                containerContenido.removeChild(textareas[1]);
            }
        }
    }
}

document.getElementById('crearCursoForm').onsubmit = function(e) {
    e.preventDefault();
    combineContentsBeforeSubmit(); // Combinar contenidos antes de enviar
    var formData = new FormData(this);
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
</script>