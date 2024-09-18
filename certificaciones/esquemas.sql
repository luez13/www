--
-- PostgreSQL database dump
--

-- Dumped from database version 16.1
-- Dumped by pg_dump version 16.1

-- Started on 2024-09-14 00:39:58

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 7 (class 2615 OID 16399)
-- Name: cursos; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA cursos;


ALTER SCHEMA cursos OWNER TO postgres;

--
-- TOC entry 2 (class 3079 OID 16495)
-- Name: uuid-ossp; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA public;


--
-- TOC entry 4864 (class 0 OID 0)
-- Dependencies: 2
-- Name: EXTENSION "uuid-ossp"; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION "uuid-ossp" IS 'generate universally unique identifiers (UUIDs)';


--
-- TOC entry 241 (class 1255 OID 73877)
-- Name: cursos_audit(); Type: FUNCTION; Schema: cursos; Owner: postgres
--

CREATE FUNCTION cursos.cursos_audit() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    accion_texto VARCHAR(10);
    usuario_sistema INTEGER;
BEGIN
    -- Obtener el ID del usuario del sistema desde una variable de configuración
    SELECT current_setting('myapp.current_user_id', true)::INTEGER INTO usuario_sistema;

    IF TG_OP = 'DELETE' THEN
        accion_texto := 'DELETE';
    ELSIF TG_OP = 'UPDATE' THEN
        accion_texto := 'UPDATE';
    ELSE
        accion_texto := 'INSERT';
    END IF;

    INSERT INTO cursos.auditoria (usuario, accion, tabla_afectada, fecha, dato_previo, dato_modificado)
    VALUES (usuario_sistema, accion_texto, TG_TABLE_NAME, now(), OLD::text, NEW::text);

    RETURN NULL;
END;
$$;


ALTER FUNCTION cursos.cursos_audit() OWNER TO postgres;

