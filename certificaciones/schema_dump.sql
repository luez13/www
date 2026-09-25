-- Table: cursos.actas_cierre
CREATE TABLE cursos.actas_cierre (
    id_acta integer NOT NULL DEFAULT nextval('cursos.actas_cierre_id_acta_seq'::regclass),
    id_materia_bimestre integer NOT NULL,
    fecha_cierre timestamp without time zone DEFAULT now(),
    participantes_inscritos integer NOT NULL DEFAULT 0,
    participantes_aprobados integer NOT NULL DEFAULT 0,
    firmante_vicerrector_id integer,
    firmante_coord_id integer,
    tipo_acta character varying(50) DEFAULT 'Regular'::character varying,
    fecha_generacion date DEFAULT CURRENT_DATE
);

-- Table: cursos.actividades_config
CREATE TABLE cursos.actividades_config (
    id_actividad_config integer NOT NULL DEFAULT nextval('cursos.actividades_config_id_actividad_config_seq'::regclass),
    id_materia_bimestre integer NOT NULL,
    nombre_actividad character varying(255) NOT NULL,
    ponderacion_porcentaje numeric NOT NULL
);

-- Table: cursos.auditoria
CREATE TABLE cursos.auditoria (
    id_auditoria integer NOT NULL DEFAULT nextval('cursos.auditoria_id_auditoria_seq'::regclass),
    usuario character varying(255),
    accion character varying(100) NOT NULL,
    tabla_afectada character varying(50) NOT NULL,
    fecha timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    dato_previo text,
    dato_modificado text,
    justificacion text
);

-- Table: cursos.buzon_sugerencias
CREATE TABLE cursos.buzon_sugerencias (
    id integer NOT NULL DEFAULT nextval('cursos.buzon_sugerencias_id_seq'::regclass),
    nombre character varying(100),
    apellido character varying(100),
    correo character varying(100),
    cedula character varying(100),
    sugerencia text,
    fecha_envio timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    id_usuario integer
);

-- Table: cursos.cargos
CREATE TABLE cursos.cargos (
    id_cargo integer NOT NULL DEFAULT nextval('cursos.cargos_id_cargo_seq'::regclass),
    nombre_cargo character varying(100) NOT NULL,
    nombre character varying(100),
    apellido character varying(100),
    titulo character varying(50),
    firma_digital character varying(255),
    id_usuario integer,
    activo boolean NOT NULL DEFAULT true
);

-- Table: cursos.certificaciones
CREATE TABLE cursos.certificaciones (
    id_certificacion integer NOT NULL DEFAULT nextval('cursos.certificaciones_id_certificacion_seq'::regclass),
    id_usuario integer,
    curso_id integer,
    valor_unico character varying,
    completado boolean DEFAULT false,
    nota integer,
    fecha_inscripcion timestamp without time zone,
    pago boolean,
    tomo integer,
    folio integer
);

-- Table: cursos.certificaciones_materias
CREATE TABLE cursos.certificaciones_materias (
    id_cert_materia integer NOT NULL DEFAULT nextval('cursos.certificaciones_materias_id_cert_materia_seq'::regclass),
    id_usuario integer NOT NULL,
    id_materia_bimestre integer NOT NULL,
    valor_unico character varying(255) NOT NULL,
    fecha_emision timestamp without time zone DEFAULT now(),
    tomo integer,
    folio integer
);

-- Table: cursos.comprobantes_pago
CREATE TABLE cursos.comprobantes_pago (
    id_comprobante integer NOT NULL DEFAULT nextval('cursos.comprobantes_pago_id_comprobante_seq'::regclass),
    id_usuario integer NOT NULL,
    id_curso integer NOT NULL,
    archivo_ruta character varying(255),
    numero_operacion character varying(100),
    banco_origen character varying(100),
    monto numeric NOT NULL,
    estado character varying(50) NOT NULL DEFAULT 'Pendiente'::character varying,
    fecha_pago date NOT NULL,
    fecha_subida timestamp without time zone DEFAULT now(),
    observacion text,
    id_materia_bimestre integer,
    moneda character varying(10) NOT NULL DEFAULT 'Bs'::character varying,
    id_admin_gestor integer,
    fecha_gestion timestamp without time zone
);

-- Table: cursos.config_sistema
CREATE TABLE cursos.config_sistema (
    clave_config character varying(100) NOT NULL,
    valor_config character varying(255) NOT NULL,
    descripcion_config text,
    fecha_modificacion timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);

-- Table: cursos.cuentas_bancarias
CREATE TABLE cursos.cuentas_bancarias (
    id_cuenta integer NOT NULL DEFAULT nextval('cursos.cuentas_bancarias_id_cuenta_seq'::regclass),
    banco character varying(100) NOT NULL,
    titular character varying(100) NOT NULL,
    cedula_rif character varying(50) NOT NULL,
    telefono character varying(50),
    correo character varying(100),
    tipo_cuenta character varying(50),
    numero_cuenta character varying(50),
    activo boolean NOT NULL DEFAULT true
);

