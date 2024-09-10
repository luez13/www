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
echo '<p>Nombre del curso: <input type="text" name="nombre_curso" value="' . $curso_editar['nombre_curso'] . '" required></p>';
echo '<p>Descripción: <textarea name="descripcion" required>' . $curso_editar['descripcion'] . '</textarea></p>';
echo '<p>Semanas: <input type="number" name="tiempo_asignado" value="' . $curso_editar['tiempo_asignado'] . '" min="1" required></p>';
echo '<p>Fecha de inicio: <input type="date" name="inicio_mes" value="' . $curso_editar['inicio_mes'] . '" required></p>';
echo '<p>Tipo de curso: <select name="tipo_curso" required>';
echo '<option value="seminarios"' . ($curso_editar['tipo_curso'] == 'seminarios' ? ' selected' : '') . '>Seminarios</option>';
echo '<option value="diplomados"' . ($curso_editar['tipo_curso'] == 'diplomados' ? ' selected' : '') . '>Diplomados</option>';
echo '<option value="congreso"' . ($curso_editar['tipo_curso'] == 'congreso' ? ' selected' : '') . '>Congreso</option>';
echo '<option value="charla"' . ($curso_editar['tipo_curso'] == 'charla' ? ' selected' : '') . '>Charla</option>';
echo '<option value="talleres"' . ($curso_editar['tipo_curso'] == 'talleres' ? ' selected' : '') . '>Talleres</option>';
echo '</select></p>';
echo '<p>Límite de inscripciones: <input type="number" name="limite_inscripciones" value="' . $curso_editar['limite_inscripciones'] . '" min="1" required></p>';
echo '<p>Días de clase:</p>';
$dias_clase = explode(',', $curso_editar['dias_clase']);
$dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
foreach ($dias as $dia) {
    echo '<p><input type="checkbox" name="dias_clase[]" value="' . $dia . '"' . (in_array($dia, $dias_clase) ? ' checked' : '') . '> ' . $dia . '</p>';
}
echo '<p>Horario de inicio: <input type="time" name="horario_inicio" value="' . $curso_editar['horario_inicio'] . '" required></p>';
echo '<p>Horario de fin: <input type="time" name="horario_fin" value="' . $curso_editar['horario_fin'] . '" required></p>';
echo '<p>Nivel del curso: <select name="nivel_curso" required>';
echo '<option value="introductorio"' . ($curso_editar['nivel_curso'] == 'introductorio' ? ' selected' : '') . '>Introductorio</option>';
echo '<option value="medio"' . ($curso_editar['nivel_curso'] == 'medio' ? ' selected' : '') . '>Medio</option>';
echo '<option value="avanzado"' . ($curso_editar['nivel_curso'] == 'avanzado' ? ' selected' : '') . '>Avanzado</option>';
echo '</select></p>';
echo '<p>Costo: <input type="number" name="costo" value="' . $curso_editar['costo'] . '" step="0.01" required></p>';
echo '<p>Conocimientos previos: <textarea name="conocimientos_previos" required>' . $curso_editar['conocimientos_previos'] . '</textarea></p>';
echo '<p>Requerimientos e implementos: <textarea name="requerimientos_implementos" required>' . $curso_editar['requerimientos_implemento'] . '</textarea></p>';
echo '<p>Desempeño al concluir: <textarea name="desempeño_al_concluir" required>' . $curso_editar['desempeno_al_concluir'] . '</textarea></p>';

// Mostrar los módulos del curso
echo '<h4>Módulos del curso</h4>';
foreach ($curso_editar['modulos'] as $modulo) {
    echo '<div class="module">';
    echo '<input type="hidden" name="id_modulo[]" value="' . $modulo['id_modulo'] . '">';
    echo '<p>Nombre del módulo: <input type="text" name="nombre_modulo[]" value="' . $modulo['nombre_modulo'] . '" required></p>';
    echo '<p>Contenido: <textarea name="contenido_modulo[]" required>' . $modulo['contenido'] . '</textarea></p>';
    echo '<p>Actividad: <input type="text" name="actividad_modulo[]" value="' . $modulo['actividad'] . '" required></p>';
    echo '<p>Instrumento: <input type="text" name="instrumento_modulo[]" value="' . $modulo['instrumento'] . '" required></p>';
    echo '</div>';
}

echo '<p><input type="submit" value="Editar curso"></p>';
echo '</form>';
echo '</div>';

// Incluir el archivo footer.php en views
include '../views/footer.php';
?>