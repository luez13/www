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

// Asignar el tipo de evaluación según el valor booleano
$tipo_evaluacion = $curso_contenido['tipo_evaluacion'] == 0 ? 'Presencial' : 'Evaluada';

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
echo '<p>Duración: ' . $curso_contenido['duracion'] . '</p>';
echo '<p>Periodo: ' . $curso_contenido['periodo'] . '</p>';
echo '<p>Modalidad: ' . $curso_contenido['modalidad'] . '</p>';
echo '<p>Tipo de evaluación: ' . $tipo_evaluacion . '</p>';
echo '<p>Tipo de curso: ' . $curso_contenido['tipo_curso'] . '</p>';

// Mostrar los cupos disponibles
$stmt = $db->prepare('SELECT COUNT(*) FROM cursos.certificaciones WHERE curso_id = :curso_id');
$stmt->execute(['curso_id' => $id_curso]);
$count = $stmt->fetchColumn();
$cupos_disponibles = $curso_contenido['limite_inscripciones'] - $count;
echo '<p>Cupos disponibles: ' . $cupos_disponibles . '</p>';

echo '<p>Promotor: ' . $promotor['nombre'] . '</p>';

// Consultar si el usuario ya está inscrito en el curso
$stmt = $db->prepare('SELECT * FROM cursos.certificaciones WHERE curso_id = :curso_id AND id_usuario = :id_usuario');
$stmt->execute(['curso_id' => $id_curso, 'id_usuario' => $_SESSION['user_id']]);
$inscripcion = $stmt->fetch();

if ($_SESSION['id_rol'] != 4) {
    if (!$inscripcion) {
        // Si el usuario no está inscrito, mostrar el botón de inscribirse
        echo '<form action="../controllers/curso_acciones.php" method="post">';
        echo '<input type="hidden" name="action" value="inscribirse">';
        echo '<input type="hidden" name="curso_id" value="' . $id_curso . '">';
        echo '<input type="hidden" name="id_usuario" value="' . $_SESSION['user_id'] . '">';
        echo '<input type="submit" value="Inscribirse al curso">';
        echo '</form>';
    } else {
        // Si el tipo de evaluación es 'Evaluada', mostrar la nota del usuario
        if ($tipo_evaluacion == 'Evaluada') {
            echo '<p>Nota: ' . $inscripcion['nota'] . '</p>';
        }
        // Si el usuario ya está inscrito y el curso no está completado, mostrar el botón de cancelar inscripción
        if (!$inscripcion['completado']) {
            echo '<form action="../controllers/curso_acciones.php" method="post">';
            echo '<input type="hidden" name="action" value="cancelar_inscripcion">';
            echo '<input type="hidden" name="id_usuario" value="' . $_SESSION['user_id'] . '">';
            echo '<input type="hidden" name="curso_id" value="' . $id_curso . '">';
            echo '<input type="submit" value="Cancelar inscripción">';
            echo '</form>';
        } else {
            // Si el curso está completado, mostrar el botón de ver certificado
            echo '<button id="ver-certificado">Ver Certificado</button>';
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

document.addEventListener('DOMContentLoaded', (event) => {
    var certificadoButton = document.getElementById('ver-certificado');
    if(certificadoButton) {
        certificadoButton.addEventListener('click', function() {
            // Crear una nueva instancia de jsPDF
            var doc = new jsPDF();

            // Agregar imagen de fondo
            var imgData = 'data:image/jpeg;base64,<?php echo $base64Image; ?>';
            doc.addImage(imgData, 'JPEG', 0, 0, doc.internal.pageSize.getWidth(), doc.internal.pageSize.getHeight());

            // Agregar texto al documento
            doc.setFontSize(36);
            doc.setFont('helvetica'); // Cambia la familia de fuentes y el estilo
            drawTextWithBorder(doc, 'Certificado de Formación', doc.internal.pageSize.getWidth() / 2, 60); // Centra el texto
            doc.setFontSize(20);
            drawTextWithBorder(doc, 'Nombre del Curso: ' + '<?php echo $curso_contenido['nombre_curso']; ?>', doc.internal.pageSize.getWidth() / 2, 120); // Centra el texto
            drawTextWithBorder(doc, 'Nombre del Estudiante: ' + '<?php echo $_SESSION['nombre']; ?>', doc.internal.pageSize.getWidth() / 2, 150); // Centra el texto
            doc.setFontSize(16);
            drawTextWithBorder(doc, 'Certificamos que el estudiante ha completado el curso.', doc.internal.pageSize.getWidth() / 2, 180); // Centra el texto

            // Abrir el PDF en una nueva pestaña
            window.open(doc.output('bloburl'), '_blank');
        });
    }
});
</script>