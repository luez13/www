<?php
require_once '../controllers/autenticacion.php';
require_once '../config/model.php';
require_once '../models/curso.php';
require_once '../views/header.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
include '../controllers/init.php';

$db = new DB();
$curso = new Curso($db);
?>

    <div class="container-fluid p-0">
        <div class="banner-container">
            <a href="../public/index.php" class="banner-link">
                <img src="../public/assets/img/vector membrete 1-01.png" alt="Banner" class="banner-image img-fluid mx-auto d-block" style="max-height: 200px;">
            </a>
        </div>
    </div>
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

                        // ---------------- NUEVO: MATERIAS DEL CURSO ----------------
                        // Obtener materias aprobadas de ese curso
                        $stmt_mat = $db->prepare('SELECT m.id_materia_bimestre, m.nombre_materia, um.nota_regular, um.nota_recuperativa, um.estado 
                                                  FROM cursos.materias_bimestre m
                                                  JOIN cursos.usuario_materias um ON m.id_materia_bimestre = um.id_materia_bimestre
                                                  JOIN cursos.usuarios u ON um.id_usuario = u.id
                                                  WHERE m.id_curso = :curso_id AND u.cedula = :cedula');
                        $stmt_mat->execute(['curso_id' => $curso_id, 'cedula' => $cedula]);
                        $materias = $stmt_mat->fetchAll(PDO::FETCH_ASSOC);

                        if ($materias) {
                            echo '<div class="card mt-4">';
                            echo '<div class="card-body">';
                            echo '<h4 class="mb-3">Módulos / Unidades Curriculares</h4>';
                            echo '<div class="table-responsive"><table class="table table-bordered table-striped">';
                            echo '<thead><tr><th>Materia</th><th>Nota Final</th><th>Estado</th><th>Certificado Individual</th></tr></thead><tbody>';
                            
                            foreach ($materias as $mat) {
                                $nota_final = max((float)$mat['nota_regular'], (float)$mat['nota_recuperativa']);
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($mat['nombre_materia']) . '</td>';
                                echo '<td>' . number_format($nota_final, 2) . '</td>';
                                echo '<td>' . htmlspecialchars($mat['estado']) . '</td>';
                                
                                // Solo mostrar botón si está Aprobado
                                if (strpos(strtolower($mat['estado']), 'aprobado') !== false) {
                                    echo '<td><a href="../controllers/generar_certificado_materia.php?id_materia=' . $mat['id_materia_bimestre'] . '&cedula=' . $cedula . '" target="_blank" class="btn btn-success btn-sm"><i class="fas fa-certificate"></i> Descargar Certificado</a></td>';
                                } else {
                                    echo '<td><span class="badge bg-secondary">No Disponible</span></td>';
                                }
                                echo '</tr>';
                            }
                            echo '</tbody></table></div>';
                            echo '</div></div>';
                        }
                        // -----------------------------------------------------------

                    } else {
                        echo '<div class="alert alert-warning mt-4">No se encontraron detalles para este curso.</div>';
                    }
                }
                ?>
            </div>
        </div>
    </div>