<?php
// Incluir el archivo model.php en config
include '../config/model.php';

// Incluir el archivo init.php en views
include '../controllers/init.php';

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
?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="h3 text-gray-800">Editar Postulación: <?= htmlspecialchars($curso_editar['nombre_curso']) ?></h3>
        <button type="button" class="btn btn-secondary btn-sm"
            onclick="loadPage('../public/gestion_cursos.php', {action: 'ver'})"><i class="fas fa-arrow-left me-1"></i>
            Volver a Postulaciones</button>
    </div>

    <form id="crearCursoForm" method="post">
        <input type="hidden" name="action" value="editar">
        <input type="hidden" name="id_curso" value="<?= $id_curso ?>">

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Información General</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Nombre del curso:</label>
                    <input type="text" class="form-control" name="nombre_curso"
                        value="<?= htmlspecialchars($curso_editar['nombre_curso']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción:</label>
                    <textarea class="form-control" name="descripcion"
                        required><?= htmlspecialchars($curso_editar['descripcion']) ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tipo de curso:</label>
                        <select class="form-select" name="tipo_curso" required>
                            <?php $tipos = ['masterclass', 'seminario', 'diplomado', 'congreso', 'charla', 'taller', 'curso', 'masterclass_rectoria', 'seminario_rectoria', 'diplomado_rectoria', 'congreso_rectoria', 'charla_rectoria', 'taller_rectoria', 'curso_rectoria']; ?>
                            <?php foreach ($tipos as $tipo): ?>
                                <option value="<?= $tipo ?>" <?= ($curso_editar['tipo_curso'] == $tipo) ? 'selected' : '' ?>>
                                    <?= ucfirst(str_replace('_', ' ', $tipo)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nivel del curso:</label>
                        <select class="form-select" name="nivel_curso" required>
                            <option value="introductorio" <?= ($curso_editar['nivel_curso'] == 'introductorio' ? 'selected' : '') ?>>Introductorio</option>
                            <option value="medio" <?= ($curso_editar['nivel_curso'] == 'medio' ? 'selected' : '') ?>>Medio
                            </option>
                            <option value="avanzado" <?= ($curso_editar['nivel_curso'] == 'avanzado' ? 'selected' : '') ?>>
                                Avanzado</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Estado:</label>
                        <select class="form-select" name="estado" required>
                            <option value="1" <?= ($curso_editar['estado'] == true ? 'selected' : '') ?>>Activo</option>
                            <option value="0" <?= ($curso_editar['estado'] == false ? 'selected' : '') ?>>Inactivo /
                                Finalizado</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Fechas y Horarios</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Fecha de inicio:</label>
                        <input class="form-control" type="date" name="inicio_mes"
                            value="<?= htmlspecialchars($curso_editar['inicio_mes']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Duración (semanas):</label>
                        <input class="form-control" type="number" name="tiempo_asignado"
                            value="<?= htmlspecialchars($curso_editar['tiempo_asignado']) ?>" min="1" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Horario de inicio:</label>
                        <input class="form-control" type="time" name="horario_inicio"
                            value="<?= htmlspecialchars($curso_editar['horario_inicio']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Horario de fin:</label>
                        <input class="form-control" type="time" name="horario_fin"
                            value="<?= htmlspecialchars($curso_editar['horario_fin']) ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label d-block">Días de clase:</label>
                    <div class="p-2 border rounded bg-light">
                        <?php
                        $dias_clase = explode(',', trim($curso_editar['dias_clase'], '{}'));
                        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                        foreach ($dias as $dia): ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="dias_clase[]" value="<?= $dia ?>"
                                    id="dia_<?= $dia ?>" <?= (in_array($dia, $dias_clase) ? 'checked' : '') ?>>
                                <label class="form-check-label" for="dia_<?= $dia ?>"><?= ucfirst($dia) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Detalles Académicos</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Límite de inscripciones:</label>
                        <input class="form-control" type="number" name="limite_inscripciones"
                            value="<?= htmlspecialchars($curso_editar['limite_inscripciones']) ?>" min="1" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Costo:</label>
                        <input class="form-control" type="number" name="costo"
                            value="<?= htmlspecialchars($curso_editar['costo']) ?>" step="0.01" min="0" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Conocimientos previos:</label>
                    <textarea class="form-control" name="conocimientos_previos"
                        required><?= htmlspecialchars($curso_editar['conocimientos_previos']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Requerimientos e implementos:</label>
                    <textarea class="form-control" name="requerimientos_implementos"
                        required><?= htmlspecialchars($curso_editar['requerimientos_implemento']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Desempeño al concluir:</label>
                    <textarea class="form-control" name="desempeño_al_concluir"
                        required><?= htmlspecialchars($curso_editar['desempeno_al_concluir']) ?></textarea>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Módulos del Curso</h6>
                <button type="button" id="addModuleBtn" class="btn btn-success btn-sm"><i class="fas fa-plus"></i>
                    Agregar Módulo</button>
            </div>
            <div class="card-body">
                <div id="moduleContainer">
                    <?php foreach ($curso_editar['modulos'] as $modulo): ?>
                        <div class="module p-3 border rounded border-left-info bg-light mb-3">
                            <input type="hidden" name="id_modulo[]" value="<?= htmlspecialchars($modulo['id_modulo']) ?>">
                            <input type="hidden" name="id_curso_modulo[]" value="<?= htmlspecialchars($id_curso) ?>">

                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Nombre del módulo:</label>
                                    <input type="text" class="form-control" name="nombre_modulo[]"
                                        value="<?= htmlspecialchars($modulo['nombre_modulo']) ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Número:</label>
                                    <input type="number" class="form-control" name="numero_modulo[]"
                                        value="<?= htmlspecialchars($modulo['numero']) ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label d-flex justify-content-between">Contenido <button type="button"
                                        class="btn btn-secondary btn-sm" onclick="agregarContenido(this)"><i
                                            class="fas fa-plus"></i></button></label>
                                <div class="container-contenido">
                                    <?php $contenidos = explode('][', trim($modulo['contenido'], '[]')); ?>
                                    <?php foreach ($contenidos as $contenido): ?>
                                        <div class="d-flex mb-2">
                                            <textarea class="form-control me-2" name="contenido[]" rows="2"
                                                required><?= htmlspecialchars($contenido) ?></textarea>
                                            <input type="hidden" name="numero_modulo_contenido[]"
                                                value="<?= htmlspecialchars($modulo['numero']) ?>">
                                            <input type="hidden" name="id_modulo_contenido[]"
                                                value="<?= htmlspecialchars($modulo['id_modulo']) ?>">
                                            <button type="button" class="btn btn-outline-danger btn-sm"
                                                onclick="quitarContenido(this)"><i class="fas fa-minus"></i></button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Actividad:</label>
                                    <input type="text" class="form-control" name="actividad_modulo[]"
                                        value="<?= htmlspecialchars($modulo['actividad']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Instrumento:</label>
                                    <input type="text" class="form-control" name="instrumento_modulo[]"
                                        value="<?= htmlspecialchars($modulo['instrumento']) ?>" required>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="text-end mb-4">
            <button type="submit" class="btn btn-primary btn-lg shadow"><i class="fas fa-save me-2"></i>Guardar
                Cambios</button>
        </div>
    </form>
</div>


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
        buttonQuitarContenido.onclick = function () {
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

    document.getElementById('crearCursoForm').onsubmit = function (e) {
        e.preventDefault();
        combineContentsBeforeSubmit(); // Combinar contenidos antes de enviar
        var formData = new FormData(this);
        $.ajax({
            url: '../controllers/curso_controlador.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                alert(response);
                loadPage('../public/gestion_cursos.php', { action: 'ver' });
            },
            error: function () {
                alert('Error al editar el curso.');
            }
        });
    };
</script>