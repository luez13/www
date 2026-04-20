<?php
// Incluir el archivo model.php en config
include '../config/model.php';

// Crear una instancia de la clase DB
$db = new DB();

// Crear una función para validar los datos de registro
function validar_registro($nombre, $apellido, $correo, $password, $confirm_password, $cedula)
{
    if (empty($nombre) || empty($apellido) || empty($correo) || empty($password) || empty($confirm_password) || empty($cedula)) {
        return ['valid' => false, 'message' => 'Todos los campos son obligatorios.'];
    }

    // Permitir caracteres latinos y espacios en nombre y apellido
    if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/u", $nombre) || !preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/u", $apellido)) {
        return ['valid' => false, 'message' => 'El nombre y apellido solo deben contener letras y espacios.'];
    }

    // Validación de correo electrónico mejorada
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        // Si la validación estándar falla, usamos una expresión regular más permisiva
        $email_regex = '/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ.!#$%&\'*+-\/=?^_`{|}~]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/';
        if (!preg_match($email_regex, $correo)) {
            return ['valid' => false, 'message' => 'El formato del correo electrónico es inválido.'];
        }
    }

    // Permitir caracteres alfanuméricos y guiones en la cédula
    if (!preg_match("/^[a-zA-Z0-9-]+$/", $cedula)) {
        return ['valid' => false, 'message' => 'La cédula solo debe contener números y guiones.'];
    }

    if ($password !== $confirm_password) {
        return ['valid' => false, 'message' => 'Las contraseñas no coinciden.'];
    }

    // Validación de contraseña (mínimo 8 caracteres)
    if (strlen($password) < 8) {
        return ['valid' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres.'];
    }

    return ['valid' => true, 'message' => ''];
}

// Crear una función para validar los datos de inicio de sesión
function validar_login($correo, $password)
{
    if (empty($correo) || empty($password)) {
        return false;
    }
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    return true;
}

// Crear una función para validar los datos de edición
function validar_edicion($nombre, $apellido, $correo, $cedula)
{
    if (empty($nombre) || empty($apellido) || empty($correo) || empty($cedula)) {
        return false;
    }
    // Permitir caracteres latinos y espacios en nombre y apellido
    if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/u", $nombre) || !preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/u", $apellido)) {
        return false;
    }
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    // Permitir caracteres no numéricos en la cédula (por ejemplo, para extranjeros)
    if (!preg_match("/^[a-zA-Z0-9-]+$/", $cedula)) {
        return false;
    }
    return true;
}

// Crear una función para redirigir al usuario a la página de inicio de sesión
function redirigir_login()
{
    // Verificar si hay una redirección pendiente a un curso específico
    if (isset($_SESSION['redirect_to_course']) && !empty($_SESSION['redirect_to_course'])) {
        $course_id = $_SESSION['redirect_to_course'];
        // Limpiamos la variable de sesión INMEDIATAMENTE para evitar el "bucle fantasma"
        unset($_SESSION['redirect_to_course']);
        header('Location: ../public/perfil.php?enroll=' . $course_id);
    } else {
        header('Location: ../public/perfil.php');
    }
    exit();
}

// Crear una función para redirigir al usuario a la página de perfil
function redirigir_perfilUsuario()
{
    // Usar la función header para enviar el encabezado de redirección
    header('Location: ../public/perfil.php');
    // Terminar la ejecución del script
    exit();
}

// Crear una función para generar una contraseña segura
function generar_password($longitud)
{
    // Definir los caracteres que se pueden usar en la contraseña
    $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    // Obtener la longitud de la cadena de caracteres
    $max = strlen($caracteres) - 1;
    // Crear una variable para guardar la contraseña
    $password = '';
    // Recorrer la longitud deseada
    for ($i = 0; $i < $longitud; $i++) {
        // Elegir un caracter aleatorio de la cadena
        $password .= $caracteres[rand(0, $max)];
    }
    // Devolver la contraseña generada
    return $password;
}

// Crear una función para verificar una contraseña
function verificar_password($password, $hash)
{
    // Usar la función password_verify de PHP para comparar la contraseña con el hash
    return password_verify($password, $hash);
}

function verificar_sesion()
{
    // Verificar si el usuario ha iniciado sesión
    if (!isset($_SESSION['user_id'])) {
        // Establecer un mensaje de error
        $_SESSION['error'] = "No tienes acceso";

        // Redirigir al usuario a la página de inicio de sesión, a menos que ya esté en ella
        if (basename($_SERVER['PHP_SELF']) != 'index.php') {
            header('Location: ../public/index.php');
            exit();
        }
    } else {
        // Obtener el rol del usuario y almacenarlo en la sesión
        $db = new DB();
        $stmt = $db->prepare('SELECT id_rol FROM cursos.usuarios WHERE id = :id_usuario');
        $stmt->execute([':id_usuario' => $_SESSION['user_id']]);
        $rol = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['id_rol'] = $rol['id_rol'];
    }
}

