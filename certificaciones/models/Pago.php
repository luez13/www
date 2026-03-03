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
        $sql = "SELECT * FROM cursos.cuentas_bancarias ORDER BY id_cuenta DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerCuentasActivas()
    {
        // En PostgreSQL los booleanos se evalúan directamente
        $sql = "SELECT * FROM cursos.cuentas_bancarias WHERE activo = true ORDER BY id_cuenta DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearCuenta($datos)
    {
        $sql = "INSERT INTO cursos.cuentas_bancarias 
                (banco, titular, cedula_rif, telefono, correo, tipo_cuenta, numero_cuenta, activo) 
                VALUES 
                (:banco, :titular, :cedula_rif, :telefono, :correo, :tipo_cuenta, :numero_cuenta, :activo)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'banco' => $datos['banco'],
            'titular' => $datos['titular'],
            'cedula_rif' => $datos['cedula_rif'],
            'telefono' => $datos['telefono'],
            'correo' => $datos['correo'],
            'tipo_cuenta' => $datos['tipo_cuenta'],
            'numero_cuenta' => $datos['numero_cuenta'],
            'activo' => $datos['activo'] ?? true // Por defecto true
        ]);
    }

    public function actualizarCuenta($datos)
    {
        $sql = "UPDATE cursos.cuentas_bancarias 
                SET banco = :banco, titular = :titular, cedula_rif = :cedula_rif, 
                    telefono = :telefono, correo = :correo, tipo_cuenta = :tipo_cuenta, 
                    numero_cuenta = :numero_cuenta, activo = :activo 
                WHERE id_cuenta = :id_cuenta";
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
            'id_cuenta' => $datos['id_cuenta']
        ]);
    }

    // ==========================================
    // MÉTODOS PARA COMPROBANTES DE PAGO
    // ==========================================

    public function registrarComprobante($datos)
    {
        $sql = "INSERT INTO cursos.comprobantes_pago 
                (id_usuario, id_curso, id_materia_bimestre, archivo_ruta, numero_operacion, banco_origen, monto, estado, fecha_pago, fecha_subida) 
                VALUES 
                (:id_usuario, :id_curso, :id_materia_bimestre, :archivo_ruta, :numero_operacion, :banco_origen, :monto, 'Pendiente', :fecha_pago, CURRENT_TIMESTAMP)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id_usuario' => $datos['id_usuario'],
            'id_curso' => $datos['id_curso'],
            'id_materia_bimestre' => $datos['id_materia_bimestre'],
            'archivo_ruta' => $datos['archivo_ruta'],
            'numero_operacion' => $datos['numero_operacion'],
            'banco_origen' => $datos['banco_origen'],
            'monto' => $datos['monto'],
            'fecha_pago' => $datos['fecha_pago']
        ]);
    }

    public function actualizarComprobante($datos)
    {
        if (!empty($datos['archivo_ruta'])) {
            $sql = "UPDATE cursos.comprobantes_pago 
                    SET numero_operacion = :numero_operacion, 
                        banco_origen = :banco_origen, 
                        monto = :monto, 
                        fecha_pago = :fecha_pago, 
                        archivo_ruta = :archivo_ruta,
                        estado = COALESCE(:estado, estado) 
                    WHERE id_comprobante = :id_comprobante";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'numero_operacion' => $datos['numero_operacion'],
                'banco_origen' => $datos['banco_origen'],
                'monto' => $datos['monto'],
                'fecha_pago' => $datos['fecha_pago'],
                'archivo_ruta' => $datos['archivo_ruta'],
                'estado' => $datos['estado'] ?? null,
                'id_comprobante' => $datos['id_comprobante']
            ]);
        } else {
            $sql = "UPDATE cursos.comprobantes_pago 
                    SET numero_operacion = :numero_operacion, 
                        banco_origen = :banco_origen, 
                        monto = :monto, 
                        fecha_pago = :fecha_pago,
                        estado = COALESCE(:estado, estado)
                    WHERE id_comprobante = :id_comprobante";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'numero_operacion' => $datos['numero_operacion'],
                'banco_origen' => $datos['banco_origen'],
                'monto' => $datos['monto'],
                'fecha_pago' => $datos['fecha_pago'],
                'estado' => $datos['estado'] ?? null,
                'id_comprobante' => $datos['id_comprobante']
            ]);
        }
    }

    public function obtenerComprobantesPorCurso($id_curso)
    {
        $sql = "SELECT cp.*, u.nombre, u.apellido, u.cedula, c.nombre_curso, m.nombre_materia 
                FROM cursos.comprobantes_pago cp
                JOIN cursos.usuarios u ON cp.id_usuario = u.id
                JOIN cursos.cursos c ON cp.id_curso = c.id_curso
                LEFT JOIN cursos.materias_bimestre m ON cp.id_materia_bimestre = m.id_materia_bimestre
                WHERE cp.id_curso = :id_curso
                ORDER BY cp.fecha_subida DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_curso' => $id_curso]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerTodosLosComprobantes()
    {
        $sql = "SELECT cp.*, u.nombre, u.apellido, u.cedula, c.nombre_curso, m.nombre_materia 
                FROM cursos.comprobantes_pago cp
                JOIN cursos.usuarios u ON cp.id_usuario = u.id
                JOIN cursos.cursos c ON cp.id_curso = c.id_curso
                LEFT JOIN cursos.materias_bimestre m ON cp.id_materia_bimestre = m.id_materia_bimestre
                ORDER BY cp.fecha_subida DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerComprobantesPorUsuario($id_usuario)
    {
        $sql = "SELECT cp.*, c.nombre_curso, m.nombre_materia 
                FROM cursos.comprobantes_pago cp
                JOIN cursos.cursos c ON cp.id_curso = c.id_curso
                LEFT JOIN cursos.materias_bimestre m ON cp.id_materia_bimestre = m.id_materia_bimestre
                WHERE cp.id_usuario = :id_usuario
                ORDER BY cp.fecha_subida DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_usuario' => $id_usuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizarEstadoComprobante($id_comprobante, $estado, $observacion = null)
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
            $sqlUpdate = "UPDATE cursos.comprobantes_pago SET estado = :estado, observacion = :observacion WHERE id_comprobante = :id_comprobante";
            $stmtUpdate = $this->db->prepare($sqlUpdate);
            $stmtUpdate->execute([
                'estado' => $estado,
                'observacion' => $observacion,
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
        // 1. Obtener la ruta del archivo primero para poder borrarlo
        $comprobante = $this->obtenerComprobantePorId($id_comprobante);

        if ($comprobante) {
            // 2. Eliminar de la base de datos lógicamente, poniendo archivo a nulo
            $sql = "UPDATE cursos.comprobantes_pago SET archivo_ruta = NULL WHERE id_comprobante = :id_comprobante";
            $stmt = $this->db->prepare($sql);
            $borradoDB = $stmt->execute(['id_comprobante' => $id_comprobante]);

            // 3. Eliminar archivo físico si existe en DB y en disco
            if ($borradoDB && !empty($comprobante['archivo_ruta'])) {
                $rutaFisica = '../public/' . $comprobante['archivo_ruta'];
                if (file_exists($rutaFisica)) {
                    unlink($rutaFisica);
                }
            }
            // Si el comprobante estaba 'Comprobado', tendríamos que revertir la certificación.
            // Pero como esta acción es de bajo nivel o restringida, dejamos esa lógica al controlador si hiciera falta
            // o asumimos que el admin sabe lo que hace.

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