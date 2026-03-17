<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class Plantilla
{
    private $pdo;

    public function __construct(DB $db_wrapper)
    {
        $this->pdo = $db_wrapper->getConn();
    }

    public function listarTodas()
    {
        $sql = "SELECT id, nombre, archivo_vista, es_defecto FROM cursos.plantillas_certificados ORDER BY id ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT id, nombre, archivo_vista, es_defecto FROM cursos.plantillas_certificados WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($nombre, $archivo_vista, $es_defecto = false)
    {
        $this->pdo->beginTransaction();
        try {
            if ($es_defecto) {
                // Si la nueva es por defecto, quitamos el flag a las demás
                $this->quitarDefectoATodas();
            }

            $sql = "INSERT INTO cursos.plantillas_certificados (nombre, archivo_vista, es_defecto) VALUES (:nombre, :archivo_vista, :es_defecto)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $nombre,
                ':archivo_vista' => $archivo_vista,
                ':es_defecto' => $es_defecto ? 'true' : 'false'
            ]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error al crear plantilla: " . $e->getMessage());
            return false;
        }
    }

    public function actualizar($id, $nombre, $archivo_vista, $es_defecto = false)
    {
        $this->pdo->beginTransaction();
        try {
            if ($es_defecto) {
                // Si se marca como defecto, quitamos el flag a las demás primero
                $this->quitarDefectoATodas();
            }

            $sql = "UPDATE cursos.plantillas_certificados SET nombre = :nombre, archivo_vista = :archivo_vista, es_defecto = :es_defecto WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $nombre,
                ':archivo_vista' => $archivo_vista,
                ':es_defecto' => $es_defecto ? 'true' : 'false',
                ':id' => $id
            ]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error al actualizar plantilla: " . $e->getMessage());
            return false;
        }
    }

    public function eliminar($id)
    {
        try {
            // TODO: Podríamos verificar si está en uso en tablas de cursos antes de borrar
            $sql = "DELETE FROM cursos.plantillas_certificados WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error al eliminar plantilla: " . $e->getMessage());
            return false;
        }
    }

    public function hacerDefecto($id)
    {
        $this->pdo->beginTransaction();
        try {
            $this->quitarDefectoATodas();

            $sql = "UPDATE cursos.plantillas_certificados SET es_defecto = true WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error al establecer plantilla por defecto: " . $e->getMessage());
            return false;
        }
    }

    private function quitarDefectoATodas()
    {
        $sql = "UPDATE cursos.plantillas_certificados SET es_defecto = false";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
    }
}
?>