// Crear una función para enviar un correo electrónico de confirmación
function enviar_correo_confirmacion($correo, $nombre, $token)
{
    $protocolo = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $app_path = explode('/controllers/', $_SERVER['PHP_SELF'])[0];
    $base_url = $protocolo . "://" . $host . $app_path;

    // Definir el asunto del correo
    $asunto = 'Confirmación de registro';
    // Definir el mensaje del correo
    $mensaje = "Hola, $nombre. Gracias por registrarte en nuestro sistema. Para confirmar tu cuenta, haz clic en el siguiente enlace:\n";
    $mensaje .= $base_url . "/public/confirmar.php?correo=$correo&token=$token\n";
    $mensaje .= "Si no has solicitado este registro, ignora este mensaje.\n";
    $mensaje .= "Saludos, el equipo de gestión de cursos y certificaciones.";
    // Definir las cabeceras del correo
    $cabeceras = "From: no-reply@gestioncursos.com\r\n";
    $cabeceras .= "Reply-To: no-reply@gestioncursos.com\r\n";
    $cabeceras .= "X-Mailer: PHP/" . phpversion();
    // Usar la función mail de PHP para enviar el correo
    mail($correo, $asunto, $mensaje, $cabeceras);
}

function esPerfil4($id_usuario)
{
    $db = new DB();
    $stmt = $db->prepare('SELECT id_rol FROM cursos.usuarios WHERE id = :id_usuario');
    $stmt->execute([':id_usuario' => $id_usuario]);
    $rol = $stmt->fetch(PDO::FETCH_ASSOC);
    return $rol['id_rol'] == 4;
}

function esPerfil3($id_usuario)
{
    $db = new DB();
    $stmt = $db->prepare('SELECT id_rol FROM cursos.usuarios WHERE id = :id_usuario');
    $stmt->execute([':id_usuario' => $id_usuario]);
    $rol = $stmt->fetch(PDO::FETCH_ASSOC);
    return $rol['id_rol'] == 3;
}

function esPerfil2($id_usuario)
{
    $db = new DB();
    $stmt = $db->prepare('SELECT id_rol FROM cursos.usuarios WHERE id = :id_usuario');
    $stmt->execute([':id_usuario' => $id_usuario]);
    $rol = $stmt->fetch(PDO::FETCH_ASSOC);
    return $rol['id_rol'] == 2;
}

function esPerfil1($id_usuario)
{
    $db = new DB();
    $stmt = $db->prepare('SELECT id_rol FROM cursos.usuarios WHERE id = :id_usuario');
    $stmt->execute([':id_usuario' => $id_usuario]);
    $rol = $stmt->fetch(PDO::FETCH_ASSOC);
    return $rol['id_rol'] == 1;
}

