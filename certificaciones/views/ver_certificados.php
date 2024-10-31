<?php
require_once '../controllers/autenticacion.php';
require_once '../config/model.php';
require_once '../models/curso.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
include '../views/header.php';

$db = new DB();
$curso = new Curso($db);
?>

    <div class="container mt-5">
        <div class="card">
            <div class="card-body">
                <h3>Ingresa tu cédula para ver los cursos finalizados</h3>
                <form method="GET" action="">
                    <div class="mb-3">
                        <label for="cedula" class="form-label">Cédula</label>
                        <input type="text" class="form-control form-input border border-dark" id="cedula" name="cedula" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Ver Cursos</button>
                </form>
                <?php
                if (isset($_GET['cedula'])) {
                    $cedula = $_GET['cedula'];
                    $stmt = $db->prepare('SELECT c.*, ce.valor_unico, u.nombre FROM cursos.cursos c
                                           JOIN cursos.certificaciones ce ON c.id_curso = ce.curso_id
                                           JOIN cursos.usuarios u ON ce.id_usuario = u.id
                                           WHERE u.cedula = :cedula AND ce.completado = true');
                    $stmt->execute(['cedula' => $cedula]);
                    $cursos_finalizados = $stmt->fetchAll();
                    if ($cursos_finalizados) {
                        echo '<h3 class="mt-4">Cursos que has finalizado</h3>';
                        echo '<div class="dropdown">';
                        echo '<button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">';
                        echo 'Selecciona un curso';
                        echo '</button>';
                        echo '<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                        foreach ($cursos_finalizados as $curso) {
                            echo '<li><a class="dropdown-item" href="ver_certificados.php?cedula=' . $cedula . '&curso_id=' . $curso['id_curso'] . '">' . $curso['nombre_curso'] . '</a></li>';
                        }
                        echo '</ul>';
                        echo '</div>';
                    } else {
                        echo '<div class="alert alert-warning mt-4">No se encontraron cursos finalizados para esta cédula.</div>';
                    }
                }
                if (isset($_GET['curso_id'])) {
                    $curso_id = $_GET['curso_id'];
                    $stmt = $db->prepare('SELECT c.*, ce.*, u.nombre FROM cursos.cursos c
                                           JOIN cursos.certificaciones ce ON c.id_curso = ce.curso_id
                                           JOIN cursos.usuarios u ON ce.id_usuario = u.id
                                           WHERE c.id_curso = :curso_id AND u.cedula = :cedula');
                    $stmt->execute(['curso_id' => $curso_id, 'cedula' => $cedula]);
                    $curso_seleccionado = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($curso_seleccionado) {
                        echo '<div class="card mt-4">';
                        echo '<div class="card-body">';
                        echo '<h3>Detalles del Curso</h3>';
                        echo '<p>Nombre del Estudiante: ' . $curso_seleccionado['nombre'] . '</p>';
                        echo '<p>Nombre del Curso: ' . $curso_seleccionado['nombre_curso'] . '</p>';
                        echo '<p>Tipo de Curso: ' . $curso_seleccionado['tipo_curso'] . '</p>';
                        echo '<p>Fecha de Inscripción: ' . date('d/m/Y', strtotime($curso_seleccionado['fecha_inscripcion'])) . '</p>';
                        echo '<p>Estado: ' . ($curso_seleccionado['completado'] ? "Aprobado" : "No Aprobado") . '</p>';
                        echo '<p>Valor Único: ' . $curso_seleccionado['valor_unico'] . '</p>';
                        echo '</div>';
                        echo '</div>';
                    } else {
                        echo '<div class="alert alert-warning mt-4">No se encontraron detalles para este curso.</div>';
                    }
                }
                ?>
            </div>
        </div>
    </div>