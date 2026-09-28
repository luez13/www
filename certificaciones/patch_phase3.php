<?php
$file = 'controllers/pagos_controlador.php';
$c = file_get_contents($file);

$search = 'case \'obtener_materias\':
        if (empty($_POST[\'id_curso\']) && empty($_GET[\'id_curso\'])) {
            echo json_encode([\'success\' => false, \'message\' => \'Falta el id del curso.\']);
            exit;
        }
        $id_curso = isset($_POST[\'id_curso\']) ? $_POST[\'id_curso\'] : (isset($_GET[\'id_curso\']) ? $_GET[\'id_curso\'] : null);
        require_once \'../models/Materia.php\';
        $materiaModel = new Materia($db);
        $materias = $materiaModel->getMateriasByCurso($id_curso);
        echo json_encode([\'success\' => true, \'data\' => $materias]);
        break;';

$replace = 'case \'obtener_materias\':
        if (empty($_POST[\'id_curso\']) && empty($_GET[\'id_curso\'])) {
            echo json_encode([\'success\' => false, \'message\' => \'Falta el id del curso.\']);
            exit;
        }
        $id_curso = isset($_POST[\'id_curso\']) ? $_POST[\'id_curso\'] : (isset($_GET[\'id_curso\']) ? $_GET[\'id_curso\'] : null);
        require_once \'../models/Materia.php\';
        require_once \'../models/curso.php\';
        
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $materiaModel = new Materia($db);
        $cursoModel = new Curso($db);
        $materias = $materiaModel->getMateriasByCurso($id_curso);
        $curso_detalle = $cursoModel->obtener_curso($id_curso);
        
        $is_pnfa = (isset($curso_detalle[\'tipo_curso\']) && $curso_detalle[\'tipo_curso\'] === \'PNFA\');
        $estado_pnfa = \'libre\'; 
        $mensaje_bloqueo = \'\';
        
        if ($is_pnfa && !empty($materias)) {
            $user_id = $_SESSION[\'user_id\'];
            $pdo = $db->getConn();
            $stmt_pagos = $pdo->prepare("SELECT id_materia_bimestre, estado FROM cursos.comprobantes_pago WHERE id_usuario = ? AND id_curso = ?");
            $stmt_pagos->execute([$user_id, $id_curso]);
            $pagos = $stmt_pagos->fetchAll(PDO::FETCH_ASSOC);
            
            $pagos_por_materia = [];
            foreach ($pagos as $p) {
                $id_mat = $p[\'id_materia_bimestre\'];
                if (!isset($pagos_por_materia[$id_mat])) $pagos_por_materia[$id_mat] = [];
                $pagos_por_materia[$id_mat][] = $p[\'estado\'];
            }
            
            $termino_a_pagar = null;
            
            foreach ($materias as $m) {
                if (stripos($m[\'nombre_materia\'], \'otro\') !== false || stripos($m[\'nombre_materia\'], \'repitencia\') !== false) {
                    continue; 
                }
                
                $id_mat = $m[\'id_materia_bimestre\'];
                $estados_pago = isset($pagos_por_materia[$id_mat]) ? $pagos_por_materia[$id_mat] : [];
                
                $aprobado = false;
                $pendiente = false;
                
                foreach ($estados_pago as $ep) {
                    if ($ep === \'Comprobado\') $aprobado = true;
                    if ($ep === \'Pendiente\') $pendiente = true;
                }
                
                if ($aprobado) {
                    continue; // Skip, already approved
                }
                
                if ($pendiente) {
                    $estado_pnfa = \'bloqueado\';
                    $mensaje_bloqueo = \'Tienes un pago en proceso de verificación para el Término \' . $m[\'lapso_academico\'] . \'.\';
                    $termino_a_pagar = null;
                    break;
                }
                
                // If neither approved nor pending (or rejected), this is the one!
                $termino_a_pagar = $m;
                break;
            }
            
            $materias_filtradas = [];
            if ($termino_a_pagar) {
                $materias_filtradas[] = $termino_a_pagar;
            }
            foreach ($materias as $m) {
                if (stripos($m[\'nombre_materia\'], \'otro\') !== false || stripos($m[\'nombre_materia\'], \'repitencia\') !== false) {
                    $materias_filtradas[] = $m;
                }
            }
            $materias = $materias_filtradas;
        }
        
        echo json_encode([
            \'success\' => true, 
            \'data\' => $materias,
            \'is_pnfa\' => $is_pnfa,
            \'estado_pnfa\' => $estado_pnfa,
            \'mensaje_bloqueo\' => $mensaje_bloqueo
        ]);
        break;';

if (strpos($c, "case 'obtener_materias':") !== false) {
    // Basic replace logic
    // Using string manipulation to ensure correct replace
    $c = preg_replace('/case \'obtener_materias\':.*break;/sU', $replace, $c);
    file_put_contents($file, $c);
    echo "Patched pagos_controlador.php successfully.\n";
} else {
    echo "Could not find obtener_materias.\n";
}

$file2 = 'views/mis_pagos.php';
$c2 = file_get_contents($file2);

$search2 = 'if (response.success && response.data && response.data.length > 0) {
                    response.data.forEach(function (materia) {
                        var option = document.createElement(\'option\');
                        option.value = materia.id_materia_bimestre;
                        option.text = \'Bimestre \' + materia.lapso_academico + \' - \' + materia.nombre_materia;
                        selectMateria.appendChild(option);
                    });
                    contenedorMaterias.style.display = \'block\';
                }';

$replace2 = 'if (response.success) {
                    if (response.is_pnfa) {
                        selectMateria.innerHTML = \'\';
                        selectMateria.required = true;
                        if (response.estado_pnfa === \'bloqueado\') {
                            selectMateria.innerHTML = \'<option value="">-- Selecciona una opción alterna (Principal Bloqueado) --</option>\';
                            
                            var alertDiv = document.getElementById(\'pnfa_alert\');
                            if (!alertDiv) {
                                alertDiv = document.createElement(\'div\');
                                alertDiv.id = \'pnfa_alert\';
                                alertDiv.className = \'alert alert-warning mt-2\';
                                contenedorMaterias.appendChild(alertDiv);
                            }
                            alertDiv.innerHTML = \'<i class="fas fa-exclamation-triangle"></i> \' + response.mensaje_bloqueo;
                        } else {
                            var existingAlert = document.getElementById(\'pnfa_alert\');
                            if(existingAlert) existingAlert.remove();
                        }
                    } else {
                        var existingAlert = document.getElementById(\'pnfa_alert\');
                        if(existingAlert) existingAlert.remove();
                        selectMateria.required = false;
                    }

                    if (response.data && response.data.length > 0) {
                        response.data.forEach(function (materia) {
                            var option = document.createElement(\'option\');
                            option.value = materia.id_materia_bimestre;
                            
                            if (response.is_pnfa) {
                                if (materia.nombre_materia.toLowerCase().includes(\'otro\') || materia.nombre_materia.toLowerCase().includes(\'repitencia\')) {
                                    option.text = materia.nombre_materia;
                                } else {
                                    option.text = \'Término \' + materia.lapso_academico + \' - \' + materia.nombre_materia;
                                }
                            } else {
                                option.text = \'Bimestre \' + materia.lapso_academico + \' - \' + materia.nombre_materia;
                            }
                            
                            selectMateria.appendChild(option);
                        });
                        contenedorMaterias.style.display = \'block\';
                    }
                }';

if (strpos($c2, "if (response.success && response.data && response.data.length > 0) {") !== false) {
    $c2 = str_replace($search2, $replace2, $c2);
    file_put_contents($file2, $c2);
    echo "Patched mis_pagos.php successfully.\n";
} else {
    echo "Could not find JS block in mis_pagos.php.\n";
}

?>
