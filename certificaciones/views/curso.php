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
        echo '<button type="submit" id="inscribirse-btn">Inscribirse al curso</button>';
        echo '</form>';
    } else {
        echo '<p>Nota: ' . $inscripcion['nota'] . '</p>';
        // Si el usuario ya está inscrito y el curso no está completado, mostrar el botón de cancelar inscripción
        if (!$inscripcion['completado']) {
            echo '<form method="POST" action="../controllers/curso_acciones.php" onsubmit="return confirmarCancelacion()">';
            echo '<input type="hidden" name="action" value="cancelar_inscripcion">';
            echo '<input type="hidden" name="id_usuario" value="' . $_SESSION['user_id'] . '">';
            echo '<input type="hidden" name="curso_id" value="' . $id_curso . '">';
            echo '<button type="submit" id="cancelar-inscripcion-btn">Cancelar inscripción</button>';
            echo '</form>';
        } else {
            // Si el curso está completado, mostrar el botón de ver certificado
            echo '<button id="ver-certificado" onclick="generarCertificado()">Ver Certificado</button>';
        }
    }
}

echo '</div>';

// Ruta a tu imagen
$imagePath = '../public/assets/img/IUT.jpg';

// Obtén los contenidos de la imagen
$imageContent = file_get_contents($imagePath);

// Codifica los contenidos de la imagen a base64
$base64Image = base64_encode($imageContent);

// Obtener el nombre del estudiante y el título del curso desde la sesión o base de datos
$nombreEstudiante = $_SESSION['nombre'];
$nombreCurso = $curso_contenido['nombre_curso'];

if (!$is_ajax) {
    // Incluir el archivo footer.php en views
    include '../views/footer.php';
}
?>

<script>
function drawTextWithBorder(doc, text, x, y) {
    var offset = 0.5; // Puedes ajustar este valor para cambiar el grosor del borde
    doc.setTextColor(255, 255, 255); // Color del borde (blanco)
    for(var i = -offset; i <= offset; i += offset) {
        for(var j = -offset; j <= offset; j += offset) {
            doc.text(text, x + i, y + j, { align: 'center' }); // Centra el texto
        }
    }
    doc.setTextColor(0, 0, 0); // Color del texto (negro)
    doc.text(text, x, y, { align: 'center' }); // Centra el texto
}

function generarCertificado() {
    // Crear una nueva instancia de jsPDF en orientación horizontal
    var doc = new jsPDF();

    // Agregar imagen de fondo
    var imgData = 'data:image/jpeg;base64,<?php echo $base64Image; ?>';
    doc.addImage(imgData, 'JPEG', 0, 0, doc.internal.pageSize.getWidth(), doc.internal.pageSize.getHeight());

    // Agregar texto al documento con los datos correspondientes
    doc.setFontSize(36);
    doc.setFont('helvetica');
    drawTextWithBorder(doc, 'Certificado de Formación', doc.internal.pageSize.getWidth() / 2, 60);

    // Agregar el nombre del curso y el estudiante
    drawTextWithBorder(doc, 'Nombre del Curso: ' + '<?php echo $curso_contenido['nombre_curso']; ?>', doc.internal.pageSize.getWidth() / 2, 120);
    drawTextWithBorder(doc, 'Nombre del Estudiante: ' + '<?php $nombreEstudiante?>', doc.internal.pageSize.getWidth() / 2, 150);

    // Agregar el resto de los datos necesarios
    doc.setFontSize(16);
    drawTextWithBorder(doc, 'Certificamos que el estudiante ha completado el curso.', doc.internal.pageSize.getWidth() / 2, 180);

    // Agregar información adicional según las especificaciones del certificado
    drawTextWithBorder(doc, 'Republica Bolivariana de Venezuela', doc.internal.pageSize.getWidth() / 2, 210);
    drawTextWithBorder(doc, 'Ministerio del Poder Popular para la Educacion Universitaria', doc.internal.pageSize.getWidth() / 2, 225);
    drawTextWithBorder(doc, 'Universidad Politecnica Territorial Agroindustrial del Estado Tachira', doc.internal.pageSize.getWidth() / 2, 240);

    // Agregar el nombre y fecha de expedición
    drawTextWithBorder(doc, '(nombre del promotor)', doc.internal.pageSize.getWidth() / 2, 270);
    drawTextWithBorder(doc, 'Certificado Expediado en la Ciudad de San Cristobal, a las *hora actual y fecha actual*', doc.internal.pageSize.getWidth() / 2, 285);

    // Abrir el PDF en una nueva pestaña
    window.open(doc.output('bloburl'), '_blank');
}

document.addEventListener('DOMContentLoaded', (event) => {
    var certificadoButton = document.getElementById('ver-certificado');
    if(certificadoButton) {
        certificadoButton.addEventListener('click', generarCertificado);
    }
});
</script>