-- Parche SQL para usuarios huérfanos
-- Este script inserta a todos los usuarios que NO están en la tabla usuarios_extensiones
-- dentro de la sede 1 (Formación Permanente).

BEGIN;

INSERT INTO cursos.usuarios_extensiones (id_usuario, id_extension)
SELECT u.id, 1 
FROM cursos.usuarios u
LEFT JOIN cursos.usuarios_extensiones ue ON u.id = ue.id_usuario
WHERE ue.id_usuario IS NULL;

COMMIT;
