<?php
// controllers/importar_inscripciones_ajax.php
include 'init.php';
include '../config/model.php';

// 1. Seguridad: Roles autorizados
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['id_rol'], [3, 4, 1])) {
    http_response_code(403);
    die(json_encode(['error' => 'No autorizado']));
}

// 2. Seguridad: Validación CSRF
if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    die(json_encode(['error' => 'Error de seguridad (CSRF). Intente recargar la página.']));
}

$db = new DB();
$pdo = $db->getConn();

// Funciones de utilidad para limpieza
function removeAccents($string)
{
    $table = [
        'Š' => 'S', 'š' => 's', 'Đ' => 'Dj', 'đ' => 'dj', 'Ž' => 'Z', 'ž' => 'z', 'Č' => 'C', 'č' => 'c', 'Ć' => 'C', 'ć' => 'c',
        'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
        'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O',
        'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss',
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e',
        'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o',
        'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y',
        'R' => 'R', 'r' => 'r'
    ];
    return strtr($string, $table);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["csv_file"]) && isset($_POST["curso_id"])) {
    $file = $_FILES["csv_file"]["tmp_name"];
    $id_curso = (int)$_POST["curso_id"];
    
    $stats = [
        'procesados' => 0,
        'nuevos_inscritos' => 0,
        'ya_existentes' => 0,
        'errores' => [],
        'count_errores' => 0
    ];

    if (($handle = fopen($file, "r")) !== FALSE) {
        // Detectar delimitador (punto y coma o coma)
        $first_line = fgets($handle);
        $delimiter = (strpos($first_line, ';') !== false) ? ';' : ',';
        rewind($handle);

        $header = fgetcsv($handle, 1000, $delimiter);
        if (!$header) {
            die(json_encode(["error" => "Error leyendo la cabecera del CSV"]));
        }

        // Mapeo dinámico de columnas
        $col_cedula = -1; $col_nombre = -1; $col_apellido = -1; $col_correo = -1;
        $clean_header = array_map(function ($val) {
            return strtolower(preg_replace('/[^a-zA-Z]/', '', removeAccents($val)));
        }, $header);

        foreach ($clean_header as $index => $colName) {
            if (strpos($colName, 'ced') !== false || strpos($colName, 'ci') !== false || strpos($colName, 'ide') !== false) $col_cedula = $index;
            elseif (strpos($colName, 'nom') !== false) $col_nombre = $index;
            elseif (strpos($colName, 'ape') !== false) $col_apellido = $index;
            elseif (strpos($colName, 'cor') !== false || strpos($colName, 'mail') !== false) $col_correo = $index;
        }

        if ($col_cedula === -1) {
            die(json_encode(["error" => "No se encontró la columna de Cédula/Identificación."]));
        }

        // Procesar filas
        while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
            $stats['procesados']++;
            
            $raw_cedula = isset($data[$col_cedula]) ? trim($data[$col_cedula]) : '';
            if (empty($raw_cedula)) continue;

            $cedula = preg_replace('/[^0-9]/', '', $raw_cedula);
            $nombre_raw = ($col_nombre !== -1 && isset($data[$col_nombre])) ? trim($data[$col_nombre]) : '';
            $apellido_raw = ($col_apellido !== -1 && isset($data[$col_apellido])) ? trim($data[$col_apellido]) : '';
            $correo_raw = ($col_correo !== -1 && isset($data[$col_correo])) ? trim($data[$col_correo]) : '';

            // --- INICIA TRANSACCIÓN POR FILA ---
            $pdo->beginTransaction();
            try {
                // 1. Verificar si el usuario existe
                $stmt = $pdo->prepare("SELECT id FROM cursos.usuarios WHERE cedula = ?");
                $stmt->execute([$cedula]);
                $user = $stmt->fetch();

                if (!$user) {
                    // Si no existe, hay que crearlo. Requiere nombre y apellido.
                    if (empty($nombre_raw) || empty($apellido_raw)) {
                        throw new Exception("Usuario no existe y los datos del CSV están incompletos (Nombre/Apellido faltante).");
                    }

                    // Normalizar nombres para email fallback
                    $cleanNombre = strtolower(removeAccents(explode(' ', $nombre_raw)[0]));
                    $cleanApellido = strtolower(removeAccents(explode(' ', $apellido_raw)[0]));
                    
                    $email = !empty($correo_raw) ? strtolower($correo_raw) : ($cleanNombre . $cleanApellido . "20@gmail.com");
                    $password = password_hash($cleanNombre . $cleanApellido . "20", PASSWORD_DEFAULT);
                    $token = md5($email . time());

                    $insUser = $pdo->prepare("INSERT INTO cursos.usuarios (nombre, apellido, cedula, correo, password, token, confirmado, id_rol) VALUES (?, ?, ?, ?, ?, ?, true, 2) RETURNING id");
                    $insUser->execute([
                        mb_convert_case($nombre_raw, MB_CASE_TITLE, "UTF-8"),
                        mb_convert_case($apellido_raw, MB_CASE_TITLE, "UTF-8"),
                        $cedula, $email, $password, $token
                    ]);
                    $id_usuario = $insUser->fetchColumn();
                } else {
                    $id_usuario = $user['id'];
                }

                // 2. Verificar si ya está inscrito
                $stmtIns = $pdo->prepare("SELECT id_usuario FROM cursos.certificaciones WHERE id_usuario = ? AND curso_id = ?");
                $stmtIns->execute([$id_usuario, $id_curso]);
                if ($stmtIns->fetch()) {
                    $stats['ya_existentes']++;
                    $pdo->rollBack(); // No es error, pero no insertamos nada nuevo
                    continue;
                }

                // 3. Inscribir
                $valor_unico = hash('sha256', $id_usuario . $id_curso . microtime());
                $insCert = $pdo->prepare("INSERT INTO cursos.certificaciones (id_usuario, curso_id, valor_unico, fecha_inscripcion, completado) VALUES (?, ?, ?, NOW(), false)");
                $insCert->execute([$id_usuario, $id_curso, $valor_unico]);

                // Todo bien
                $pdo->commit();
                $stats['nuevos_inscritos']++;

            } catch (Exception $e) {
                $pdo->rollBack();
                $stats['count_errores']++;
                $stats['errores'][] = "Fila {$stats['procesados']} (CI $cedula): " . $e->getMessage();
            }
        }
        fclose($handle);
    }
    echo json_encode($stats);
} else {
    echo json_encode(['error' => 'Solicitud incompleta']);
}
