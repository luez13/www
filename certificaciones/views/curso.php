<?php
// Incluir el archivo model.php en config
include '../config/model.php';

// Incluir el archivo header.php en views
include '../views/header.php';

// Incluir el archivo curso.php en models
include '../models/curso.php';

// Obtener el id del usuario de la sesión
$id_usuario = $_SESSION['user_id'];

// Crear una instancia de la clase DB
$db = new DB();

// Crear una instancia de la clase Curso
$curso = new Curso($db);

// Obtener el id del curso de la URL
$id_curso = $_GET['id'];

// Usar el método de la clase Curso para obtener el contenido del curso
$curso_contenido = $curso->obtener_curso($id_curso);

// Asignar el tipo de evaluación según el valor booleano
if ($curso_contenido['tipo_evaluacion'] == 0) {
    $tipo_evaluacion = 'Presencial';
} else {
    $tipo_evaluacion = 'Evaluada';
}

// Obtener el nombre del promotor
$stmt = $db->prepare('SELECT nombre FROM cursos.usuarios WHERE id = :id_promotor');
$stmt->execute(['id_promotor' => $curso_contenido['promotor']]);
$promotor = $stmt->fetch();

echo '<div class="main-content">';

// Mostrar el contenido del curso en formato HTML
echo '<h3>Contenido del curso</h3>';
echo '<p>Nombre: ' . $curso_contenido['nombre_curso'] . '</p>';
echo '<p>Descripción: ' . $curso_contenido['descripcion'] . '</p>';
echo '<p>Duración: ' . $curso_contenido['duracion'] . '</p>';
echo '<p>Periodo: ' . $curso_contenido['periodo'] . '</p>';
echo '<p>Modalidad: ' . $curso_contenido['modalidad'] . '</p>';
// Mostrar el tipo de evaluación del curso
echo '<p>Tipo de evaluación: ' . $tipo_evaluacion . '</p>';
echo '<p>Tipo de curso: ' . $curso_contenido['tipo_curso'] . '</p>';

// Mostrar los cupos disponibles
$stmt = $db->prepare('SELECT COUNT(*) FROM cursos.certificaciones WHERE curso_id = :curso_id');
$stmt->execute(['curso_id' => $id_curso]);
$count = $stmt->fetchColumn();
$cupos_disponibles = $curso_contenido['limite_inscripciones'] - $count;
echo '<p>Cupos disponibles: ' . $cupos_disponibles . '</p>';

echo '<p>Promotor: ' . $promotor['nombre'] . '</p>'; // Mostrar el nombre del promotor

// Consultar si el usuario ya está inscrito en el curso
$stmt = $db->prepare('SELECT * FROM cursos.certificaciones WHERE curso_id = :curso_id AND id_usuario = :id_usuario');
$stmt->execute(['curso_id' => $id_curso, 'id_usuario' => $_SESSION['user_id']]);
$inscripcion = $stmt->fetch();

if (!$inscripcion) {
    // Si el usuario no está inscrito, mostrar el botón de inscribirse
    echo '<form action="../controllers/curso_acciones.php" method="post">';
    echo '<input type="hidden" name="action" value="inscribirse">';
    echo '<input type="hidden" name="curso_id" value="' . $id_curso . '">';
    echo '<input type="hidden" name="id_usuario" value="' . $_SESSION['user_id'] . '">';
    echo '<input type="submit" value="Inscribirse al curso">';
    echo '</form>';
} else {
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

    // Si el tipo de evaluación es 'Evaluada', mostrar la nota del usuario
    if ($tipo_evaluacion == 'Evaluada') {
        echo '<p>Nota: ' . $inscripcion['nota'] . '</p>';
    }
}

echo '</div>';
// Incluir el archivo footer.php en views
include '../views/footer.php';
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.debug.js"></script>
<script>
document.addEventListener('DOMContentLoaded', (event) => {
    var certificadoButton = document.getElementById('ver-certificado');
    if(certificadoButton) {
        certificadoButton.addEventListener('click', function() {
            // Crear una nueva instancia de jsPDF
            var doc = new jsPDF();

            // Agregar texto al documento
            doc.setFontSize(22);
            doc.text('Certificado de Formación', 10, 10);
            doc.setFontSize(16);
            doc.text('Nombre del Curso: ' + '<?php echo $curso_contenido['nombre_curso']; ?>', 10, 20);
            doc.text('Nombre del Estudiante: ' + '<?php echo $_SESSION['nombre']; ?>', 10, 30);
            doc.text('Certificamos que el estudiante ha completado el curso.', 10, 40);
            doc.text('Fecha: ' + new Date().toLocaleDateString(), 10, 50);
            doc.text('Firma: ___________________', 10, 60);

            // Descargar el PDF
            doc.save('Certificado.pdf');
        });
    }
});
</script>