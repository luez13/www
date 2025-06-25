<?php
// Incluir el archivo model.php en config
include '../config/model.php';

// Crear una instancia de la clase DB
$db = new DB();

// Crear una función para validar los datos de registro
function validar_registro($nombre, $apellido, $correo, $password, $confirm_password, $cedula) {
    if (empty($nombre) || empty($apellido) || empty($correo) || empty($password) || empty($confirm_password) || empty($cedula)) {
        return false;
    }
    
    // Permitir caracteres latinos y espacios en nombre y apellido
    if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/u", $nombre) || !preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/u", $apellido)) {
        return false;
    }
    
    // Validación de correo electrónico mejorada
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        // Si la validación estándar falla, usamos una expresión regular más permisiva
        $email_regex = '/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ.!#$%&\'*+-\/=?^_`{|}~]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/';
        if (!preg_match($email_regex, $correo)) {
            return false;
        }
    }
    
    // Permitir caracteres alfanuméricos y guiones en la cédula
    if (!preg_match("/^[a-zA-Z0-9-]+$/", $cedula)) {
        return false;
    }
    
    if ($password !== $confirm_password) {
        return false;
    }
    
    // Validación de contraseña (mínimo 8 caracteres)
    if (strlen($password) < 8) {
        return false;
    }
    
    return true;
}

// Crear una función para validar los datos de inicio de sesión
function validar_login($correo, $password) {
    if (empty($correo) || empty($password)) {
        return false;
    }
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    return true;
}

