<?php
session_start();
require_once __DIR__ . '/../config/model.php';
require_once __DIR__ . '/../models/Plantilla.php';

// Validar que el usuario sea administrador
if (!isset($_SESSION['user_rol']) || (int)$_SESSION['user_rol'] !== 4) {
    header("Location: ../public/index.php");
    exit();
}
$redireccion = "Location: ../public/perfil.php?modulo=plantillas";

$db = new DB();
$plantillaModel = new Plantilla($db);

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

switch ($action) {
    case 'crear':
        $nombre = trim($_POST['nombre']);
        $archivo_vista = trim($_POST['archivo_vista']);
        $es_defecto = isset($_POST['es_defecto']) ? true : false;

        if (empty($nombre) || empty($archivo_vista)) {
            $_SESSION['error_plantilla'] = "El nombre y el archivo de vista son obligatorios.";
            header($redireccion);
            exit();
        }

        if ($plantillaModel->crear($nombre, $archivo_vista, $es_defecto)) {
            $_SESSION['mensaje_plantilla'] = "Plantilla creada exitosamente.";
        } else {
            $_SESSION['error_plantilla'] = "Error al crear la plantilla.";
        }
        header($redireccion);
        break;

    case 'actualizar':
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        $nombre = trim($_POST['nombre']);
        $archivo_vista = trim($_POST['archivo_vista']);
        $es_defecto = isset($_POST['es_defecto']) ? true : false;

        if (!$id || empty($nombre) || empty($archivo_vista)) {
            $_SESSION['error_plantilla'] = "Datos incompletos para actualizar.";
            header($redireccion);
            exit();
        }

        if ($plantillaModel->actualizar($id, $nombre, $archivo_vista, $es_defecto)) {
            $_SESSION['mensaje_plantilla'] = "Plantilla actualizada exitosamente.";
        } else {
            $_SESSION['error_plantilla'] = "Error al actualizar la plantilla.";
        }
        header($redireccion);
        break;

    case 'eliminar':
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        if (!$id) {
            $_SESSION['error_plantilla'] = "ID inválido.";
            header($redireccion);
            exit();
        }

        // Evitar eliminar la que es por defecto como medida de seguridad opcional (?)
        $actual = $plantillaModel->obtenerPorId($id);
        if ($actual && $actual['es_defecto']) {
            $_SESSION['error_plantilla'] = "No puedes eliminar la plantilla por defecto. Cambia otra a por defecto primero.";
        } else {
            if ($plantillaModel->eliminar($id)) {
                $_SESSION['mensaje_plantilla'] = "Plantilla eliminada exitosamente.";
            } else {
                $_SESSION['error_plantilla'] = "Error al eliminar. Posiblemente está en uso por algún curso.";
            }
        }
        header($redireccion);
        break;

    case 'hacer_defecto':
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        if ($id) {
            if ($plantillaModel->hacerDefecto($id)) {
                $_SESSION['mensaje_plantilla'] = "Plantilla establecida por defecto.";
            } else {
                $_SESSION['error_plantilla'] = "Error al cambiar plantilla por defecto.";
            }
        }
        header($redireccion);
        break;

    default:
        header($redireccion);
        break;
}
?>
