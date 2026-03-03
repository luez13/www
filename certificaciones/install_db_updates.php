<?php
require_once __DIR__ . '/config/model.php';

try {
    $db = new DB();
    $pdo = $db->getConn();

    // 1. Añadir columna observacion
    $pdo->exec("ALTER TABLE cursos.comprobantes_pago ADD COLUMN IF NOT EXISTS observacion TEXT;");
    echo "Columna observacion añadida correctamente.\n";

    // 2. Añadir columna id_materia_bimestre
    $pdo->exec("ALTER TABLE cursos.comprobantes_pago ADD COLUMN IF NOT EXISTS id_materia_bimestre INTEGER;");

    // Check if foreign key exists, if not, add it
    $stmt = $pdo->query("SELECT 1 FROM pg_constraint WHERE conname = 'fk_comprobante_materia'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE cursos.comprobantes_pago ADD CONSTRAINT fk_comprobante_materia FOREIGN KEY (id_materia_bimestre) REFERENCES cursos.materias_bimestre(id_materia_bimestre) ON DELETE SET NULL;");
        echo "Foreign key fk_comprobante_materia añadida.\n";
    }

    // 3. Insertar 6ta firma
    $pdo->exec("INSERT INTO cursos.posiciones_firma (codigo_posicion, descripcion_posicion, pagina) VALUES ('P2_INF_CEN', 'Página 2, Inferior Centro', 2) ON CONFLICT (codigo_posicion) DO NOTHING;");
    echo "Firma P2_INF_CEN insertada.\n";

    echo "¡Todas las migraciones ejecutadas con éxito!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