--
-- TOC entry 242 (class 1255 OID 73728)
-- Name: cursos_audit(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.cursos_audit() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    accion_texto VARCHAR(10);
    usuario_sistema INTEGER;
BEGIN
    -- Intentar obtener el ID del usuario del sistema desde una variable de configuración
    BEGIN
        SELECT current_setting('myapp.current_user_id', true)::INTEGER INTO usuario_sistema;
    EXCEPTION
        WHEN others THEN
            -- Si no se puede obtener el ID del usuario, usar un valor predeterminado
            usuario_sistema := -1; -- Puedes usar -1 o cualquier otro valor que indique "sin usuario"
    END;

    IF TG_OP = 'DELETE' THEN
        accion_texto := 'DELETE';
    ELSIF TG_OP = 'UPDATE' THEN
        accion_texto := 'UPDATE';
    ELSE
        accion_texto := 'INSERT';
    END IF;

    INSERT INTO cursos.auditoria (usuario, accion, tabla_afectada, fecha, dato_previo, dato_modificado)
    VALUES (usuario_sistema, accion_texto, TG_TABLE_NAME, now(), OLD::text, NEW::text);

    RETURN NULL;
END;
$$;


ALTER FUNCTION public.cursos_audit() OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 226 (class 1259 OID 65556)
-- Name: auditoria; Type: TABLE; Schema: cursos; Owner: postgres
--

CREATE TABLE cursos.auditoria (
    id_auditoria integer NOT NULL,
    usuario character varying(255),
    accion character varying(20) NOT NULL,
    tabla_afectada character varying(50) NOT NULL,
    fecha timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    dato_previo text,
    dato_modificado text
);


ALTER TABLE cursos.auditoria OWNER TO postgres;

--
-- TOC entry 225 (class 1259 OID 65555)
-- Name: auditoria_id_auditoria_seq; Type: SEQUENCE; Schema: cursos; Owner: postgres
--

CREATE SEQUENCE cursos.auditoria_id_auditoria_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE cursos.auditoria_id_auditoria_seq OWNER TO postgres;

--
-- TOC entry 4865 (class 0 OID 0)
-- Dependencies: 225
-- Name: auditoria_id_auditoria_seq; Type: SEQUENCE OWNED BY; Schema: cursos; Owner: postgres
--

ALTER SEQUENCE cursos.auditoria_id_auditoria_seq OWNED BY cursos.auditoria.id_auditoria;


--
-- TOC entry 224 (class 1259 OID 16557)
-- Name: certificaciones; Type: TABLE; Schema: cursos; Owner: postgres
--

CREATE TABLE cursos.certificaciones (
    id_certificacion integer NOT NULL,
    id_usuario integer,
    curso_id integer,
    valor_unico character varying,
    completado boolean DEFAULT false,
    nota integer,
    fecha_inscripcion timestamp without time zone,
    pago boolean
);


ALTER TABLE cursos.certificaciones OWNER TO postgres;

--
-- TOC entry 223 (class 1259 OID 16556)
-- Name: certificaciones_id_certificacion_seq; Type: SEQUENCE; Schema: cursos; Owner: postgres
--

CREATE SEQUENCE cursos.certificaciones_id_certificacion_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE cursos.certificaciones_id_certificacion_seq OWNER TO postgres;

--
-- TOC entry 4866 (class 0 OID 0)
-- Dependencies: 223
-- Name: certificaciones_id_certificacion_seq; Type: SEQUENCE OWNED BY; Schema: cursos; Owner: postgres
--

ALTER SEQUENCE cursos.certificaciones_id_certificacion_seq OWNED BY cursos.certificaciones.id_certificacion;


--
-- TOC entry 222 (class 1259 OID 16548)
-- Name: cursos; Type: TABLE; Schema: cursos; Owner: postgres
--

CREATE TABLE cursos.cursos (
    id_curso integer NOT NULL,
    promotor character varying(255),
    nombre_curso character varying(255),
    descripcion text,
    tiempo_asignado integer,
    inicio_mes date,
    tipo_curso character varying(255),
    autorizacion character varying(255),
    limite_inscripciones integer,
    estado boolean,
    dias_clase text[],
    horario_inicio time without time zone,
    horario_fin time without time zone,
    nivel_curso character varying(255),
    costo numeric(10,2),
    conocimientos_previos text,
    requerimientos_implemento text,
    desempeno_al_concluir text
);


ALTER TABLE cursos.cursos OWNER TO postgres;

--
-- TOC entry 221 (class 1259 OID 16547)
-- Name: cursos_id_curso_seq; Type: SEQUENCE; Schema: cursos; Owner: postgres
--

CREATE SEQUENCE cursos.cursos_id_curso_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE cursos.cursos_id_curso_seq OWNER TO postgres;

--
-- TOC entry 4867 (class 0 OID 0)
-- Dependencies: 221
-- Name: cursos_id_curso_seq; Type: SEQUENCE OWNED BY; Schema: cursos; Owner: postgres
--

ALTER SEQUENCE cursos.cursos_id_curso_seq OWNED BY cursos.cursos.id_curso;


--
-- TOC entry 228 (class 1259 OID 81930)
-- Name: modulos; Type: TABLE; Schema: cursos; Owner: postgres
--

CREATE TABLE cursos.modulos (
    id_modulo integer NOT NULL,
    id_curso integer,
    nombre_modulo character varying(255),
    contenido text,
    numero integer,
    actividad character varying(255),
    instrumento character varying(255)
);


ALTER TABLE cursos.modulos OWNER TO postgres;

--
-- TOC entry 227 (class 1259 OID 81929)
-- Name: cursos_modulos_id_modulo_seq; Type: SEQUENCE; Schema: cursos; Owner: postgres
--

CREATE SEQUENCE cursos.cursos_modulos_id_modulo_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE cursos.cursos_modulos_id_modulo_seq OWNER TO postgres;

--
-- TOC entry 4868 (class 0 OID 0)
-- Dependencies: 227
-- Name: cursos_modulos_id_modulo_seq; Type: SEQUENCE OWNED BY; Schema: cursos; Owner: postgres
--

ALTER SEQUENCE cursos.cursos_modulos_id_modulo_seq OWNED BY cursos.modulos.id_modulo;


--
-- TOC entry 218 (class 1259 OID 16523)
-- Name: roles; Type: TABLE; Schema: cursos; Owner: postgres
--

CREATE TABLE cursos.roles (
    id_rol integer NOT NULL,
    nombre_rol character varying(100)
);


ALTER TABLE cursos.roles OWNER TO postgres;

--
-- TOC entry 217 (class 1259 OID 16522)
-- Name: roles_id_rol_seq; Type: SEQUENCE; Schema: cursos; Owner: postgres
--

CREATE SEQUENCE cursos.roles_id_rol_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE cursos.roles_id_rol_seq OWNER TO postgres;

--
-- TOC entry 4869 (class 0 OID 0)
-- Dependencies: 217
-- Name: roles_id_rol_seq; Type: SEQUENCE OWNED BY; Schema: cursos; Owner: postgres
--

ALTER SEQUENCE cursos.roles_id_rol_seq OWNED BY cursos.roles.id_rol;


--
-- TOC entry 230 (class 1259 OID 90129)
-- Name: usuario_documentos; Type: TABLE; Schema: cursos; Owner: postgres
--

CREATE TABLE cursos.usuario_documentos (
    documento_id integer NOT NULL,
    usuario_id integer NOT NULL,
    documento_path character varying(255) NOT NULL,
    documento_type character varying(50) NOT NULL
);


ALTER TABLE cursos.usuario_documentos OWNER TO postgres;

--
-- TOC entry 229 (class 1259 OID 90128)
-- Name: usuario_documentos_documento_id_seq; Type: SEQUENCE; Schema: cursos; Owner: postgres
--

CREATE SEQUENCE cursos.usuario_documentos_documento_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE cursos.usuario_documentos_documento_id_seq OWNER TO postgres;

--
-- TOC entry 4870 (class 0 OID 0)
-- Dependencies: 229
-- Name: usuario_documentos_documento_id_seq; Type: SEQUENCE OWNED BY; Schema: cursos; Owner: postgres
--

ALTER SEQUENCE cursos.usuario_documentos_documento_id_seq OWNED BY cursos.usuario_documentos.documento_id;


--
-- TOC entry 220 (class 1259 OID 16530)
-- Name: usuarios; Type: TABLE; Schema: cursos; Owner: postgres
--

CREATE TABLE cursos.usuarios (
    id integer NOT NULL,
    nombre character varying(100),
    apellido character varying(100),
    correo character varying(100),
    password text,
    cedula character varying(100),
    id_rol integer,
    token character varying(255),
    confirmado boolean,
    firma_digital character varying(255)
);


ALTER TABLE cursos.usuarios OWNER TO postgres;

--
-- TOC entry 219 (class 1259 OID 16529)
-- Name: usuarios_id_seq; Type: SEQUENCE; Schema: cursos; Owner: postgres
--

CREATE SEQUENCE cursos.usuarios_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE cursos.usuarios_id_seq OWNER TO postgres;

--
-- TOC entry 4871 (class 0 OID 0)
-- Dependencies: 219
-- Name: usuarios_id_seq; Type: SEQUENCE OWNED BY; Schema: cursos; Owner: postgres
--

ALTER SEQUENCE cursos.usuarios_id_seq OWNED BY cursos.usuarios.id;


--
-- TOC entry 4683 (class 2604 OID 65559)
-- Name: auditoria id_auditoria; Type: DEFAULT; Schema: cursos; Owner: postgres
--

ALTER TABLE ONLY cursos.auditoria ALTER COLUMN id_auditoria SET DEFAULT nextval('cursos.auditoria_id_auditoria_seq'::regclass);


--
-- TOC entry 4681 (class 2604 OID 16560)
-- Name: certificaciones id_certificacion; Type: DEFAULT; Schema: cursos; Owner: postgres
--

ALTER TABLE ONLY cursos.certificaciones ALTER COLUMN id_certificacion SET DEFAULT nextval('cursos.certificaciones_id_certificacion_seq'::regclass);


--
-- TOC entry 4680 (class 2604 OID 16551)
-- Name: cursos id_curso; Type: DEFAULT; Schema: cursos; Owner: postgres
--

ALTER TABLE ONLY cursos.cursos ALTER COLUMN id_curso SET DEFAULT nextval('cursos.cursos_id_curso_seq'::regclass);


--
-- TOC entry 4685 (class 2604 OID 81933)
-- Name: modulos id_modulo; Type: DEFAULT; Schema: cursos; Owner: postgres
--

ALTER TABLE ONLY cursos.modulos ALTER COLUMN id_modulo SET DEFAULT nextval('cursos.cursos_modulos_id_modulo_seq'::regclass);


--
-- TOC entry 4678 (class 2604 OID 16526)
-- Name: roles id_rol; Type: DEFAULT; Schema: cursos; Owner: postgres
--

ALTER TABLE ONLY cursos.roles ALTER COLUMN id_rol SET DEFAULT nextval('cursos.roles_id_rol_seq'::regclass);


--
-- TOC entry 4686 (class 2604 OID 90132)
-- Name: usuario_documentos documento_id; Type: DEFAULT; Schema: cursos; Owner: postgres
--

ALTER TABLE ONLY cursos.usuario_documentos ALTER COLUMN documento_id SET DEFAULT nextval('cursos.usuario_documentos_documento_id_seq'::regclass);


--
-- TOC entry 4679 (class 2604 OID 16533)
-- Name: usuarios id; Type: DEFAULT; Schema: cursos; Owner: postgres
--

ALTER TABLE ONLY cursos.usuarios ALTER COLUMN id SET DEFAULT nextval('cursos.usuarios_id_seq'::regclass);


--
-- TOC entry 4702 (class 2606 OID 65564)
-- Name: auditoria auditoria_pkey; Type: CONSTRAINT; Schema: cursos; Owner: postgres
--

ALTER TABLE ONLY cursos.auditoria
    ADD CONSTRAINT auditoria_pkey PRIMARY KEY (id_auditoria);


--
-- TOC entry 4698 (class 2606 OID 16562)
-- Name: certificaciones certificaciones_pkey; Type: CONSTRAINT; Schema: cursos; Owner: postgres
--

ALTER TABLE ONLY cursos.certificaciones
    ADD CONSTRAINT certificaciones_pkey PRIMARY KEY (id_certificacion);


--
-- TOC entry 4700 (class 2606 OID 40961)
-- Name: certificaciones certificaciones_valor_unico_key; Type: CONSTRAINT; Schema: cursos; Owner: postgres
--

ALTER TABLE ONLY cursos.certificaciones
    ADD CONSTRAINT certificaciones_valor_unico_key UNIQUE (valor_unico);


--
-- TOC entry 4704 (class 2606 OID 81937)
-- Name: modulos cursos_modulos_pkey; Type: CONSTRAINT; Schema: cursos; Owner: postgres
--

ALTER TABLE ONLY cursos.modulos
    ADD CONSTRAINT cursos_modulos_pkey PRIMARY KEY (id_modulo);


--
-- TOC entry 4696 (class 2606 OID 16555)
-- Name: cursos cursos_pkey; Type: CONSTRAINT; Schema: cursos; Owner: postgres
--

ALTER TABLE ONLY cursos.cursos
    ADD CONSTRAINT cursos_pkey PRIMARY KEY (id_curso);


--
-- TOC entry 4688 (class 2606 OID 16528)
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: cursos; Owner: postgres
--

ALTER TABLE ONLY cursos.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id_rol);


