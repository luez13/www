<?php
// Incluir el archivo model.php en config
include '../config/model.php';

// Crear una instancia de la clase DB
$db = new DB();

// Crear una instancia de la clase Curso
include '../models/curso.php';
$curso = new Curso($db);

// Obtener el id del curso de la URL
$id_curso = $_GET['id'];

// Usar el método de la clase Curso para obtener el contenido del curso
$curso_contenido = $curso->obtener_curso($id_curso);

// Obtener el nombre del promotor
$stmt = $db->prepare('SELECT nombre FROM cursos.usuarios WHERE id = :id_promotor');
$stmt->execute(['id_promotor' => $curso_contenido['promotor']]);
$promotor = $stmt->fetch();

// Obtener el valor único del curso
$stmt = $db->prepare('SELECT valor_unico FROM cursos.certificaciones WHERE curso_id = :curso_id');
$stmt->execute(['curso_id' => $id_curso]);
$valor_unico = $stmt->fetchColumn();

// Definir la base de la URL
$base_url = 'http://localhost/certificaciones/controllers/generar_certificado.php';

// Verificar si la solicitud es AJAX
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!$is_ajax) {
    // Incluir el archivo header.php en views
    include '../views/header.php';
}

echo '<div class="main-content">';
echo '<h3>Contenido del curso</h3>';
echo '<p>Nombre: ' . $curso_contenido['nombre_curso'] . '</p>';
echo '<p>Descripción: ' . $curso_contenido['descripcion'] . '</p>';
echo '<p>Tipo de curso: ' . $curso_contenido['tipo_curso'] . '</p>';
echo '<p>Tiempo asignado: ' . $curso_contenido['tiempo_asignado'] . '</p>';
echo '<p>Inicio del mes: ' . $curso_contenido['inicio_mes'] . '</p>';
echo '<p>Estado: ' . ($curso_contenido['estado'] ? 'Activo' : 'Inactivo') . '</p>';
echo '<p>Días de clase: ' . $curso_contenido['dias_clase'] . '</p>';
echo '<p>Horario de inicio: ' . $curso_contenido['horario_inicio'] . '</p>';
echo '<p>Horario de fin: ' . $curso_contenido['horario_fin'] . '</p>';
echo '<p>Nivel del curso: ' . $curso_contenido['nivel_curso'] . '</p>';
echo '<p>Costo: ' . $curso_contenido['costo'] . '</p>';
echo '<p>Conocimientos previos: ' . $curso_contenido['conocimientos_previos'] . '</p>';
echo '<p>Requerimientos: ' . $curso_contenido['requerimientos_implemento'] . '</p>';
echo '<p>Desempeño al culminar: ' . $curso_contenido['desempeno_al_concluir'] . '</p>';

// Mostrar los cupos disponibles
$stmt = $db->prepare('SELECT COUNT(*) FROM cursos.certificaciones WHERE curso_id = :curso_id');
$stmt->execute(['curso_id' => $id_curso]);
$count = $stmt->fetchColumn();
$cupos_disponibles = $curso_contenido['limite_inscripciones'] - $count;
echo '<p>Cupos disponibles: ' . $cupos_disponibles . '</p>';

echo '<p>Promotor: ' . $promotor['nombre'] . '</p>';

// Consultar los módulos del curso
$stmt = $db->prepare('SELECT * FROM cursos.modulos WHERE id_curso = :id_curso ORDER BY numero');
$stmt->execute(['id_curso' => $id_curso]);
$modulos = $stmt->fetchAll();

echo '<h3>Módulos del curso</h3>';
foreach ($modulos as $modulo) {
    echo '<div class="modulo">';
    echo '<h4>Módulo ' . $modulo['numero'] . ': ' . $modulo['nombre_modulo'] . '</h4>';
    echo '<p>Contenido: ' . $modulo['contenido'] . '</p>';
    echo '<p>Actividad: ' . $modulo['actividad'] . '</p>';
    echo '<p>Instrumento: ' . $modulo['instrumento'] . '</p>';
}

// Consultar si el usuario ya está inscrito en el curso
$stmt = $db->prepare('SELECT * FROM cursos.certificaciones WHERE curso_id = :curso_id AND id_usuario = :id_usuario');
$stmt->execute(['curso_id' => $id_curso, 'id_usuario' => $_SESSION['user_id']]);
$inscripcion = $stmt->fetch();

if ($_SESSION['id_rol'] != 4) {
    if (!$inscripcion) {
        // Si el usuario no está inscrito, mostrar el botón de inscribirse
        echo '<form method="POST" action="../controllers/curso_acciones.php" onsubmit="return confirmarInscripcion()">';
        echo '<input type="hidden" name="action" value="inscribirse">';
        echo '<input type="hidden" name="id_usuario" value="' . $_SESSION['user_id'] . '">';
        echo '<input type="hidden" name="curso_id" value="' . $id_curso . '">';
        echo '<button type="submit" id="inscribirse-btn" class="btn btn-primary">Inscribirse al curso</button>';
        echo '</form>';
    } else {
        echo '<p>Nota: ' . $inscripcion['nota'] . '</p>';
        // Si el usuario ya está inscrito y el curso no está completado, mostrar el botón de cancelar inscripción
        if (!$inscripcion['completado']) {
            echo '<form method="POST" action="../controllers/curso_acciones.php" onsubmit="return confirmarCancelacion()">';
            echo '<input type="hidden" name="action" value="cancelar_inscripcion">';
            echo '<input type="hidden" name="id_usuario" value="' . $_SESSION['user_id'] . '">';
            echo '<input type="hidden" name="curso_id" value="' . $id_curso . '">';
            echo '<button type="submit" id="cancelar-inscripcion-btn" class="btn btn-danger">Cancelar inscripción</button>';
            echo '</form>';
        } else {
            // Si el curso está completado, mostrar los botones de ver certificado y ver URL
            echo '<button class="btn btn-success" type="button" onclick="generarCertificado(\'' . $valor_unico . '\')">Ver Certificado</button>';
            echo '<button class="btn btn-info" type="button" data-bs-toggle="collapse" data-bs-target="#collapseURL" aria-expanded="false" aria-controls="collapseURL">Ver URL</button>';
            echo '<div class="collapse" id="collapseURL">';
            echo '<div class="card card-body">';
            echo '<p>Aquí va la URL del curso: <a href="' . $base_url . '?valor_unico=' . $valor_unico . '">https://example.com/curso/' . $valor_unico . '</a></p>';
            echo '</div>';
        }
    }
}

echo '</div>';

if (!$is_ajax) {
    // Incluir el archivo footer.php en views
    include '../views/footer.php';
}
?>