<?php
// Incluir el archivo model.php en config
include '../config/model.php';
// Incluir el helper de correos
include '../config/mailer.php';

// Crear una instancia de la clase DB
$db = new DB();

function limpiar_telefono($telefono) {
    if (empty($telefono)) return null;
    $has_plus = (strpos(trim($telefono), '+') === 0);
    $cleaned = preg_replace("/[^0-9]/", "", $telefono);
    return $has_plus ? '+' . $cleaned : $cleaned;
}

// Crear una función para validar los datos de registro
function validar_registro($nombre, $apellido, $correo, $password, $confirm_password, $cedula, $telefono)
{
    if (empty($nombre) || empty($apellido) || empty($correo) || empty($password) || empty($confirm_password) || empty($cedula) || empty($telefono)) {
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
function validar_edicion($nombre, $apellido, $correo, $cedula, $telefono)
{
    if (empty($nombre) || empty($apellido) || empty($correo) || empty($cedula) || empty($telefono)) {
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
        // Validación de Tenant (Aislamiento de Sesión)
        // Exceptuamos la página de selección para no crear un bucle infinito
        if (basename($_SERVER['PHP_SELF']) != 'seleccionar_extension.php' && !isset($_SESSION['id_extension'])) {
            header('Location: ../public/seleccionar_extension.php');
            exit();
        }

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
    
    // Construir enlace y mensaje HTML
    $enlace = $base_url . "/public/confirmar.php?correo=$correo&token=$token";
    $mensajeHtml = "<h3>¡Bienvenido, $nombre!</h3>
                <p>Gracias por registrarte en nuestro sistema.</p>
                <p>Para confirmar tu cuenta y activarla, por favor haz clic en el siguiente botón:</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$enlace' style='background-color: #4e73df; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Confirmar mi Cuenta</a>
                </div>
                <p>Si el botón no funciona, copia y pega el siguiente enlace en tu navegador:</p>
                <p><a href='$enlace'>$enlace</a></p>
                <p>Si no has solicitado este registro, ignora este mensaje.</p>";
                
    // Usar el helper global
    enviarCorreo($correo, $asunto, $mensajeHtml);
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
    $acciones_protegidas = ['login', 'registro', 'editar_perfil', 'recuperar', 'reset', 'asignar_sede_inicial'];
    if (in_array($action, $acciones_protegidas)) {
        if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['auth_error'] = "Error de seguridad CSRF. Petición bloqueada.";
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }

    // Ejecutar la acción correspondiente
    switch ($action) {
        case 'asignar_sede_inicial':
            if (!isset($_SESSION['user_id'])) {
                header('Location: ../public/index.php');
                exit;
            }
            $id_extension = (int) $_POST['id_extension'];
            try {
                $stmt = $db->prepare("INSERT INTO cursos.usuarios_extensiones (id_usuario, id_extension) VALUES (:id_user, :id_ext)");
                $stmt->execute(['id_user' => $_SESSION['user_id'], 'id_ext' => $id_extension]);
                
                // Reiniciar el proceso de login simulando que ya se resolvió el acceso
                $_SESSION['id_extension'] = $id_extension;
                
                // Buscar si es academico
                $stmtExt = $db->prepare("SELECT es_academico FROM cursos.extensiones WHERE id_extension = :id");
                $stmtExt->execute(['id' => $id_extension]);
                $val = $stmtExt->fetchColumn();
                $_SESSION['es_academico'] = ($val === true || $val === 1 || $val === '1' || $val === 't' || $val === 'true');
                $_SESSION['es_multisede'] = false;
                
                redirigir_login();
            } catch (PDOException $e) {
                $_SESSION['auth_error'] = "Error al asignar sede: " . $e->getMessage();
                header('Location: ../public/asignacion_inicial.php');
                exit;
            }
            break;
        case 'registro':
            // Obtener los datos del formulario
            $nombre = $_POST['nombre'];
            $apellido = $_POST['apellido'];
            $correo = strtolower($_POST['correo']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            $cedula = $_POST['cedula'];
            $telefono = limpiar_telefono($_POST['telefono']);
            $redirect_id = isset($_POST['redirect_course_id']) ? $_POST['redirect_course_id'] : null;

            if (!empty($redirect_id)) {
                $_SESSION['redirect_to_course'] = $redirect_id;
            }

            // Validar los datos
            $validacion = validar_registro($nombre, $apellido, $correo, $password, $confirm_password, $cedula, $telefono);
            if ($validacion['valid']) {
                // Generar un token de confirmación
                $token = md5($correo . time());
                // Encriptar la contraseña
                $hash = password_hash($password, PASSWORD_DEFAULT);
                // Insertar los datos en la base de datos
                try {
                    $stmt = $db->prepare('INSERT INTO cursos.usuarios (nombre, apellido, correo, password, cedula, telefono, token, confirmado, id_rol) VALUES (:nombre, :apellido, :correo, :password, :cedula, :telefono, :token, true, 1) RETURNING id');
                    $stmt->execute(['nombre' => $nombre, 'apellido' => $apellido, 'correo' => $correo, 'password' => $hash, 'cedula' => $cedula, 'telefono' => $telefono, 'token' => $token]);
                    
                    $nuevo_id_usuario = $stmt->fetchColumn();

                    // ASIGNACIÓN JIT (Just-In-Time) DE DEPENDENCIA (TENANT)
                    // 1. Usar el enviado desde el formulario público si existe (prioridad)
                    // 2. Si vino redirigido por un curso, intentar usar la sede del curso
                    // 3. Fallback a 1 (Formación Permanente)
                    $id_extension_asignar = isset($_POST['id_extension']) ? (int)$_POST['id_extension'] : 1; 
                    
                    if (!empty($redirect_id)) {
                        $stmtExt = $db->prepare("SELECT id_extension FROM cursos.cursos WHERE id_curso = :id_curso");
                        $stmtExt->execute(['id_curso' => $redirect_id]);
                        $ext_curso = $stmtExt->fetchColumn();
                        if ($ext_curso) {
                            $id_extension_asignar = $ext_curso;
                        }
                    }

                    $stmtAsignar = $db->prepare("INSERT INTO cursos.usuarios_extensiones (id_usuario, id_extension) VALUES (:id_user, :id_ext)");
                    $stmtAsignar->execute(['id_user' => $nuevo_id_usuario, 'id_ext' => $id_extension_asignar]);

                    // Enviar un correo de confirmación al usuario
                    enviar_correo_confirmacion($correo, $nombre, $token);
                    // Mostrar un mensaje de éxito al usuario
                    $_SESSION['auth_success'] = "Te has registrado correctamente. Por favor, inicia sesión.";
                    header('Location: ../public/register.php');
                    exit;
                } catch (PDOException $e) {
                    $_SESSION['auth_error'] = "Error de Base de Datos Real: " . $e->getMessage();
                    $_SESSION['form_data'] = ['nombre' => $nombre, 'apellido' => $apellido, 'correo' => $correo, 'cedula' => $cedula, 'telefono' => $_POST['telefono']];
                    header('Location: ../public/register.php');
                    exit;
                }
            } else {
                $_SESSION['auth_error'] = $validacion['message'];
                $_SESSION['form_data'] = ['nombre' => $nombre, 'apellido' => $apellido, 'correo' => $correo, 'cedula' => $cedula, 'telefono' => $_POST['telefono']];
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
                            $_SESSION['telefono'] = isset($user['telefono']) ? $user['telefono'] : '';
                            
                            // Multi-tenant: Comprobar a qué sedes (extensiones) tiene acceso
                            $stmtExt = $db->prepare('
                                SELECT e.id_extension, e.nombre_extension, e.es_academico 
                                FROM cursos.usuarios_extensiones ue 
                                JOIN cursos.extensiones e ON ue.id_extension = e.id_extension 
                                WHERE ue.id_usuario = :id_usuario AND ue.activo = TRUE AND e.activa = TRUE
                            ');
                            $stmtExt->execute([':id_usuario' => $user['id']]);
                            $extensiones = $stmtExt->fetchAll(PDO::FETCH_ASSOC);

                            if (count($extensiones) == 0) {
                                // Caso A: Cero Accesos (Usuario Huérfano)
                                // Redirigir al lobby de asignación inicial
                                header('Location: ../public/asignacion_inicial.php');
                                exit;
                            } else if (count($extensiones) == 1) {
                                // Caso B: Acceso Único
                                $_SESSION['id_extension'] = $extensiones[0]['id_extension'];
                                $val = $extensiones[0]['es_academico'];
                                $_SESSION['es_academico'] = ($val === true || $val === 1 || $val === '1' || $val === 't' || $val === 'true');
                                $_SESSION['es_multisede'] = false;
                                redirigir_login();
                            } else {
                                // Caso C: Multi-Acceso
                                // Redirigir al Gateway (sin ejecutar redirigir_login)
                                $_SESSION['es_multisede'] = true;
                                header('Location: ../public/seleccionar_extension.php');
                                exit;
                            }
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
            $telefono = limpiar_telefono($_POST['telefono']);
            $nuevaContrasena = $_POST['nueva_contrasena']; // Nuevo campo para la nueva contraseña

            // Validar los datos
            if (validar_edicion($nombre, $apellido, $correo, $cedula, $telefono)) {
                // Obtener el id del usuario de la sesión
                $user_id = $_SESSION['user_id'];

                // Actualizar los datos en la base de datos
                try {
                    $stmt = $db->prepare('UPDATE cursos.usuarios SET nombre = :nombre, apellido = :apellido, correo = :correo, cedula = :cedula, telefono = :telefono WHERE id = :id');
                    $stmt->execute(['nombre' => $nombre, 'apellido' => $apellido, 'correo' => $correo, 'cedula' => $cedula, 'telefono' => $telefono, 'id' => $user_id]);

                    // Si se proporcionó una nueva contraseña, actualizarla también
                    if (!empty($nuevaContrasena)) {
                        $hashNuevaContrasena = password_hash($nuevaContrasena, PASSWORD_DEFAULT);
                        $stmt = $db->prepare('UPDATE cursos.usuarios SET password = :hash WHERE id = :id');
                        $stmt->execute(['hash' => $hashNuevaContrasena, 'id' => $user_id]);
                    }

                    // Actualizar variables de sesión
                    $_SESSION['nombre'] = $nombre;
                    $_SESSION['apellido'] = $apellido;
                    $_SESSION['correo'] = $correo;
                    $_SESSION['cedula'] = $cedula;
                    $_SESSION['telefono'] = $telefono;

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
                        $mensajeHtml = "<h3>Hola " . htmlspecialchars($user['nombre']) . "</h3>
                                    <p>Hemos recibido una solicitud para restablecer tu contraseña.</p>
                                    <div style='text-align: center; margin: 30px 0;'>
                                        <a href='$link' style='background-color: #4e73df; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Restablecer Contraseña</a>
                                    </div>
                                    <p>Si el botón no funciona, copia y pega el siguiente enlace en tu navegador:</p>
                                    <p><a href='$link'>$link</a></p>
                                    <p>Si no realizaste esta solicitud, puedes ignorar este correo de forma segura.</p>";
                        
                        enviarCorreo($correo, $asunto, $mensajeHtml);

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