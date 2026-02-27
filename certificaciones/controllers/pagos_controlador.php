<?php
// controllers/pagos_controlador.php

// 1. Iniciar sesión e incluir dependencias
include 'init.php'; // Asegúrate de que este archivo tiene session_start()
require_once '../config/model.php';
require_once '../models/Pago.php';

// Configurar la cabecera para devolver siempre JSON
header('Content-Type: application/json; charset=utf-8');

// 2. Verificar autenticación básica
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado. Usuario no autenticado.']);
    exit;
}

// 3. Instanciar conexión y modelo
$db = new DB();
$pagoModel = new Pago($db);

// 4. Capturar la acción
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// 5. Procesar las peticiones
switch ($action) {

    case 'subir_comprobante':
        // Validar que se hayan enviado los datos requeridos
        $requeridos = ['id_curso', 'numero_operacion', 'banco_origen', 'monto', 'fecha_pago'];
        foreach ($requeridos as $campo) {
            if (empty($_POST[$campo])) {
                echo json_encode(['success' => false, 'message' => "El campo $campo es obligatorio."]);
                exit;
            }
        }

        // Validar el archivo
        if (!isset($_FILES['comprobante_archivo']) || $_FILES['comprobante_archivo']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Error al subir el archivo o no se seleccionó ninguno.']);
            exit;
        }

        $archivo = $_FILES['comprobante_archivo'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $permitidas = ['pdf', 'jpg', 'jpeg', 'png'];

        if (!in_array($extension, $permitidas)) {
            echo json_encode(['success' => false, 'message' => 'Formato de archivo no permitido. Usa PDF, JPG, JPEG o PNG.']);
            exit;
        }

        // Preparar directorio de subida
        $directorioDestino = '../public/assets/comprobantes/';
        if (!file_exists($directorioDestino)) {
            mkdir($directorioDestino, 0777, true);
        }

        // Generar nombre único: idUsuario_idCurso_timestamp.ext
        $id_usuario = $_SESSION['user_id'];
        $id_curso = $_POST['id_curso'];
        $nombreUnico = $id_usuario . '_' . $id_curso . '_' . time() . '.' . $extension;
        $rutaFisica = $directorioDestino . $nombreUnico;

        // La ruta que guardaremos en BD (relativa a public)
        $rutaBD = 'assets/comprobantes/' . $nombreUnico;

        if (move_uploaded_file($archivo['tmp_name'], $rutaFisica)) {
            // Archivo guardado físicamente, procedemos a registrar en BD
            $datosPago = [
                'id_usuario' => $id_usuario,
                'id_curso' => $id_curso,
                'archivo_ruta' => $rutaBD,
                'numero_operacion' => trim($_POST['numero_operacion']),
                'banco_origen' => trim($_POST['banco_origen']),
                'monto' => floatval($_POST['monto']),
                'fecha_pago' => $_POST['fecha_pago']
            ];

            if ($pagoModel->registrarComprobante($datosPago)) {
                echo json_encode(['success' => true, 'message' => 'Comprobante subido y registrado exitosamente.']);
            }
            else {
                // Si falla la BD, es buena práctica borrar el archivo huérfano
                unlink($rutaFisica);
                echo json_encode(['success' => false, 'message' => 'Error al guardar el registro en la base de datos.']);
            }
        }
        else {
            echo json_encode(['success' => false, 'message' => 'Error al mover el archivo al servidor.']);
        }
        break;

    case 'actualizar_estado_comprobante':
        // Validar roles (1 o 2)
        if (!isset($_SESSION['id_rol']) || !in_array($_SESSION['id_rol'], [3, 4])) {
            echo json_encode(['success' => false, 'message' => 'No tienes permisos para realizar esta acción.']);
            exit;
        }

        if (empty($_POST['id_comprobante']) || empty($_POST['estado'])) {
            echo json_encode(['success' => false, 'message' => 'Faltan parámetros obligatorios.']);
            exit;
        }

        $id_comprobante = $_POST['id_comprobante'];
        $estado = $_POST['estado'];
        $estadosPermitidos = ['Pendiente', 'Comprobado', 'Rechazado'];

        if (!in_array($estado, $estadosPermitidos)) {
            echo json_encode(['success' => false, 'message' => 'Estado no válido.']);
            exit;
        }

        if ($pagoModel->actualizarEstadoComprobante($id_comprobante, $estado)) {
            echo json_encode(['success' => true, 'message' => "El estado del comprobante cambió a $estado."]);
        }
        else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado en la base de datos.']);
        }
        break;

    case 'crear_cuenta':
    case 'actualizar_cuenta':
        // Validar roles (1 o 2) para gestionar cuentas bancarias
        if (!isset($_SESSION['id_rol']) || !in_array($_SESSION['id_rol'], [3, 4])) {
            echo json_encode(['success' => false, 'message' => 'No tienes permisos administrativos para gestionar cuentas.']);
            exit;
        }

        // Recopilar datos comunes
        $datosCuenta = [
            'banco' => $_POST['banco'] ?? '',
            'titular' => $_POST['titular'] ?? '',
            'cedula_rif' => $_POST['cedula_rif'] ?? '',
            'telefono' => $_POST['telefono'] ?? '',
            'correo' => $_POST['correo'] ?? '',
            'tipo_cuenta' => $_POST['tipo_cuenta'] ?? '',
            'numero_cuenta' => $_POST['numero_cuenta'] ?? '',
            // Los checkboxes HTML no envían nada si no están marcados
            'activo' => isset($_POST['activo']) ? true : false
        ];

        // Validar únicamente los campos estrictamente necesarios
        $camposObligatorios = ['banco', 'titular', 'cedula_rif'];
        foreach ($camposObligatorios as $campo) {
            if (empty($datosCuenta[$campo])) {
                echo json_encode(['success' => false, 'message' => "El campo $campo es obligatorio."]);
                exit;
            }
        }

        if ($action === 'crear_cuenta') {
            if ($pagoModel->crearCuenta($datosCuenta)) {
                echo json_encode(['success' => true, 'message' => 'Cuenta bancaria creada exitosamente.']);
            }
            else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar la nueva cuenta bancaria.']);
            }
        }
        else {
            // actualizar_cuenta
            if (empty($_POST['id_cuenta'])) {
                echo json_encode(['success' => false, 'message' => 'El ID de la cuenta es obligatorio para actualizar.']);
                exit;
            }
            $datosCuenta['id_cuenta'] = $_POST['id_cuenta'];

            if ($pagoModel->actualizarCuenta($datosCuenta)) {
                echo json_encode(['success' => true, 'message' => 'Cuenta bancaria actualizada correctamente.']);
            }
            else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar la cuenta bancaria.']);
            }
        }
        break;

    default:
        // Acción no reconocida
        echo json_encode(['success' => false, 'message' => 'Acción no válida o no especificada.']);
        break;
}
?>