// Crear una función para validar los datos de edición
function validar_edicion($nombre, $apellido, $correo, $cedula) {
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
function redirigir_login() {
    // Usar la función header para enviar el encabezado de redirección
    header('Location: ../public/perfil.php');
    // Terminar la ejecución del script
    exit();
}

// Crear una función para redirigir al usuario a la página de perfil
function redirigir_perfilUsuario() {
    // Usar la función header para enviar el encabezado de redirección
    header('Location: ../public/perfil.php');
    // Terminar la ejecución del script
    exit();
}

// Crear una función para generar una contraseña segura
function generar_password($longitud) {
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
function verificar_password($password, $hash) {
    // Usar la función password_verify de PHP para comparar la contraseña con el hash
    return password_verify($password, $hash);
}

function verificar_sesion() {
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
function enviar_correo_confirmacion($correo, $nombre, $token) {
    // Definir el asunto del correo
    $asunto = 'Confirmación de registro';
    // Definir el mensaje del correo
    $mensaje = "Hola, $nombre. Gracias por registrarte en nuestro sistema. Para confirmar tu cuenta, haz clic en el siguiente enlace:\n";
    $mensaje .= "http://localhost/public/confirmar.php?correo=$correo&token=$token\n";
    $mensaje .= "Si no has solicitado este registro, ignora este mensaje.\n";
    $mensaje .= "Saludos, el equipo de gestión de cursos y certificaciones.";
    // Definir las cabeceras del correo
    $cabeceras = "From: no-reply@gestioncursos.com\r\n";
    $cabeceras .= "Reply-To: no-reply@gestioncursos.com\r\n";
    $cabeceras .= "X-Mailer: PHP/" . phpversion();
    // Usar la función mail de PHP para enviar el correo
    mail($correo, $asunto, $mensaje, $cabeceras);
}

function esPerfil4($id_usuario) {
    $db = new DB();
    $stmt = $db->prepare('SELECT id_rol FROM cursos.usuarios WHERE id = :id_usuario');
    $stmt->execute([':id_usuario' => $id_usuario]);
    $rol = $stmt->fetch(PDO::FETCH_ASSOC);
    return $rol['id_rol'] == 4;
}

function esPerfil3($id_usuario) {
    $db = new DB();
    $stmt = $db->prepare('SELECT id_rol FROM cursos.usuarios WHERE id = :id_usuario');
    $stmt->execute([':id_usuario' => $id_usuario]);
    $rol = $stmt->fetch(PDO::FETCH_ASSOC);
    return $rol['id_rol'] == 3;
}

function esPerfil2($id_usuario) {
    $db = new DB();
    $stmt = $db->prepare('SELECT id_rol FROM cursos.usuarios WHERE id = :id_usuario');
    $stmt->execute([':id_usuario' => $id_usuario]);
    $rol = $stmt->fetch(PDO::FETCH_ASSOC);
    return $rol['id_rol'] == 2;
}

function esPerfil1($id_usuario) {
    $db = new DB();
    $stmt = $db->prepare('SELECT id_rol FROM cursos.usuarios WHERE id = :id_usuario');
    $stmt->execute([':id_usuario' => $id_usuario]);
    $rol = $stmt->fetch(PDO::FETCH_ASSOC);
    return $rol['id_rol'] == 1;
}

// Comprobar si la variable $_POST['action'] está definida
if (isset($_POST['action'])) {
    // Asignar el valor de la variable a una variable local
    $action = $_POST['action'];
    // Ejecutar el código según el valor de la variable

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
        
        // Validar los datos
        if (validar_registro($nombre, $apellido, $correo, $password, $confirm_password, $cedula)) {
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
                echo '<script>alert("Te has registrado correctamente, inicia sesión"); window.location.href = "../public/index.php";</script>';
            } catch (PDOException $e) {
                // Mostrar un mensaje de error al usuario
                echo '<p>Ha ocurrido un error al registrarte: ' . $e->getMessage() . '</p>';
            }
        } else {
            // Mostrar un mensaje de error al usuario
            echo '<p>Los datos de registro son inválidos</p>';
        }
        break;    
        case 'login':
            // Obtener los datos del formulario
            $correo = strtolower($_POST['correo']);
            $password = $_POST['password'];
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
                            // Iniciar la sesión y guardar el id y el rol del usuario
                            session_start();
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['user_rol'] = $user['id_rol'];
                            $_SESSION['nombre'] = $user['nombre']; // Guardar el nombre del usuario
                            $_SESSION['apellido'] = $user['apellido'];
                            $_SESSION['correo'] = $user['correo'];
                            $_SESSION['cedula'] = $user['cedula'];
                            // Redirigir al usuario a la página de perfil
                            redirigir_login();
                        } else {
                            // Mostrar un mensaje de error al usuario
                            echo '<script>alert("La contraseña es incorrecta"); window.location.href = "../public/index.php";</script>';
                        }
                    } else {
                        // Mostrar un mensaje de error al usuario
                        echo '<script>alert("El usuario no existe o no está confirmado"); window.location.href = "../public/index.php";</script>';
                    }
                } catch (PDOException $e) {
                    // Mostrar un mensaje de error al usuario
                    echo '<script>alert("Ha ocurrido un error al iniciar sesión: ' . $e->getMessage() . '"); window.location.href = "../public/index.php";</script>';
                }
            } else {
                // Mostrar un mensaje de error al usuario
                echo '<script>alert("Los datos de inicio de sesión son inválidos"); window.location.href = "../public/index.php";</script>';
            }
            break;        
    case 'editar_perfil':
        session_start();
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
        session_start();
        session_unset();
        session_destroy();
        // Redirigir al usuario a la página de inicio de sesión
        redirigir_login();
        break;
    case 'recuperar':
        // Obtener el correo del formulario
        $correo = strtolower($_POST['correo']);
        // Verificar que el correo no esté vacío
        if (!empty($correo)) {
            // Consultar la base de datos para obtener el usuario con el correo ingresado
            try {
                $stmt = $db->prepare('SELECT * FROM cursos.usuarios WHERE correo = :correo');
                $stmt->execute(['correo' => $correo]);
                $user = $stmt->fetch();
                // Verificar si el usuario existe y está confirmado
                if ($user && $user['confirmado']) {
                    // Generar una nueva contraseña
                    $password = generar_password(8);
                    // Encriptar la contraseña
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    // Actualizar la contraseña en la base de datos
                    $stmt = $db->prepare('UPDATE cursos.usuarios SET password = :password WHERE correo = :correo');
                    $stmt->execute(['password' => $hash, 'correo' => $correo]);
                    // Enviar un correo de recuperación al usuario
                    //enviar_correo_recuperacion($correo, $user['nombre'], $password);
                    // Mostrar un mensaje de éxito al usuario
                    echo '<p>Te hemos enviado un correo electrónico con tu nueva contraseña. Por favor, revisa tu bandeja de entrada.</p>';
                } else {
                    // Mostrar un mensaje de error al usuario
                    echo '<p>El usuario no existe o no está confirmado</p>';
                }
            } catch (PDOException $e) {
                // Mostrar un mensaje de error al usuario
                echo '<p>Ha ocurrido un error al recuperar tu contraseña: ' . $e->getMessage() . '</p>';
            }
        } else {
            // Mostrar un mensaje de error al usuario
            echo '<p>Debes ingresar tu correo electrónico</p>';
        }
        break;
}
}
?>