--
-- TOC entry 4706 (class 2606 OID 90134)
-- Name: usuario_documentos usuario_documentos_pkey; Type: CONSTRAINT; Schema: cursos; Owner: postgres
--

ALTER TABLE ONLY cursos.usuario_documentos
    ADD CONSTRAINT usuario_documentos_pkey PRIMARY KEY (documento_id);


--
-- TOC entry 4690 (class 2606 OID 16541)
-- Name: usuarios usuarios_cedula_key; Type: CONSTRAINT; Schema: cursos; Owner: postgres
--

ALTER TABLE ONLY cursos.usuarios
    ADD CONSTRAINT usuarios_cedula_key UNIQUE (cedula);


--
-- TOC entry 4692 (class 2606 OID 16539)
-- Name: usuarios usuarios_correo_key; Type: CONSTRAINT; Schema: cursos; Owner: postgres
--

ALTER TABLE ONLY cursos.usuarios
    ADD CONSTRAINT usuarios_correo_key UNIQUE (correo);


--
-- TOC entry 4694 (class 2606 OID 16537)
-- Name: usuarios usuarios_pkey; Type: CONSTRAINT; Schema: cursos; Owner: postgres
--

ALTER TABLE ONLY cursos.usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id);


--
-- TOC entry 4715 (class 2620 OID 73878)
-- Name: certificaciones certificaciones_trigger; Type: TRIGGER; Schema: cursos; Owner: postgres
--

