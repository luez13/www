-- ====================================================================================
-- SCRIPT DE SEMILLA (SEED) PARA DATOS DE PRUEBA
-- Ejecuta esto en pgAdmin para llenar tu base de datos con información aleatoria 
-- orientada a probar los filtros y exportaciones de pagos.
-- ====================================================================================

DO $$
DECLARE
    v_id_usuario INTEGER;
    v_id_curso INTEGER;
    v_estado VARCHAR;
    v_banco VARCHAR;
    v_fecha DATE;
    v_monto NUMERIC;
    i INTEGER;
    bancos TEXT[] := ARRAY['Banesco', 'Mercantil', 'Provincial', 'Venezuela', 'BNC'];
    estados TEXT[] := ARRAY['Pendiente', 'Comprobado', 'Rechazado'];
    nombres TEXT[] := ARRAY['Juan', 'Maria', 'Carlos', 'Ana', 'Luis', 'Pedro', 'Laura', 'Sofia'];
    apellidos TEXT[] := ARRAY['Perez', 'Gomez', 'Rodriguez', 'Lopez', 'Martinez', 'Garcia'];
BEGIN
    -- 1. Crear 5 cursos de prueba
    FOR i IN 1..5 LOOP
        INSERT INTO cursos.cursos (nombre_curso, costo, estado)
        VALUES ('Diplomado de Testing Avanzado ' || i, (random() * 100 + 50)::numeric(10,2), true)
        RETURNING id_curso INTO v_id_curso;
    END LOOP;

    -- 2. Crear 10 usuarios de prueba (Participantes)
    FOR i IN 1..10 LOOP
        INSERT INTO cursos.usuarios (nombre, apellido, correo, cedula, id_rol)
        VALUES (
            nombres[1 + floor(random() * array_length(nombres, 1))],
            apellidos[1 + floor(random() * array_length(apellidos, 1))],
            'prueba' || i || '_' || floor(random()*1000) || '@correo.com',
            'V-' || (floor(random() * 20000000) + 10000000)::text,
            1
        )
        RETURNING id INTO v_id_usuario;
    END LOOP;

    -- 3. Crear 30 comprobantes de pago aleatorios mezclando usuarios y cursos
    FOR i IN 1..30 LOOP
        -- Obtener usuario y curso al azar
        SELECT id INTO v_id_usuario FROM cursos.usuarios ORDER BY random() LIMIT 1;
        SELECT id_curso INTO v_id_curso FROM cursos.cursos ORDER BY random() LIMIT 1;
        
        -- Datos aleatorios para el comprobante
        v_estado := estados[1 + floor(random() * array_length(estados, 1))];
        v_banco := bancos[1 + floor(random() * array_length(bancos, 1))];
        -- Fecha aleatoria dentro de los últimos 60 días
        v_fecha := (CURRENT_DATE - (floor(random() * 60) || ' days')::interval)::date;
        v_monto := (random() * 80 + 20)::numeric(10,2);

        INSERT INTO cursos.comprobantes_pago (
            id_usuario, id_curso, archivo_ruta, numero_operacion, 
            banco_origen, monto, estado, fecha_pago, fecha_subida
        ) VALUES (
            v_id_usuario,
            v_id_curso,
            'assets/comprobantes/dummy_receipt.jpg', -- Placeholder, el archivo real no existe pero la DB lo admitirá
            'REF-TEST-' || floor(random() * 999999999)::text,
            v_banco,
            v_monto,
            v_estado,
            v_fecha,
            v_fecha + (floor(random() * 10) || ' hours')::interval
        );
    END LOOP;
END $$;
