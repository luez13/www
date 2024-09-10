<?php
// Incluir el archivo model.php en config
include '../config/model.php';

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

// Obtener los datos del formulario
$id_usuario = $_POST['id_usuario'];
$id_curso = $_POST['id_curso'];
$nota = $_POST['nota'];

// Validar los datos
if (validar_datos($id_usuario, $id_curso, $nota)) {
    // Actualizar la base de datos con la nota
    try {
        $stmt = $db->prepare('UPDATE cursos.certificaciones SET nota = :nota WHERE id_usuario = :id_usuario AND curso_id = :curso_id');
        $stmt->execute(['nota' => $nota, 'id_usuario' => $id_usuario, 'curso_id' => $id_curso]);
        // Devolver una respuesta JSON de éxito
        echo json_encode(['status' => 'success', 'message' => 'La nota se ha asignado correctamente']);
    } catch (PDOException $e) {
        // Devolver una respuesta JSON de error
        echo json_encode(['status' => 'error', 'message' => 'Ha ocurrido un error al asignar la nota: ' . $e->getMessage()]);
    }
} else {
    // Devolver una respuesta JSON de error
    echo json_encode(['status' => 'error', 'message' => 'Los datos son inválidos']);
}
?>