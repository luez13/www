<?php
require_once __DIR__ . '/config/model.php';
$db = new DB();
$pdo = $db->getConn();

$sql = "
ALTER TABLE cursos.auditoria ADD COLUMN IF NOT EXISTS justificacion TEXT;

ALTER TABLE cursos.actas_cierre ADD COLUMN IF NOT EXISTS tipo_acta VARCHAR(50) DEFAULT 'Regular', ADD COLUMN IF NOT EXISTS fecha_generacion DATE DEFAULT CURRENT_DATE;

CREATE TABLE IF NOT EXISTS cursos.usuario_materias (
    id_usuario_materia SERIAL PRIMARY KEY,
    id_usuario INTEGER NOT NULL REFERENCES cursos.usuarios(id) ON DELETE RESTRICT,
    id_materia_bimestre INTEGER NOT NULL REFERENCES cursos.materias_bimestre(id_materia_bimestre) ON DELETE RESTRICT,
    nota_regular NUMERIC(4,2),
    nota_recuperativa NUMERIC(4,2) DEFAULT NULL,
    estado VARCHAR(50) NOT NULL DEFAULT 'Reprobado',
    UNIQUE (id_usuario, id_materia_bimestre)
);

CREATE TABLE IF NOT EXISTS cursos.certificaciones_materias (
    id_cert_materia SERIAL PRIMARY KEY,
    id_usuario INTEGER NOT NULL REFERENCES cursos.usuarios(id) ON DELETE RESTRICT,
    id_materia_bimestre INTEGER NOT NULL REFERENCES cursos.materias_bimestre(id_materia_bimestre) ON DELETE RESTRICT,
    valor_unico VARCHAR(255) UNIQUE NOT NULL,
    fecha_emision TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
    tomo INTEGER,
    folio INTEGER
);
";

try {
    $pdo->exec($sql);
    echo "SQL ejecutado con éxito localmente.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