CREATE TRIGGER certificaciones_trigger AFTER INSERT OR DELETE OR UPDATE ON cursos.certificaciones FOR EACH ROW EXECUTE FUNCTION cursos.cursos_audit();


--
-- TOC entry 4714 (class 2620 OID 73879)
-- Name: cursos cursos_trigger; Type: TRIGGER; Schema: cursos; Owner: postgres
--

CREATE TRIGGER cursos_trigger AFTER INSERT OR DELETE OR UPDATE ON cursos.cursos FOR EACH ROW EXECUTE FUNCTION cursos.cursos_audit();


--
-- TOC entry 4712 (class 2620 OID 73880)
-- Name: roles roles_trigger; Type: TRIGGER; Schema: cursos; Owner: postgres
--

CREATE TRIGGER roles_trigger AFTER INSERT OR DELETE OR UPDATE ON cursos.roles FOR EACH ROW EXECUTE FUNCTION cursos.cursos_audit();


--
-- TOC entry 4713 (class 2620 OID 73881)
-- Name: usuarios usuarios_trigger; Type: TRIGGER; Schema: cursos; Owner: postgres
--

CREATE TRIGGER usuarios_trigger AFTER INSERT OR DELETE OR UPDATE ON cursos.usuarios FOR EACH ROW EXECUTE FUNCTION cursos.cursos_audit();


