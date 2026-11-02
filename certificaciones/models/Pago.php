<?php
// models/Pago.php

class Pago
{
    private $db;

    public function __construct($db)
    {
        // Obtenemos la conexión PDO nativa desde tu clase DB
        $this->db = $db->getConn();
    }

    // ==========================================
    // MÉTODOS PARA CUENTAS BANCARIAS
    // ==========================================

    public function obtenerCuentas()
    {
        $sql = "SELECT c.*, e.nombre_extension 
                FROM cursos.cuentas_bancarias c
                LEFT JOIN cursos.extensiones e ON c.id_extension = e.id_extension 
                ORDER BY c.id_cuenta DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerCuentasActivas($id_extension = null)
    {
        if ($id_extension === null && isset($_SESSION['id_extension'])) {
            $id_extension = (int)$_SESSION['id_extension'];
        }
        if (!empty($id_extension)) {
            $sql = "SELECT * FROM cursos.cuentas_bancarias WHERE activo = true AND id_extension = :id_extension ORDER BY id_cuenta DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id_extension' => (int)$id_extension]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        // En PostgreSQL los booleanos se evalúan directamente
        $sql = "SELECT * FROM cursos.cuentas_bancarias WHERE activo = true ORDER BY id_cuenta DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearCuenta($datos)
    {
        $sql = "INSERT INTO cursos.cuentas_bancarias 
                (banco, titular, cedula_rif, telefono, correo, tipo_cuenta, numero_cuenta, activo, id_extension) 
                VALUES 
                (:banco, :titular, :cedula_rif, :telefono, :correo, :tipo_cuenta, :numero_cuenta, :activo, :id_extension)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'banco' => $datos['banco'],
            'titular' => $datos['titular'],
            'cedula_rif' => $datos['cedula_rif'],
            'telefono' => $datos['telefono'],
            'correo' => $datos['correo'],
            'tipo_cuenta' => $datos['tipo_cuenta'],
            'numero_cuenta' => $datos['numero_cuenta'],
            'activo' => isset($datos['activo']) ? $datos['activo'] : true, // Por defecto true
            'id_extension' => isset($datos['id_extension']) ? $datos['id_extension'] : null
        ]);
    }

    public function actualizarCuenta($datos)
    {
        $sql = "UPDATE cursos.cuentas_bancarias 
                SET banco = :banco, titular = :titular, cedula_rif = :cedula_rif, 
                    telefono = :telefono, correo = :correo, tipo_cuenta = :tipo_cuenta, 
                    numero_cuenta = :numero_cuenta, activo = :activo, id_extension = :id_extension WHERE id_cuenta = :id_cuenta";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'banco' => $datos['banco'],
            'titular' => $datos['titular'],
            'cedula_rif' => $datos['cedula_rif'],
            'telefono' => $datos['telefono'],
            'correo' => $datos['correo'],
            'tipo_cuenta' => $datos['tipo_cuenta'],
            'numero_cuenta' => $datos['numero_cuenta'],
            'activo' => $datos['activo'],
            'id_extension' => isset($datos['id_extension']) ? $datos['id_extension'] : null,
            'id_cuenta' => $datos['id_cuenta']
        ]);
    }

    // ==========================================
    // MÉTODOS PARA COMPROBANTES DE PAGO
    // ==========================================

    private function sanearExtensionComprobantesHuerfanos()
    {
        try {
            $this->db->exec("
                UPDATE cursos.comprobantes_pago cp
                SET id_extension = COALESCE(
                    (SELECT c.id_extension FROM cursos.cursos c WHERE c.id_curso = cp.id_curso),
                    (SELECT cb.id_extension FROM cursos.cuentas_bancarias cb WHERE cb.id_cuenta = cp.id_cuenta_destino)
                )
                WHERE cp.id_extension IS NULL
            ");
        } catch (Exception $e) {
            // Ignorar si no aplica
        }
    }

    public function registrarComprobante($datos)
    {
        // Resolver id_extension garantizando que nunca quede en NULL
        $id_extension = !empty($datos['id_extension']) ? (int)$datos['id_extension'] : null;

        if (empty($id_extension) && !empty($datos['id_curso'])) {
            try {
                $stmtExt = $this->db->prepare("SELECT id_extension FROM cursos.cursos WHERE id_curso = :id_curso LIMIT 1");
                $stmtExt->execute(['id_curso' => $datos['id_curso']]);
                $extVal = $stmtExt->fetchColumn();
                if (!empty($extVal)) {
                    $id_extension = (int)$extVal;
                }
            } catch (Exception $e) {
                // Continuar al siguiente fallback
            }
        }

        if (empty($id_extension) && !empty($datos['id_cuenta_destino'])) {
            try {
                $stmtExtCta = $this->db->prepare("SELECT id_extension FROM cursos.cuentas_bancarias WHERE id_cuenta = :id_cuenta LIMIT 1");
                $stmtExtCta->execute(['id_cuenta' => $datos['id_cuenta_destino']]);
                $extCtaVal = $stmtExtCta->fetchColumn();
                if (!empty($extCtaVal)) {
                    $id_extension = (int)$extCtaVal;
                }
            } catch (Exception $e) {
                // Continuar al siguiente fallback
            }
        }

        if (empty($id_extension)) {
            $id_extension = !empty($_SESSION['id_extension']) ? (int)$_SESSION['id_extension'] : 1;
        }

        $paramsBase = [
            'id_usuario' => $datos['id_usuario'],
            'id_curso' => $datos['id_curso'],
            'id_materia_bimestre' => !empty($datos['id_materia_bimestre']) ? $datos['id_materia_bimestre'] : null,
            'archivo_ruta' => isset($datos['archivo_ruta']) ? $datos['archivo_ruta'] : null,
            'numero_operacion' => isset($datos['numero_operacion']) ? $datos['numero_operacion'] : null,
            'banco_origen' => isset($datos['banco_origen']) ? $datos['banco_origen'] : '',
            'monto' => $datos['monto'],
            'fecha_pago' => $datos['fecha_pago'],
            'moneda' => isset($datos['moneda']) ? $datos['moneda'] : 'Bs'
        ];

        try {
            $sql = "INSERT INTO cursos.comprobantes_pago 
                    (id_usuario, id_curso, id_materia_bimestre, archivo_ruta, numero_operacion, banco_origen, monto, estado, fecha_pago, fecha_subida, moneda, id_extension, id_cuenta_destino) 
                    VALUES 
                    (:id_usuario, :id_curso, :id_materia_bimestre, :archivo_ruta, :numero_operacion, :banco_origen, :monto, 'Pendiente', :fecha_pago, CURRENT_TIMESTAMP, :moneda, :id_extension, :id_cuenta_destino)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(array_merge($paramsBase, [
                'id_extension' => $id_extension,
                'id_cuenta_destino' => !empty($datos['id_cuenta_destino']) ? $datos['id_cuenta_destino'] : null
            ]));
        } catch (Exception $e) {
            // Fallback por si el entorno local no tiene las columnas opcionales
            $sqlFallback = "INSERT INTO cursos.comprobantes_pago 
                            (id_usuario, id_curso, id_materia_bimestre, archivo_ruta, numero_operacion, banco_origen, monto, estado, fecha_pago, fecha_subida, moneda) 
                            VALUES 
                            (:id_usuario, :id_curso, :id_materia_bimestre, :archivo_ruta, :numero_operacion, :banco_origen, :monto, 'Pendiente', :fecha_pago, CURRENT_TIMESTAMP, :moneda)";
            $stmtFallback = $this->db->prepare($sqlFallback);
            return $stmtFallback->execute($paramsBase);
        }
    }

    public function actualizarComprobante($datos)
    {
        $id_curso = isset($datos['id_curso']) ? (int)$datos['id_curso'] : null;
        $id_materia = isset($datos['id_materia']) ? (int)$datos['id_materia'] : null;

        if (!empty($datos['archivo_ruta'])) {
            $sql = "UPDATE cursos.comprobantes_pago 
                    SET numero_operacion = :numero_operacion, 
                        banco_origen = :banco_origen, 
                        monto = :monto, 
                        fecha_pago = :fecha_pago, 
                        archivo_ruta = :archivo_ruta,
                        estado = COALESCE(:estado, estado),
                        moneda = COALESCE(:moneda, moneda),
                        id_curso = :id_curso,
                        id_materia_bimestre = :id_materia
                    WHERE id_comprobante = :id_comprobante";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'numero_operacion' => $datos['numero_operacion'],
                'banco_origen' => $datos['banco_origen'],
                'monto' => $datos['monto'],
                'fecha_pago' => $datos['fecha_pago'],
                'archivo_ruta' => $datos['archivo_ruta'],
                'estado' => isset($datos['estado']) ? $datos['estado'] : null,
                'moneda' => isset($datos['moneda']) ? $datos['moneda'] : null,
                'id_curso' => $id_curso,
                'id_materia' => $id_materia,
                'id_comprobante' => $datos['id_comprobante']
            ]);
        } else {
            $sql = "UPDATE cursos.comprobantes_pago 
                    SET numero_operacion = :numero_operacion, 
                        banco_origen = :banco_origen, 
                        monto = :monto, 
                        fecha_pago = :fecha_pago,
                        estado = COALESCE(:estado, estado),
                        moneda = COALESCE(:moneda, moneda),
                        id_curso = :id_curso,
                        id_materia_bimestre = :id_materia
                    WHERE id_comprobante = :id_comprobante";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'numero_operacion' => $datos['numero_operacion'],
                'banco_origen' => $datos['banco_origen'],
                'monto' => $datos['monto'],
                'fecha_pago' => $datos['fecha_pago'],
                'estado' => isset($datos['estado']) ? $datos['estado'] : null,
                'moneda' => isset($datos['moneda']) ? $datos['moneda'] : null,
                'id_curso' => $id_curso,
                'id_materia' => $id_materia,
                'id_comprobante' => $datos['id_comprobante']
            ]);
        }
    }

    public function obtenerComprobantesPorCurso($id_curso)
    {
        $this->sanearExtensionComprobantesHuerfanos();
        $id_extension = isset($_SESSION['id_extension']) ? (int)$_SESSION['id_extension'] : 1;
        $sql = "SELECT cp.*, u.nombre, u.apellido, u.cedula, c.nombre_curso, m.nombre_materia, admin.nombre as admin_nombre, admin.apellido as admin_apellido
                FROM cursos.comprobantes_pago cp
                JOIN cursos.usuarios u ON cp.id_usuario = u.id
                JOIN cursos.cursos c ON cp.id_curso = c.id_curso
                LEFT JOIN cursos.usuarios admin ON cp.id_admin_gestor = admin.id
                LEFT JOIN cursos.materias_bimestre m ON cp.id_materia_bimestre = m.id_materia_bimestre
                WHERE cp.id_curso = :id_curso
                  AND COALESCE(cp.id_extension, c.id_extension, :id_ext_fallback) = :id_extension
                ORDER BY cp.fecha_subida DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id_curso' => $id_curso,
            'id_ext_fallback' => $id_extension,
            'id_extension' => $id_extension
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerTodosLosComprobantes()
    {
        $this->sanearExtensionComprobantesHuerfanos();
        $id_extension = isset($_SESSION['id_extension']) ? (int)$_SESSION['id_extension'] : 1;
        $sql = "SELECT cp.*, u.nombre, u.apellido, u.cedula, c.nombre_curso, m.nombre_materia, admin.nombre as admin_nombre, admin.apellido as admin_apellido
                FROM cursos.comprobantes_pago cp
                JOIN cursos.usuarios u ON cp.id_usuario = u.id
                JOIN cursos.cursos c ON cp.id_curso = c.id_curso
                LEFT JOIN cursos.usuarios admin ON cp.id_admin_gestor = admin.id
                LEFT JOIN cursos.materias_bimestre m ON cp.id_materia_bimestre = m.id_materia_bimestre
                WHERE COALESCE(cp.id_extension, c.id_extension, :id_ext_fallback) = :id_extension
                ORDER BY cp.fecha_subida DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id_ext_fallback' => $id_extension,
            'id_extension' => $id_extension
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerComprobantesPorUsuario($id_usuario, $id_extension = null)
    {
        $this->sanearExtensionComprobantesHuerfanos();
        if ($id_extension === null && isset($_SESSION['id_extension'])) {
            $id_extension = (int)$_SESSION['id_extension'];
        }
        if (!empty($id_extension)) {
            $sql = "SELECT cp.*, c.nombre_curso, m.nombre_materia, admin.nombre as admin_nombre, admin.apellido as admin_apellido
                    FROM cursos.comprobantes_pago cp
                    JOIN cursos.cursos c ON cp.id_curso = c.id_curso
                    LEFT JOIN cursos.usuarios admin ON cp.id_admin_gestor = admin.id
                    LEFT JOIN cursos.materias_bimestre m ON cp.id_materia_bimestre = m.id_materia_bimestre
                    WHERE cp.id_usuario = :id_usuario
                      AND COALESCE(cp.id_extension, c.id_extension, :id_ext_fallback) = :id_extension
                    ORDER BY cp.fecha_subida DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id_usuario' => $id_usuario,
                'id_ext_fallback' => (int)$id_extension,
                'id_extension' => (int)$id_extension
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $sql = "SELECT cp.*, c.nombre_curso, m.nombre_materia, admin.nombre as admin_nombre, admin.apellido as admin_apellido
                FROM cursos.comprobantes_pago cp
                JOIN cursos.cursos c ON cp.id_curso = c.id_curso
                LEFT JOIN cursos.usuarios admin ON cp.id_admin_gestor = admin.id
                LEFT JOIN cursos.materias_bimestre m ON cp.id_materia_bimestre = m.id_materia_bimestre
                WHERE cp.id_usuario = :id_usuario
                ORDER BY cp.fecha_subida DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_usuario' => $id_usuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizarEstadoComprobante($id_comprobante, $estado, $observacion = null, $id_admin_gestor = null)
    {
        try {
            // 1. Iniciamos una transacción para asegurar que ambos updates se ejecuten juntos
            $this->db->beginTransaction();

            // 2. Obtenemos los datos del comprobante para saber a qué usuario y curso pertenece
            $sqlGet = "SELECT id_usuario, id_curso, id_materia_bimestre FROM cursos.comprobantes_pago WHERE id_comprobante = :id_comprobante";
            $stmtGet = $this->db->prepare($sqlGet);
            $stmtGet->execute(['id_comprobante' => $id_comprobante]);
            $comprobante = $stmtGet->fetch(PDO::FETCH_ASSOC);

            if (!$comprobante) {
                throw new Exception("Comprobante no encontrado.");
            }

            // 3. Actualizamos el estado del comprobante y observación
            $sqlUpdate = "UPDATE cursos.comprobantes_pago SET estado = :estado, observacion = :observacion, id_admin_gestor = :id_admin_gestor, fecha_gestion = NOW() WHERE id_comprobante = :id_comprobante";
            $stmtUpdate = $this->db->prepare($sqlUpdate);
            $stmtUpdate->execute([
                'estado' => $estado,
                'observacion' => $observacion,
                'id_admin_gestor' => $id_admin_gestor,
                'id_comprobante' => $id_comprobante
            ]);

            // 4. Lógica de Certificaciones: Solo aplica si es el diplomado completo y no una materia individual
            if (empty($comprobante['id_materia_bimestre'])) {
                $pagoCompletado = ($estado === 'Comprobado') ? true : false;
                $this->actualizarEstadoCertificacion($comprobante['id_usuario'], $comprobante['id_curso'], $pagoCompletado);
            }

            // 5. Confirmamos la transacción
            $this->db->commit();
            return true;

        } catch (Exception $e) {
            // Loguear el error exacto para saber qué falló en la BD
            error_log("Error al actualizar comprobante (ID: $id_comprobante): " . $e->getMessage());
            
            // Si algo falla, revertimos todo
            $this->db->rollBack();
            return false;
        }
    }

    // ==========================================
    // MÉTODOS DE ELIMINACIÓN Y MANTENIMIENTO
    // ==========================================

    public function obtenerComprobantePorId($id_comprobante)
    {
        $sql = "SELECT * FROM cursos.comprobantes_pago WHERE id_comprobante = :id_comprobante";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_comprobante' => $id_comprobante]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function eliminarComprobante($id_comprobante)
    {
        // 1. Obtener los datos del comprobante primero
        $comprobante = $this->obtenerComprobantePorId($id_comprobante);

        if ($comprobante) {
            // 2. Si el pago estaba 'Comprobado' y era de un curso completo, revertimos el estado de pago en certificaciones
            if ($comprobante['estado'] === 'Comprobado' && empty($comprobante['id_materia_bimestre'])) {
                $this->actualizarEstadoCertificacion($comprobante['id_usuario'], $comprobante['id_curso'], false);
            }

            // 3. Eliminar físicamente de la base de datos
            $sql = "DELETE FROM cursos.comprobantes_pago WHERE id_comprobante = :id_comprobante";
            $stmt = $this->db->prepare($sql);
            $borradoDB = $stmt->execute(['id_comprobante' => $id_comprobante]);

            // 4. Eliminar archivo físico si existe
            if ($borradoDB && !empty($comprobante['archivo_ruta'])) {
                $rutaFisica = '../public/' . $comprobante['archivo_ruta'];
                if (file_exists($rutaFisica)) {
                    unlink($rutaFisica);
                }
            }

            return $borradoDB;
        }
        return false;
    }

    public function vaciarComprobantes()
    {
        try {
            // Eliminar solo las referencias a archivos en todos los registros
            $sql = "UPDATE cursos.comprobantes_pago SET archivo_ruta = NULL";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            // Borrar archivos físicos del directorio
            $directorio = '../public/assets/comprobantes/';
            $archivos = glob($directorio . '*'); // Obtener todos los archivos

            foreach ($archivos as $archivo) {
                if (is_file($archivo)) {
                    unlink($archivo); // Eliminar el archivo
                }
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // ==========================================
    // MÉTODOS PRIVADOS AUXILIARES
    // ==========================================

    private function actualizarEstadoCertificacion($id_usuario, $id_curso, $estadoPago)
    {
        // Nota: Asumimos que la columna es curso_id según tu contexto
        $sql = "UPDATE cursos.certificaciones 
                SET pago = :pago 
                WHERE id_usuario = :id_usuario AND curso_id = :curso_id";

        $stmt = $this->db->prepare($sql);

        // PDO::PARAM_BOOL es vital en PostgreSQL para insertar true/false correctamente
        $stmt->bindValue(':pago', $estadoPago, PDO::PARAM_BOOL);
        $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->bindValue(':curso_id', $id_curso, PDO::PARAM_INT);

        $stmt->execute();
    }
}
?>