// Comprobar si la variable $_POST['action'] está definida
if (isset($_POST['action'])) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    $action = $_POST['action'];

    // CSRF Protection
    $acciones_protegidas = ['login', 'registro', 'editar_perfil', 'recuperar', 'reset'];
    if (in_array($action, $acciones_protegidas)) {
        if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['auth_error'] = "Error de seguridad CSRF. Petición bloqueada.";
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }

    // Ejecutar la acción correspondiente
    switch ($action) {
        case 'registro':
            // Obtener los datos del formulario
            $nombre = $_POST['nombre'];
            $apellido = $_POST['apellido'];
            $correo = strtolower($_POST['correo']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            $cedula = $_POST['cedula'];
            $redirect_id = isset($_POST['redirect_course_id']) ? $_POST['redirect_course_id'] : null;

            if (!empty($redirect_id)) {
                $_SESSION['redirect_to_course'] = $redirect_id;
            }

            // Validar los datos
            $validacion = validar_registro($nombre, $apellido, $correo, $password, $confirm_password, $cedula);
            if ($validacion['valid']) {
                // Generar un token de confirmación
                $token = md5($correo . time());
                // Encriptar la contraseña
                $hash = password_hash($password, PASSWORD_DEFAULT);
                // Insertar los datos en la base de datos
                try {
                    $stmt = $db->prepare('INSERT INTO cursos.usuarios (nombre, apellido, correo, password, cedula, token, confirmado, id_rol) VALUES (:nombre, :apellido, :correo, :password, :cedula, :token, true, 1)');
                    $stmt->execute(['nombre' => $nombre, 'apellido' => $apellido, 'correo' => $correo, 'password' => $hash, 'cedula' => $cedula, 'token' => $token]);
                    // Enviar un correo de confirmación al usuario
                    enviar_correo_confirmacion($correo, $nombre, $token);
                    // Mostrar un mensaje de éxito al usuario
                    $_SESSION['auth_success'] = "Te has registrado correctamente. Por favor, inicia sesión.";
                    header('Location: ../public/register.php');
                    exit;
                } catch (PDOException $e) {
                    $_SESSION['auth_error'] = "Ha ocurrido un error al registrarte. Es posible que el correo o cédula ya estén registrados.";
                    $_SESSION['form_data'] = ['nombre' => $nombre, 'apellido' => $apellido, 'correo' => $correo, 'cedula' => $cedula];
                    header('Location: ../public/register.php');
                    exit;
                }
            } else {
                $_SESSION['auth_error'] = $validacion['message'];
                $_SESSION['form_data'] = ['nombre' => $nombre, 'apellido' => $apellido, 'correo' => $correo, 'cedula' => $cedula];
                header('Location: ../public/register.php');
                exit;
            }
            break;
        case 'login':
            // Obtener los datos del formulario
            $correo = strtolower($_POST['correo']);
            $password = $_POST['password'];
            $redirect_id = isset($_POST['redirect_course_id']) ? $_POST['redirect_course_id'] : null;

            if (!empty($redirect_id)) {
                $_SESSION['redirect_to_course'] = $redirect_id;
            }

            // Validar los datos
            if (validar_login($correo, $password)) {
                // Consultar la base de datos para obtener el usuario con el correo ingresado
                try {
                    $stmt = $db->prepare('SELECT * FROM cursos.usuarios WHERE correo = :correo');
                    $stmt->execute(['correo' => $correo]);
                    $user = $stmt->fetch();
                    // Verificar si el usuario existe y está confirmado
                    if ($user && $user['confirmado']) {
                        // Verificar si la contraseña es correcta
                        if (verificar_password($password, $user['password'])) {
                            // Guardar datos en la sesión
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['user_rol'] = $user['id_rol'];
                            $_SESSION['nombre'] = $user['nombre']; // Guardar el nombre del usuario
                            $_SESSION['apellido'] = $user['apellido'];
                            $_SESSION['correo'] = $user['correo'];
                            $_SESSION['cedula'] = $user['cedula'];
                            // Redirigir al usuario a la página de perfil
                            redirigir_login();
                        } else {
                            $_SESSION['auth_error'] = "La contraseña es incorrecta.";
                            header('Location: ../public/index.php');
                            exit;
                        }
                    } else {
                        $_SESSION['auth_error'] = "El usuario no existe o no está confirmado.";
                        header('Location: ../public/index.php');
                        exit;
                    }
                } catch (PDOException $e) {
                    $_SESSION['auth_error'] = "Ha ocurrido un error al iniciar sesión: " . $e->getMessage();
                    header('Location: ../public/index.php');
                    exit;
                }
            } else {
                $_SESSION['auth_error'] = "Los datos de inicio de sesión son inválidos. Revisa tu correo o contraseña.";
                header('Location: ../public/index.php');
                exit;
            }
            break;
        case 'editar_perfil':
            // Obtener los datos del formulario
            $nombre = $_POST['nombre'];
            $apellido = $_POST['apellido'];
            $correo = strtolower($_POST['correo']);
            $cedula = $_POST['cedula'];
            $nuevaContrasena = $_POST['nueva_contrasena']; // Nuevo campo para la nueva contraseña

            // Validar los datos
            if (validar_edicion($nombre, $apellido, $correo, $cedula)) {
                // Obtener el id del usuario de la sesión
                $user_id = $_SESSION['user_id'];

                // Actualizar los datos en la base de datos
                try {
                    $stmt = $db->prepare('UPDATE cursos.usuarios SET nombre = :nombre, apellido = :apellido, correo = :correo, cedula = :cedula WHERE id = :id');
                    $stmt->execute(['nombre' => $nombre, 'apellido' => $apellido, 'correo' => $correo, 'cedula' => $cedula, 'id' => $user_id]);

                    // Si se proporcionó una nueva contraseña, actualizarla también
                    if (!empty($nuevaContrasena)) {
                        $hashNuevaContrasena = password_hash($nuevaContrasena, PASSWORD_DEFAULT);
                        $stmt = $db->prepare('UPDATE cursos.usuarios SET password = :hash WHERE id = :id');
                        $stmt->execute(['hash' => $hashNuevaContrasena, 'id' => $user_id]);
                    }

                    // Mostrar un mensaje de éxito al usuario
                    echo '<p>Tus datos se han actualizado correctamente</p>';
                    redirigir_perfilUsuario();
                } catch (PDOException $e) {
                    // Mostrar un mensaje de error al usuario
                    echo '<p>Ha ocurrido un error al actualizar tus datos: ' . $e->getMessage() . '</p>';
                }
            } else {
                // Mostrar un mensaje de error al usuario
                echo '<p>Los datos de edición son inválidos</p>';
            }
            break;
        case 'logout':
            // Cerrar la sesión y destruir los datos
            session_unset();
            session_destroy();
            // Redirigir al usuario a la página de inicio de sesión
            redirigir_login();
            break;
        case 'recuperar':
            // Obtener el correo del formulario
            $correo = strtolower($_POST['correo']);
            if (!empty($correo)) {
                try {
                    $stmt = $db->prepare('SELECT * FROM cursos.usuarios WHERE correo = :correo AND confirmado = true');
                    $stmt->execute(['correo' => $correo]);
                    $user = $stmt->fetch();

                    if ($user) {
                        // Generar token seguro
                        $token = bin2hex(openssl_random_pseudo_bytes(32));

                        // Guardar en BD reutilizando el campo token
                        $stmt = $db->prepare('UPDATE cursos.usuarios SET token = :token WHERE correo = :correo');
                        $stmt->execute(['token' => $token, 'correo' => $correo]);

                        // Construir el enlace dinámico
                        $protocolo = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                        $host = $_SERVER['HTTP_HOST'];
                        $app_path = explode('/controllers/', $_SERVER['PHP_SELF'])[0];
                        $base_url = $protocolo . "://" . $host . $app_path;
                        $link = $base_url . "/public/reset_password.php?token=" . $token;

                        $asunto = 'Recuperar Contraseña';
                        $mensaje = "Hola " . $user['nombre'] . ".\n\nHaz clic aquí para restablecer tu contraseña:\n" . $link;
                        $cabeceras = "From: no-reply@gestioncursos.com\r\nReply-To: no-reply@gestioncursos.com\r\n";
                        @mail($correo, $asunto, $mensaje, $cabeceras);

                        $_SESSION['auth_success'] = "Se ha enviado un enlace de recuperación a tu correo electrónico.";
                        header('Location: ../public/index.php');
                        exit;
                    } else {
                        $_SESSION['auth_error'] = "El correo no está registrado o no está confirmado.";
                        header('Location: ../public/recuperar_password.php');
                        exit;
                    }
                } catch (PDOException $e) {
                    $_SESSION['auth_error'] = "Error de base de datos: " . $e->getMessage();
                    header('Location: ../public/recuperar_password.php');
                    exit;
                }
            } else {
                $_SESSION['auth_error'] = "Debes ingresar tu correo electrónico.";
                header('Location: ../public/recuperar_password.php');
                exit;
            }
            break;

        case 'reset':
            $token = $_POST['token'];
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            if (empty($token) || empty($password) || empty($confirm_password)) {
                $_SESSION['auth_error'] = "Faltan datos por enviar.";
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

            if (strlen($password) < 8) {
                $_SESSION['auth_error'] = "La contraseña debe tener al menos 8 caracteres.";
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

            if ($password !== $confirm_password) {
                $_SESSION['auth_error'] = "Las contraseñas no coinciden.";
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

            try {
                // Verificar que el token exista (sólo cambiaremos la pass al usuario con ese token)
                $stmt = $db->prepare('SELECT id, correo FROM cursos.usuarios WHERE token = :token');
                $stmt->execute(['token' => $token]);
                $user = $stmt->fetch();

                if ($user) {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    // Actualizar clave y rotar el token para que este link expire
                    $nuevo_token_dummy = md5(time() . rand());

                    $stmtUpdate = $db->prepare('UPDATE cursos.usuarios SET password = :password, token = :nuevo_token WHERE id = :id');
                    $stmtUpdate->execute(['password' => $hash, 'nuevo_token' => $nuevo_token_dummy, 'id' => $user['id']]);

                    $_SESSION['auth_success'] = "¡Contraseña actualizada con éxito! Ya puedes iniciar sesión.";
                    header('Location: ../public/index.php');
                    exit;
                } else {
                    $_SESSION['auth_error'] = "El enlace es inválido o ya ha expirado.";
                    header('Location: ../public/index.php');
                    exit;
                }
            } catch (PDOException $e) {
                $_SESSION['auth_error'] = "Error: " . $e->getMessage();
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            break;
    }
}
?>