--
-- TOC entry 4708 (class 2606 OID 57344)
-- Name: certificaciones certificaciones_id_curso_fkey; Type: FK CONSTRAINT; Schema: cursos; Owner: postgres
--

ALTER TABLE ONLY cursos.certificaciones
    ADD CONSTRAINT certificaciones_id_curso_fkey FOREIGN KEY (curso_id) REFERENCES cursos.cursos(id_curso);


--
-- TOC entry 4709 (class 2606 OID 16565)
-- Name: certificaciones certificaciones_id_usuario_fkey; Type: FK CONSTRAINT; Schema: cursos; Owner: postgres
--

ALTER TABLE ONLY cursos.certificaciones
    ADD CONSTRAINT certificaciones_id_usuario_fkey FOREIGN KEY (id_usuario) REFERENCES cursos.usuarios(id);


--
-- TOC entry 4710 (class 2606 OID 81938)
-- Name: modulos cursos_modulos_id_curso_fkey; Type: FK CONSTRAINT; Schema: cursos; Owner: postgres
--

ALTER TABLE ONLY cursos.modulos
    ADD CONSTRAINT cursos_modulos_id_curso_fkey FOREIGN KEY (id_curso) REFERENCES cursos.cursos(id_curso);


--
-- TOC entry 4711 (class 2606 OID 90135)
-- Name: usuario_documentos usuario_documentos_usuario_id_fkey; Type: FK CONSTRAINT; Schema: cursos; Owner: postgres
--

ALTER TABLE ONLY cursos.usuario_documentos
    ADD CONSTRAINT usuario_documentos_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES cursos.usuarios(id);


--
-- TOC entry 4707 (class 2606 OID 16542)
-- Name: usuarios usuarios_id_rol_fkey; Type: FK CONSTRAINT; Schema: cursos; Owner: postgres
--

ALTER TABLE ONLY cursos.usuarios
    ADD CONSTRAINT usuarios_id_rol_fkey FOREIGN KEY (id_rol) REFERENCES cursos.roles(id_rol);


-- Completed on 2024-09-14 00:39:58

--
-- PostgreSQL database dump complete
--