-- Table: cursos.cursos
CREATE TABLE cursos.cursos (
    id_curso integer NOT NULL DEFAULT nextval('cursos.cursos_id_curso_seq'::regclass),
    promotor integer,
    nombre_curso character varying(255),
    descripcion text,
    tiempo_asignado integer,
    inicio_mes date,
    tipo_curso character varying(255),
    autorizacion character varying(255),
    limite_inscripciones integer,
    estado boolean,
    dias_clase ARRAY,
    horario_inicio time without time zone,
    horario_fin time without time zone,
    nivel_curso character varying(255),
    costo numeric,
    conocimientos_previos text,
    requerimientos_implemento text,
    desempeno_al_concluir text,
    horas_cronologicas integer,
    firma_digital boolean DEFAULT false,
    fecha_finalizacion timestamp without time zone,
    imagen_portada character varying(255),
    id_plantilla integer,
    nota_minima_aprobatoria integer DEFAULT 12,
    fecha_acta_cierre timestamp without time zone,
    permitir_pagos boolean DEFAULT true
);

-- Table: cursos.cursos_config_firmas
CREATE TABLE cursos.cursos_config_firmas (
    id_config integer NOT NULL DEFAULT nextval('cursos.cursos_config_firmas_id_config_seq'::regclass),
    id_curso integer NOT NULL,
    id_posicion integer NOT NULL,
    id_cargo_firmante integer,
    usar_promotor_curso boolean NOT NULL DEFAULT false
);

-- Table: cursos.landing_carrusel
CREATE TABLE cursos.landing_carrusel (
    id_carrusel integer NOT NULL DEFAULT nextval('cursos.landing_carrusel_id_carrusel_seq'::regclass),
    ruta_imagen character varying(255) NOT NULL,
    titulo character varying(255),
    descripcion text,
    activo boolean DEFAULT true,
    fecha_creacion timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);

-- Table: cursos.materias_bimestre
CREATE TABLE cursos.materias_bimestre (
    id_materia_bimestre integer NOT NULL DEFAULT nextval('cursos.materias_bimestre_id_materia_bimestre_seq'::regclass),
    id_curso integer NOT NULL,
    nombre_materia character varying(255) NOT NULL,
    duracion_bimestres character varying(50) NOT NULL,
    total_horas integer NOT NULL,
    modalidad character varying(255) NOT NULL DEFAULT 'Virtual'::character varying,
    docente_id integer NOT NULL,
    lapso_academico smallint NOT NULL DEFAULT 1,
    temario text,
    fecha_inicio date,
    fecha_fin date
);

-- Table: cursos.modulos
CREATE TABLE cursos.modulos (
    id_modulo integer NOT NULL DEFAULT nextval('cursos.modulos_id_modulo_seq'::regclass),
    id_curso integer,
    nombre_modulo text,
    contenido text,
    numero integer,
    actividad character varying(255),
    instrumento character varying(255)
);

-- Table: cursos.notas_participante
CREATE TABLE cursos.notas_participante (
    id_nota integer NOT NULL DEFAULT nextval('cursos.notas_participante_id_nota_seq'::regclass),
    id_usuario integer NOT NULL,
    id_actividad_config integer NOT NULL,
    calificacion_obtenida numeric NOT NULL
);

-- Table: cursos.plantillas_certificados
CREATE TABLE cursos.plantillas_certificados (
    id integer NOT NULL DEFAULT nextval('cursos.plantillas_certificados_id_seq'::regclass),
    nombre character varying(255) NOT NULL,
    imagen_fondo character varying(255),
    es_defecto boolean DEFAULT false,
    archivo_vista character varying(255) DEFAULT 'certificado_base.php'::character varying
);

-- Table: cursos.posiciones_firma
CREATE TABLE cursos.posiciones_firma (
    id_posicion integer NOT NULL DEFAULT nextval('cursos.posiciones_firma_id_posicion_seq'::regclass),
    codigo_posicion character varying(25) NOT NULL,
    descripcion_posicion character varying(100) NOT NULL,
    pagina smallint NOT NULL
);

-- Table: cursos.roles
CREATE TABLE cursos.roles (
    id_rol integer NOT NULL DEFAULT nextval('cursos.roles_id_rol_seq'::regclass),
    nombre_rol character varying(100)
);

-- Table: cursos.usuario_documentos
CREATE TABLE cursos.usuario_documentos (
    documento_id integer NOT NULL DEFAULT nextval('cursos.usuario_documentos_documento_id_seq'::regclass),
    usuario_id integer NOT NULL,
    documento_path character varying(255) NOT NULL,
    documento_type character varying(50) NOT NULL,
    id_curso integer
);

-- Table: cursos.usuario_materias
CREATE TABLE cursos.usuario_materias (
    id_usuario_materia integer NOT NULL DEFAULT nextval('cursos.usuario_materias_id_usuario_materia_seq'::regclass),
    id_usuario integer NOT NULL,
    id_materia_bimestre integer NOT NULL,
    nota_regular numeric,
    nota_recuperativa numeric DEFAULT NULL::numeric,
    estado character varying(50) NOT NULL DEFAULT 'Reprobado'::character varying
);

-- Table: cursos.usuarios
CREATE TABLE cursos.usuarios (
    id integer NOT NULL DEFAULT nextval('cursos.usuarios_id_seq'::regclass),
    nombre character varying(100),
    apellido character varying(100),
    correo character varying(100),
    password text,
    cedula character varying(100),
    id_rol integer,
    token character varying(255),
    confirmado boolean,
    firma_digital character varying(255),
    titulo character varying(50),
    cargo character varying(100),
    telefono character varying(20)
);

