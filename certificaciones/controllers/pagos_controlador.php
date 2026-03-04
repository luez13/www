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
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

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
                'id_materia_bimestre' => !empty($_POST['id_materia_bimestre']) ? intval($_POST['id_materia_bimestre']) : null,
                'archivo_ruta' => $rutaBD,
                'numero_operacion' => trim($_POST['numero_operacion']),
                'banco_origen' => trim($_POST['banco_origen']),
                'monto' => floatval($_POST['monto']),
                'fecha_pago' => $_POST['fecha_pago']
            ];

            if ($pagoModel->registrarComprobante($datosPago)) {
                echo json_encode(['success' => true, 'message' => 'Comprobante subido y registrado exitosamente.']);
            } else {
                // Si falla la BD, es buena práctica borrar el archivo huérfano
                unlink($rutaFisica);
                echo json_encode(['success' => false, 'message' => 'Error al guardar el registro en la base de datos.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al mover el archivo al servidor.']);
        }
        break;

    case 'obtener_materias':
        if (empty($_POST['id_curso']) && empty($_GET['id_curso'])) {
            echo json_encode(['success' => false, 'message' => 'Falta el id del curso.']);
            exit;
        }
        $id_curso = isset($_POST['id_curso']) ? $_POST['id_curso'] : (isset($_GET['id_curso']) ? $_GET['id_curso'] : null);
        require_once '../models/Materia.php';
        $materiaModel = new Materia($db);
        $materias = $materiaModel->getMateriasByCurso($id_curso);
        echo json_encode(['success' => true, 'data' => $materias]);
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
        $observacion = !empty($_POST['observacion']) ? trim($_POST['observacion']) : null;
        $estadosPermitidos = ['Pendiente', 'Comprobado', 'Rechazado'];

        if (!in_array($estado, $estadosPermitidos)) {
            echo json_encode(['success' => false, 'message' => 'Estado no válido.']);
            exit;
        }

        if ($pagoModel->actualizarEstadoComprobante($id_comprobante, $estado, $observacion)) {
            echo json_encode(['success' => true, 'message' => "El estado del comprobante cambió a $estado."]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado en la base de datos.']);
        }
        break;

    case 'eliminar_comprobante':
        if (empty($_POST['id_comprobante'])) {
            echo json_encode(['success' => false, 'message' => 'Falta el ID del comprobante.']);
            exit;
        }
        $id_comprobante = $_POST['id_comprobante'];

        $comprobante = $pagoModel->obtenerComprobantePorId($id_comprobante);
        if (!$comprobante) {
            echo json_encode(['success' => false, 'message' => 'El comprobante no existe.']);
            exit;
        }

        $es_admin = isset($_SESSION['id_rol']) && in_array($_SESSION['id_rol'], [3, 4]);
        $es_propietario = $comprobante['id_usuario'] == $_SESSION['user_id'];
        $estado_permitido = in_array($comprobante['estado'], ['Pendiente', 'Rechazado']);

        // Puede eliminar si es Admin, O si es el dueño y el estado lo permite
        if ($es_admin || ($es_propietario && $estado_permitido)) {
            if ($pagoModel->eliminarComprobante($id_comprobante)) {
                echo json_encode(['success' => true, 'message' => 'Comprobante eliminado con éxito.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el comprobante de la base de datos.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No tienes permisos para eliminar este comprobante (está comprobado o no te pertenece).']);
        }
        break;

    case 'editar_comprobante':
        // Validar requeridos básicos
        $requeridos = ['id_comprobante', 'numero_operacion', 'banco_origen', 'monto', 'fecha_pago'];
        foreach ($requeridos as $campo) {
            if (empty($_POST[$campo])) {
                echo json_encode(['success' => false, 'message' => "El campo $campo es obligatorio."]);
                exit;
            }
        }

        $id_comprobante = $_POST['id_comprobante'];
        $comprobante_actual = $pagoModel->obtenerComprobantePorId($id_comprobante);

        if (!$comprobante_actual) {
            echo json_encode(['success' => false, 'message' => 'Comprobante no encontrado.']);
            exit;
        }

        $es_admin = isset($_SESSION['id_rol']) && in_array($_SESSION['id_rol'], [3, 4]);
        $es_propietario = $comprobante_actual['id_usuario'] == $_SESSION['user_id'];
        $estado_permitido = in_array($comprobante_actual['estado'], ['Pendiente', 'Rechazado']);

        // Puede editar si es Admin, O si es el dueño y el estado lo permite
        if (!$es_admin && !($es_propietario && $estado_permitido)) {
            echo json_encode(['success' => false, 'message' => 'No tienes permisos para editar este comprobante.']);
            exit;
        }

        $origen_peticion = isset($_POST['origen']) ? $_POST['origen'] : '';
        $estado_final = ($origen_peticion === 'usuario') ? 'Pendiente' : ($es_admin ? null : 'Pendiente');

        $datosActualizar = [
            'id_comprobante' => $id_comprobante,
            'numero_operacion' => trim($_POST['numero_operacion']),
            'banco_origen' => trim($_POST['banco_origen']),
            'monto' => floatval($_POST['monto']),
            'fecha_pago' => $_POST['fecha_pago'],
            'archivo_ruta' => null,
            'estado' => $estado_final
        ];

        // Manejar subida de archivo opcional
        if (isset($_FILES['comprobante_archivo']) && $_FILES['comprobante_archivo']['error'] === UPLOAD_ERR_OK) {
            $archivo = $_FILES['comprobante_archivo'];
            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            $permitidas = ['pdf', 'jpg', 'jpeg', 'png'];

            if (!in_array($extension, $permitidas)) {
                echo json_encode(['success' => false, 'message' => 'Formato de archivo no permitido. Usa PDF, JPG, JPEG o PNG.']);
                exit;
            }

            $directorioDestino = '../public/assets/comprobantes/';
            if (!file_exists($directorioDestino))
                mkdir($directorioDestino, 0777, true);

            $nombreUnico = $comprobante_actual['id_usuario'] . '_' . $comprobante_actual['id_curso'] . '_' . time() . '.' . $extension;
            $rutaFisica = $directorioDestino . $nombreUnico;

            if (move_uploaded_file($archivo['tmp_name'], $rutaFisica)) {
                $datosActualizar['archivo_ruta'] = 'assets/comprobantes/' . $nombreUnico;
                // Borrar antiguo
                if (!empty($comprobante_actual['archivo_ruta'])) {
                    $rutaAntigua = '../public/' . $comprobante_actual['archivo_ruta'];
                    if (file_exists($rutaAntigua)) {
                        unlink($rutaAntigua);
                    }
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al subir el nuevo comprobante.']);
                exit;
            }
        }

        if ($pagoModel->actualizarComprobante($datosActualizar)) {
            echo json_encode(['success' => true, 'message' => 'Comprobante actualizado correctamente.']);
        } else {
            // Revertir
            if ($datosActualizar['archivo_ruta']) {
                unlink('../public/' . $datosActualizar['archivo_ruta']);
            }
            echo json_encode(['success' => false, 'message' => 'Error al actualizar base de datos.']);
        }
        break;

    case 'backup_comprobantes':
        // Validar que sea admin superior
        if (!isset($_SESSION['id_rol']) || !in_array($_SESSION['id_rol'], [3, 4])) {
            die('Acceso denegado. No eres administrador.');
        }

        $directorio = '../public/assets/comprobantes/';
        $archivo_tar = '../public/assets/backup_comprobantes_' . date('Y-m-d_H-i-s') . '.tar';

        if (!class_exists('PharData')) {
            die('La clase PharData no está habilitada en PHP. Contacta con soporte.');
        }

        try {
            $phar = new PharData($archivo_tar);
            $phar->buildFromDirectory($directorio);

            // Forzar descarga
            if (file_exists($archivo_tar)) {
                header('Content-Type: application/x-tar');
                header('Content-Disposition: attachment; filename="' . basename($archivo_tar) . '"');
                header('Content-Length: ' . filesize($archivo_tar));
                readfile($archivo_tar);
                // Eliminar el archivo temporal
                unlink($archivo_tar);
                exit;
            } else {
                die('Error al generar el archivo TAR.');
            }
        } catch (Exception $e) {
            die('Error creando el backup: ' . $e->getMessage());
        }
        break;

    case 'limpiar_comprobantes':
        // Validar rol de admin
        if (!isset($_SESSION['id_rol']) || !in_array($_SESSION['id_rol'], [3, 4])) {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado. No tienes permisos para vaciar el servidor.']);
            exit;
        }

        if ($pagoModel->vaciarComprobantes()) {
            echo json_encode(['success' => true, 'message' => 'Se han borrado todos los comprobantes físicos y registros.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al vaciar los comprobantes.']);
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
            'banco' => isset($_POST['banco']) ? $_POST['banco'] : '',
            'titular' => isset($_POST['titular']) ? $_POST['titular'] : '',
            'cedula_rif' => isset($_POST['cedula_rif']) ? $_POST['cedula_rif'] : '',
            'telefono' => isset($_POST['telefono']) ? $_POST['telefono'] : '',
            'correo' => isset($_POST['correo']) ? $_POST['correo'] : '',
            'tipo_cuenta' => isset($_POST['tipo_cuenta']) ? $_POST['tipo_cuenta'] : '',
            'numero_cuenta' => isset($_POST['numero_cuenta']) ? $_POST['numero_cuenta'] : '',
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
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar la nueva cuenta bancaria.']);
            }
        } else {
            // actualizar_cuenta
            if (empty($_POST['id_cuenta'])) {
                echo json_encode(['success' => false, 'message' => 'El ID de la cuenta es obligatorio para actualizar.']);
                exit;
            }
            $datosCuenta['id_cuenta'] = $_POST['id_cuenta'];

            if ($pagoModel->actualizarCuenta($datosCuenta)) {
                echo json_encode(['success' => true, 'message' => 'Cuenta bancaria actualizada correctamente.']);
            } else {
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