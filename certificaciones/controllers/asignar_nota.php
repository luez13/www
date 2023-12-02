<?php
// Incluir el archivo model.php en config
include '../config/model.php';

// Incluir el archivo header.php en views
include '../views/header.php';

// Crear una instancia de la clase DB
$db = new DB();

// Crear una función para validar los datos
function validar_datos($id_usuario, $id_curso, $nota) {
    // Verificar que los datos no estén vacíos
    if (empty($id_usuario) || empty($id_curso) || empty($nota)) {
        return false;
    }
    // Verificar que los datos sean numéricos
    if (!is_numeric($id_usuario) || !is_numeric($id_curso) || !is_numeric($nota)) {
        return false;
    }
    // Verificar que la nota esté entre 0 y 100
    if ($nota < 0 || $nota > 100) {
        return false;
    }
    // Si todo está bien, devolver true
    return true;
}

// Crear una función para redirigir al usuario
function redirigir($url) {
    // Usar la función header para enviar el encabezado de redirección
    header('Location: ' . $url);
    // Terminar la ejecución del script
    exit();
}

// Obtener los datos del formulario
$id_usuario = $_POST['id_usuario'];
$id_curso = $_POST['id_curso'];
$nota = $_POST['nota'];

// Validar los datos
if (validar_datos($id_usuario, $id_curso, $nota)) {
    // Actualizar la base de datos con la nota
    try {
        $stmt = $db->prepare('UPDATE cursos.certificaciones SET nota = :nota WHERE id_usuario = :id_usuario AND id_curso = :id_curso');
        $stmt->execute(['nota' => $nota, 'id_usuario' => $id_usuario, 'id_curso' => $id_curso]);
        // Mostrar un mensaje de éxito al usuario
        echo '<p>La nota se ha asignado correctamente</p>';
    } catch (PDOException $e) {
        // Mostrar un mensaje de error al usuario
        echo '<p>Ha ocurrido un error al asignar la nota: ' . $e->getMessage() . '</p>';
    }
} else {
    // Mostrar un mensaje de error al usuario
    echo '<p>Los datos son inválidos</p>';
}

// Redirigir al usuario a la página de gestión de cursos
redirigir('../public/gestion_cursos.php');

// Incluir el archivo footer.php en views
include '../views/footer.php';
?>