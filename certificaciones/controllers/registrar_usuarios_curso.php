<?php
// Incluir el archivo model.php en config
include '../config/model.php';

// Crear una instancia de la clase DB
$db = new DB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarios = $_POST['usuarios'];
    $curso_id = $_POST['curso_id'];

    foreach ($usuarios as $usuario_id) {
        // Verificar si el usuario ya está inscrito en el curso
        $stmt = $db->prepare('SELECT * FROM cursos.certificaciones WHERE curso_id = :curso_id AND id_usuario = :id_usuario');
        $stmt->bindParam(':curso_id', $curso_id);
        $stmt->bindParam(':id_usuario', $usuario_id);
        $stmt->execute();
        $inscripcion = $stmt->fetch();

        if (!$inscripcion) {
            // Insertar el registro del usuario en el curso
            $stmt = $db->prepare('INSERT INTO cursos.certificaciones (curso_id, id_usuario) VALUES (:curso_id, :id_usuario)');
            $stmt->bindParam(':curso_id', $curso_id);
            $stmt->bindParam(':id_usuario', $usuario_id);
            $stmt->execute();
        }
    }

    echo 'Usuarios registrados correctamente en el curso.';
} else {
    echo 'Solicitud inválida.';
}
?>