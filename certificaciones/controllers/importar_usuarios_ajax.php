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

        $header = fgetcsv($handle, 1000, $delimiter); // Skip header
        while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
            $cedula = trim($data[0] ?? '');
            $nombre = trim($data[1] ?? '');
            $apellido = trim($data[2] ?? '');
            $correo = trim($data[3] ?? '');

            if (empty($cedula) || empty($nombre) || empty($apellido)) {
                continue; // Skip invalid bare-minimum blank rows
            }

            // Generate clean names
            $firstName = utf8_encode(explode(' ', $nombre)[0]);
            $firstSurname = utf8_encode(explode(' ', $apellido)[0]);

            $cleanFirstName = strtolower(removeAccents($firstName));
            $cleanFirstSurname = strtolower(removeAccents($firstSurname));

            // Generate defaults if missing
            $generatedPassword = $cleanFirstName . $cleanFirstSurname . '20';
            if (empty($correo)) {
                $correo = $generatedPassword . '@gmail.com';
            }

            $userArray = [
                'cedula' => htmlspecialchars($cedula),
                'nombre' => htmlspecialchars(utf8_encode($nombre)),
                'apellido' => htmlspecialchars(utf8_encode($apellido)),
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