<?php
// controllers/importar_usuarios_ajax.php
include 'init.php';
include '../config/model.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['id_rol'], [3, 4, 1])) {
    http_response_code(403);
    die('No autorizado');
}

$db = new DB();

// Clean string functions
function removeAccents($string)
{
    $table = array(
        'Š' => 'S',
        'š' => 's',
        'Đ' => 'Dj',
        'đ' => 'dj',
        'Ž' => 'Z',
        'ž' => 'z',
        'Č' => 'C',
        'č' => 'c',
        'Ć' => 'C',
        'ć' => 'c',
        'À' => 'A',
        'Á' => 'A',
        'Â' => 'A',
        'Ã' => 'A',
        'Ä' => 'A',
        'Å' => 'A',
        'Æ' => 'A',
        'Ç' => 'C',
        'È' => 'E',
        'É' => 'E',
        'Ê' => 'E',
        'Ë' => 'E',
        'Ì' => 'I',
        'Í' => 'I',
        'Î' => 'I',
        'Ï' => 'I',
        'Ñ' => 'N',
        'Ò' => 'O',
        'Ó' => 'O',
        'Ô' => 'O',
        'Õ' => 'O',
        'Ö' => 'O',
        'Ø' => 'O',
        'Ù' => 'U',
        'Ú' => 'U',
        'Û' => 'U',
        'Ü' => 'U',
        'Ý' => 'Y',
        'Þ' => 'B',
        'ß' => 'Ss',
        'à' => 'a',
        'á' => 'a',
        'â' => 'a',
        'ã' => 'a',
        'ä' => 'a',
        'å' => 'a',
        'æ' => 'a',
        'ç' => 'c',
        'è' => 'e',
        'é' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'ì' => 'i',
        'í' => 'i',
        'î' => 'i',
        'ï' => 'i',
        'ð' => 'o',
        'ñ' => 'n',
        'ò' => 'o',
        'ó' => 'o',
        'ô' => 'o',
        'õ' => 'o',
        'ö' => 'o',
        'ø' => 'o',
        'ù' => 'u',
        'ú' => 'u',
        'û' => 'u',
        'ý' => 'y',
        'þ' => 'b',
        'ÿ' => 'y',
        'R' => 'R',
        'r' => 'r',
    );
    return strtr($string, $table);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["csv_file"])) {
    $file = $_FILES["csv_file"]["tmp_name"];
    $results = [];

    // Pre-cache existing cedulas and correos for fast validation
    $stmt = $db->prepare('SELECT cedula, correo FROM cursos.usuarios');
    $stmt->execute();
    $existingUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $existingCedulas = array_column($existingUsers, 'cedula');
    $existingCorreos = array_column($existingUsers, 'correo'); // Remove empties

    if (($handle = fopen($file, "r")) !== FALSE) {
        $first_line = fgets($handle);
        $delimiter = strpos($first_line, ';') !== false ? ';' : ',';
        rewind($handle);

        $header = fgetcsv($handle, 1000, $delimiter); // Lee cabecera
        if (!$header) {
            die("Error leyendo cabecera del CSV");
        }

        // --- MAPEO DINÁMICO DE COLUMNAS ---
        $col_cedula = -1;
        $col_nombre = -1;
        $col_apellido = -1;
        $col_correo = -1;

        // Limpiar cabeceras para matcheo fácil
        $clean_header = array_map(function ($val) {
            return strtolower(preg_replace('/[^a-zA-Z]/', '', removeAccents($val)));
        }, $header);

        foreach ($clean_header as $index => $colName) {
            if (strpos($colName, 'ced') !== false || strpos($colName, 'ide') !== false || strpos($colName, 'ci') !== false) {
                $col_cedula = $index;
            } elseif (strpos($colName, 'nom') !== false) {
                $col_nombre = $index;
            } elseif (strpos($colName, 'ape') !== false) {
                $col_apellido = $index;
            } elseif (strpos($colName, 'cor') !== false || strpos($colName, 'mail') !== false) {
                $col_correo = $index;
            }
        }

        // Si no detectó nombre ni apellido, aborta
        if ($col_nombre === -1 || $col_apellido === -1 || $col_cedula === -1) {
            die(json_encode(["error" => "No se pudieron identificar las columnas requeridas (Nombres, Apellidos, Cédula). Cabeceras detectadas: " . implode(", ", $header)]));
        }

        while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
            // Leer usando mapeo dinámico
            $raw_cedula = isset($data[$col_cedula]) ? trim($data[$col_cedula]) : '';
            $raw_nombre = isset($data[$col_nombre]) ? trim($data[$col_nombre]) : '';
            $raw_apellido = isset($data[$col_apellido]) ? trim($data[$col_apellido]) : '';
            $raw_correo = ($col_correo !== -1 && isset($data[$col_correo])) ? trim($data[$col_correo]) : '';

            if (empty($raw_cedula) || empty($raw_nombre) || empty($raw_apellido)) {
                continue; // Skip invalid bare-minimum blank rows
            }

            // Asegurar UTF-8 sin doble-codificar si el archivo ya viene en UTF-8 moderno (CSV UTF-8 de Excel)
            $raw_nombre = mb_check_encoding($raw_nombre, 'UTF-8') ? $raw_nombre : utf8_encode($raw_nombre);
            $raw_apellido = mb_check_encoding($raw_apellido, 'UTF-8') ? $raw_apellido : utf8_encode($raw_apellido);

            // LIMPIEZA EXTREMA
            $cedula = preg_replace('/[^0-9]/', '', $raw_cedula); // Solo números, quita V-, E-, puntos
            $nombre = preg_replace('/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/u', '', $raw_nombre);
            $apellido = preg_replace('/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/u', '', $raw_apellido);
            $correo = strtolower(str_replace(' ', '', $raw_correo)); // Sin espacios minúscula

            // Generate defaults if missing
            $firstName = explode(' ', trim($nombre))[0];
            $firstSurname = explode(' ', trim($apellido))[0];
            $cleanFirstName = strtolower(removeAccents($firstName));
            $cleanFirstSurname = strtolower(removeAccents($firstSurname));

            $generatedPassword = $cleanFirstName . $cleanFirstSurname . '20';
            if (empty($correo)) {
                $correo = $generatedPassword . '@gmail.com';
            }

            $userArray = [
                'cedula' => htmlspecialchars($cedula),
                'nombre' => htmlspecialchars(trim($nombre)),
                'apellido' => htmlspecialchars(trim($apellido)),
                'correo' => htmlspecialchars($correo),
                'password' => $generatedPassword,
                'valido' => true,
                'error' => ''
            ];

            // Validations
            if (in_array($cedula, $existingCedulas)) {
                $userArray['valido'] = false;
                $userArray['error'] = 'Cédula ya está registrada.';
            } else if (!empty($correo) && in_array($correo, $existingCorreos)) {
                $userArray['valido'] = false;
                $userArray['error'] = 'Correo electrónico ya está registrado.';
            }

            $results[] = $userArray;
        }
        fclose($handle);
    }

    echo json_encode($results);
}
?>