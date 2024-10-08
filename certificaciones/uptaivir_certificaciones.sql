PGDMP                          |            uptaivir_certificaciones    10.23    10.23 B    H           0    0    ENCODING    ENCODING        SET client_encoding = 'UTF8';
                       false            I           0    0 
   STDSTRINGS 
   STDSTRINGS     (   SET standard_conforming_strings = 'on';
                       false            J           0    0 
   SEARCHPATH 
   SEARCHPATH     8   SELECT pg_catalog.set_config('search_path', '', false);
                       false                        2615    18293    cursos    SCHEMA        CREATE SCHEMA cursos;
    DROP SCHEMA cursos;
             postgres    false            �            1255    18294    cursos_audit()    FUNCTION     �  CREATE FUNCTION cursos.cursos_audit() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    accion_texto VARCHAR(10);
    usuario_sistema INTEGER;
BEGIN
    -- Obtener el ID del usuario del sistema desde una variable de configuración
    BEGIN
        SELECT current_setting('myapp.current_user_id', true)::INTEGER INTO usuario_sistema;
    EXCEPTION
        WHEN others THEN
            -- Si no se puede obtener el ID del usuario, usar un valor predeterminado
            usuario_sistema := -1; -- Puedes usar -1 o cualquier otro valor que indique "sin usuario"
    END;

    IF TG_OP = 'DELETE' THEN
        accion_texto := 'DELETE';
        INSERT INTO cursos.auditoria (usuario, accion, tabla_afectada, fecha, dato_previo, dato_modificado)
        VALUES (usuario_sistema, accion_texto, TG_TABLE_NAME, now(), OLD::text, NULL);
        RETURN OLD;
    ELSIF TG_OP = 'UPDATE' THEN
        accion_texto := 'UPDATE';
        INSERT INTO cursos.auditoria (usuario, accion, tabla_afectada, fecha, dato_previo, dato_modificado)
        VALUES (usuario_sistema, accion_texto, TG_TABLE_NAME, now(), OLD::text, NEW::text);
        RETURN NEW;
    ELSIF TG_OP = 'INSERT' THEN
        accion_texto := 'INSERT';
        INSERT INTO cursos.auditoria (usuario, accion, tabla_afectada, fecha, dato_previo, dato_modificado)
        VALUES (usuario_sistema, accion_texto, TG_TABLE_NAME, now(), NULL, NEW::text);
        RETURN NEW;
    END IF;

    RETURN NULL;
END;
$$;
 %   DROP FUNCTION cursos.cursos_audit();
       cursos       postgres    false    7            �            1259    18295 	   auditoria    TABLE     A  CREATE TABLE cursos.auditoria (
    id_auditoria integer NOT NULL,
    usuario character varying(255),
    accion character varying(20) NOT NULL,
    tabla_afectada character varying(50) NOT NULL,
    fecha timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    dato_previo text,
    dato_modificado text
);
    DROP TABLE cursos.auditoria;
       cursos         postgres    false    7            �            1259    18302    auditoria_id_auditoria_seq    SEQUENCE     �   CREATE SEQUENCE cursos.auditoria_id_auditoria_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 1   DROP SEQUENCE cursos.auditoria_id_auditoria_seq;
       cursos       postgres    false    197    7            K           0    0    auditoria_id_auditoria_seq    SEQUENCE OWNED BY     Y   ALTER SEQUENCE cursos.auditoria_id_auditoria_seq OWNED BY cursos.auditoria.id_auditoria;
            cursos       postgres    false    198            �            1259    18304    certificaciones    TABLE       CREATE TABLE cursos.certificaciones (
    id_certificacion integer NOT NULL,
    id_usuario integer,
    curso_id integer,
    valor_unico character varying,
    completado boolean DEFAULT false,
    nota integer,
    fecha_inscripcion timestamp without time zone,
    pago boolean
);
 #   DROP TABLE cursos.certificaciones;
       cursos         postgres    false    7            �            1259    18311 $   certificaciones_id_certificacion_seq    SEQUENCE     �   CREATE SEQUENCE cursos.certificaciones_id_certificacion_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 ;   DROP SEQUENCE cursos.certificaciones_id_certificacion_seq;
       cursos       postgres    false    7    199            L           0    0 $   certificaciones_id_certificacion_seq    SEQUENCE OWNED BY     m   ALTER SEQUENCE cursos.certificaciones_id_certificacion_seq OWNED BY cursos.certificaciones.id_certificacion;
            cursos       postgres    false    200            �            1259    18313    cursos    TABLE     i  CREATE TABLE cursos.cursos (
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
    DROP TABLE cursos.cursos;
       cursos         postgres    false    7            �            1259    18319    cursos_id_curso_seq    SEQUENCE     �   CREATE SEQUENCE cursos.cursos_id_curso_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 *   DROP SEQUENCE cursos.cursos_id_curso_seq;
       cursos       postgres    false    7    201            M           0    0    cursos_id_curso_seq    SEQUENCE OWNED BY     K   ALTER SEQUENCE cursos.cursos_id_curso_seq OWNED BY cursos.cursos.id_curso;
            cursos       postgres    false    202            �            1259    18321    modulos    TABLE     �   CREATE TABLE cursos.modulos (
    id_modulo integer NOT NULL,
    id_curso integer,
    nombre_modulo character varying(255),
    contenido text,
    numero integer,
    actividad character varying(255),
    instrumento character varying(255)
);
    DROP TABLE cursos.modulos;
       cursos         postgres    false    7            �            1259    18327    modulos_id_modulo_seq    SEQUENCE     �   CREATE SEQUENCE cursos.modulos_id_modulo_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 ,   DROP SEQUENCE cursos.modulos_id_modulo_seq;
       cursos       postgres    false    7    203            N           0    0    modulos_id_modulo_seq    SEQUENCE OWNED BY     O   ALTER SEQUENCE cursos.modulos_id_modulo_seq OWNED BY cursos.modulos.id_modulo;
            cursos       postgres    false    204            �            1259    18329    roles    TABLE     b   CREATE TABLE cursos.roles (
    id_rol integer NOT NULL,
    nombre_rol character varying(100)
);
    DROP TABLE cursos.roles;
       cursos         postgres    false    7            �            1259    18332    roles_id_rol_seq    SEQUENCE     �   CREATE SEQUENCE cursos.roles_id_rol_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 '   DROP SEQUENCE cursos.roles_id_rol_seq;
       cursos       postgres    false    205    7            O           0    0    roles_id_rol_seq    SEQUENCE OWNED BY     E   ALTER SEQUENCE cursos.roles_id_rol_seq OWNED BY cursos.roles.id_rol;
            cursos       postgres    false    206            �            1259    18334    usuario_documentos    TABLE     �   CREATE TABLE cursos.usuario_documentos (
    documento_id integer NOT NULL,
    usuario_id integer NOT NULL,
    documento_path character varying(255) NOT NULL,
    documento_type character varying(50) NOT NULL
);
 &   DROP TABLE cursos.usuario_documentos;
       cursos         postgres    false    7            �            1259    18337 #   usuario_documentos_documento_id_seq    SEQUENCE     �   CREATE SEQUENCE cursos.usuario_documentos_documento_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 :   DROP SEQUENCE cursos.usuario_documentos_documento_id_seq;
       cursos       postgres    false    207    7            P           0    0 #   usuario_documentos_documento_id_seq    SEQUENCE OWNED BY     k   ALTER SEQUENCE cursos.usuario_documentos_documento_id_seq OWNED BY cursos.usuario_documentos.documento_id;
            cursos       postgres    false    208            �            1259    18339    usuarios    TABLE     S  CREATE TABLE cursos.usuarios (
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
    DROP TABLE cursos.usuarios;
       cursos         postgres    false    7            �            1259    18345    usuarios_id_seq    SEQUENCE     �   CREATE SEQUENCE cursos.usuarios_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 &   DROP SEQUENCE cursos.usuarios_id_seq;
       cursos       postgres    false    7    209            Q           0    0    usuarios_id_seq    SEQUENCE OWNED BY     C   ALTER SEQUENCE cursos.usuarios_id_seq OWNED BY cursos.usuarios.id;
            cursos       postgres    false    210            �
           2604    18347    auditoria id_auditoria    DEFAULT     �   ALTER TABLE ONLY cursos.auditoria ALTER COLUMN id_auditoria SET DEFAULT nextval('cursos.auditoria_id_auditoria_seq'::regclass);
 E   ALTER TABLE cursos.auditoria ALTER COLUMN id_auditoria DROP DEFAULT;
       cursos       postgres    false    198    197            �
           2604    18348     certificaciones id_certificacion    DEFAULT     �   ALTER TABLE ONLY cursos.certificaciones ALTER COLUMN id_certificacion SET DEFAULT nextval('cursos.certificaciones_id_certificacion_seq'::regclass);
 O   ALTER TABLE cursos.certificaciones ALTER COLUMN id_certificacion DROP DEFAULT;
       cursos       postgres    false    200    199            �
           2604    18349    cursos id_curso    DEFAULT     r   ALTER TABLE ONLY cursos.cursos ALTER COLUMN id_curso SET DEFAULT nextval('cursos.cursos_id_curso_seq'::regclass);
 >   ALTER TABLE cursos.cursos ALTER COLUMN id_curso DROP DEFAULT;
       cursos       postgres    false    202    201            �
           2604    18350    modulos id_modulo    DEFAULT     v   ALTER TABLE ONLY cursos.modulos ALTER COLUMN id_modulo SET DEFAULT nextval('cursos.modulos_id_modulo_seq'::regclass);
 @   ALTER TABLE cursos.modulos ALTER COLUMN id_modulo DROP DEFAULT;
       cursos       postgres    false    204    203            �
           2604    18351    roles id_rol    DEFAULT     l   ALTER TABLE ONLY cursos.roles ALTER COLUMN id_rol SET DEFAULT nextval('cursos.roles_id_rol_seq'::regclass);
 ;   ALTER TABLE cursos.roles ALTER COLUMN id_rol DROP DEFAULT;
       cursos       postgres    false    206    205            �
           2604    18352    usuario_documentos documento_id    DEFAULT     �   ALTER TABLE ONLY cursos.usuario_documentos ALTER COLUMN documento_id SET DEFAULT nextval('cursos.usuario_documentos_documento_id_seq'::regclass);
 N   ALTER TABLE cursos.usuario_documentos ALTER COLUMN documento_id DROP DEFAULT;
       cursos       postgres    false    208    207            �
           2604    18353    usuarios id    DEFAULT     j   ALTER TABLE ONLY cursos.usuarios ALTER COLUMN id SET DEFAULT nextval('cursos.usuarios_id_seq'::regclass);
 :   ALTER TABLE cursos.usuarios ALTER COLUMN id DROP DEFAULT;
       cursos       postgres    false    210    209            8          0    18295 	   auditoria 
   TABLE DATA               w   COPY cursos.auditoria (id_auditoria, usuario, accion, tabla_afectada, fecha, dato_previo, dato_modificado) FROM stdin;
    cursos       postgres    false    197   8W       :          0    18304    certificaciones 
   TABLE DATA               �   COPY cursos.certificaciones (id_certificacion, id_usuario, curso_id, valor_unico, completado, nota, fecha_inscripcion, pago) FROM stdin;
    cursos       postgres    false    199   �Z       <          0    18313    cursos 
   TABLE DATA               *  COPY cursos.cursos (id_curso, promotor, nombre_curso, descripcion, tiempo_asignado, inicio_mes, tipo_curso, autorizacion, limite_inscripciones, estado, dias_clase, horario_inicio, horario_fin, nivel_curso, costo, conocimientos_previos, requerimientos_implemento, desempeno_al_concluir) FROM stdin;
    cursos       postgres    false    201   [       >          0    18321    modulos 
   TABLE DATA               p   COPY cursos.modulos (id_modulo, id_curso, nombre_modulo, contenido, numero, actividad, instrumento) FROM stdin;
    cursos       postgres    false    203   �[       @          0    18329    roles 
   TABLE DATA               3   COPY cursos.roles (id_rol, nombre_rol) FROM stdin;
    cursos       postgres    false    205   �[       B          0    18334    usuario_documentos 
   TABLE DATA               f   COPY cursos.usuario_documentos (documento_id, usuario_id, documento_path, documento_type) FROM stdin;
    cursos       postgres    false    207   \       D          0    18339    usuarios 
   TABLE DATA               |   COPY cursos.usuarios (id, nombre, apellido, correo, password, cedula, id_rol, token, confirmado, firma_digital) FROM stdin;
    cursos       postgres    false    209   6\       R           0    0    auditoria_id_auditoria_seq    SEQUENCE SET     I   SELECT pg_catalog.setval('cursos.auditoria_id_auditoria_seq', 35, true);
            cursos       postgres    false    198            S           0    0 $   certificaciones_id_certificacion_seq    SEQUENCE SET     R   SELECT pg_catalog.setval('cursos.certificaciones_id_certificacion_seq', 1, true);
            cursos       postgres    false    200            T           0    0    cursos_id_curso_seq    SEQUENCE SET     A   SELECT pg_catalog.setval('cursos.cursos_id_curso_seq', 1, true);
            cursos       postgres    false    202            U           0    0    modulos_id_modulo_seq    SEQUENCE SET     C   SELECT pg_catalog.setval('cursos.modulos_id_modulo_seq', 2, true);
            cursos       postgres    false    204            V           0    0    roles_id_rol_seq    SEQUENCE SET     ?   SELECT pg_catalog.setval('cursos.roles_id_rol_seq', 1, false);
            cursos       postgres    false    206            W           0    0 #   usuario_documentos_documento_id_seq    SEQUENCE SET     R   SELECT pg_catalog.setval('cursos.usuario_documentos_documento_id_seq', 1, false);
            cursos       postgres    false    208            X           0    0    usuarios_id_seq    SEQUENCE SET     =   SELECT pg_catalog.setval('cursos.usuarios_id_seq', 3, true);
            cursos       postgres    false    210            �
           2606    18355    auditoria auditoria_pkey 
   CONSTRAINT     `   ALTER TABLE ONLY cursos.auditoria
    ADD CONSTRAINT auditoria_pkey PRIMARY KEY (id_auditoria);
 B   ALTER TABLE ONLY cursos.auditoria DROP CONSTRAINT auditoria_pkey;
       cursos         postgres    false    197            �
           2606    18357 $   certificaciones certificaciones_pkey 
   CONSTRAINT     p   ALTER TABLE ONLY cursos.certificaciones
    ADD CONSTRAINT certificaciones_pkey PRIMARY KEY (id_certificacion);
 N   ALTER TABLE ONLY cursos.certificaciones DROP CONSTRAINT certificaciones_pkey;
       cursos         postgres    false    199            �
           2606    18359 /   certificaciones certificaciones_valor_unico_key 
   CONSTRAINT     q   ALTER TABLE ONLY cursos.certificaciones
    ADD CONSTRAINT certificaciones_valor_unico_key UNIQUE (valor_unico);
 Y   ALTER TABLE ONLY cursos.certificaciones DROP CONSTRAINT certificaciones_valor_unico_key;
       cursos         postgres    false    199            �
           2606    18361    cursos cursos_pkey 
   CONSTRAINT     V   ALTER TABLE ONLY cursos.cursos
    ADD CONSTRAINT cursos_pkey PRIMARY KEY (id_curso);
 <   ALTER TABLE ONLY cursos.cursos DROP CONSTRAINT cursos_pkey;
       cursos         postgres    false    201            �
           2606    18363    modulos modulos_pkey 
   CONSTRAINT     Y   ALTER TABLE ONLY cursos.modulos
    ADD CONSTRAINT modulos_pkey PRIMARY KEY (id_modulo);
 >   ALTER TABLE ONLY cursos.modulos DROP CONSTRAINT modulos_pkey;
       cursos         postgres    false    203            �
           2606    18365    roles roles_pkey 
   CONSTRAINT     R   ALTER TABLE ONLY cursos.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id_rol);
 :   ALTER TABLE ONLY cursos.roles DROP CONSTRAINT roles_pkey;
       cursos         postgres    false    205            �
           2606    18367 *   usuario_documentos usuario_documentos_pkey 
   CONSTRAINT     r   ALTER TABLE ONLY cursos.usuario_documentos
    ADD CONSTRAINT usuario_documentos_pkey PRIMARY KEY (documento_id);
 T   ALTER TABLE ONLY cursos.usuario_documentos DROP CONSTRAINT usuario_documentos_pkey;
       cursos         postgres    false    207            �
           2606    18369    usuarios usuarios_cedula_key 
   CONSTRAINT     Y   ALTER TABLE ONLY cursos.usuarios
    ADD CONSTRAINT usuarios_cedula_key UNIQUE (cedula);
 F   ALTER TABLE ONLY cursos.usuarios DROP CONSTRAINT usuarios_cedula_key;
       cursos         postgres    false    209            �
           2606    18371    usuarios usuarios_correo_key 
   CONSTRAINT     Y   ALTER TABLE ONLY cursos.usuarios
    ADD CONSTRAINT usuarios_correo_key UNIQUE (correo);
 F   ALTER TABLE ONLY cursos.usuarios DROP CONSTRAINT usuarios_correo_key;
       cursos         postgres    false    209            �
           2606    18373    usuarios usuarios_pkey 
   CONSTRAINT     T   ALTER TABLE ONLY cursos.usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id);
 @   ALTER TABLE ONLY cursos.usuarios DROP CONSTRAINT usuarios_pkey;
       cursos         postgres    false    209            �
           2620    18374 '   certificaciones certificaciones_trigger    TRIGGER     �   CREATE TRIGGER certificaciones_trigger AFTER INSERT OR DELETE OR UPDATE ON cursos.certificaciones FOR EACH ROW EXECUTE PROCEDURE cursos.cursos_audit();
 @   DROP TRIGGER certificaciones_trigger ON cursos.certificaciones;
       cursos       postgres    false    223    199            �
           2620    18375    cursos cursos_trigger    TRIGGER     �   CREATE TRIGGER cursos_trigger AFTER INSERT OR DELETE OR UPDATE ON cursos.cursos FOR EACH ROW EXECUTE PROCEDURE cursos.cursos_audit();
 .   DROP TRIGGER cursos_trigger ON cursos.cursos;
       cursos       postgres    false    201    223            �
           2620    18376    roles roles_trigger    TRIGGER     �   CREATE TRIGGER roles_trigger AFTER INSERT OR DELETE OR UPDATE ON cursos.roles FOR EACH ROW EXECUTE PROCEDURE cursos.cursos_audit();
 ,   DROP TRIGGER roles_trigger ON cursos.roles;
       cursos       postgres    false    205    223            �
           2620    18377    usuarios usuarios_trigger    TRIGGER     �   CREATE TRIGGER usuarios_trigger AFTER INSERT OR DELETE OR UPDATE ON cursos.usuarios FOR EACH ROW EXECUTE PROCEDURE cursos.cursos_audit();
 2   DROP TRIGGER usuarios_trigger ON cursos.usuarios;
       cursos       postgres    false    223    209            �
           2606    18378 -   certificaciones certificaciones_id_curso_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY cursos.certificaciones
    ADD CONSTRAINT certificaciones_id_curso_fkey FOREIGN KEY (curso_id) REFERENCES cursos.cursos(id_curso);
 W   ALTER TABLE ONLY cursos.certificaciones DROP CONSTRAINT certificaciones_id_curso_fkey;
       cursos       postgres    false    201    199    2729            �
           2606    18383 /   certificaciones certificaciones_id_usuario_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY cursos.certificaciones
    ADD CONSTRAINT certificaciones_id_usuario_fkey FOREIGN KEY (id_usuario) REFERENCES cursos.usuarios(id);
 Y   ALTER TABLE ONLY cursos.certificaciones DROP CONSTRAINT certificaciones_id_usuario_fkey;
       cursos       postgres    false    209    199    2741            �
           2606    18388 $   modulos cursos_modulos_id_curso_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY cursos.modulos
    ADD CONSTRAINT cursos_modulos_id_curso_fkey FOREIGN KEY (id_curso) REFERENCES cursos.cursos(id_curso);
 N   ALTER TABLE ONLY cursos.modulos DROP CONSTRAINT cursos_modulos_id_curso_fkey;
       cursos       postgres    false    201    203    2729            �
           2606    18393 5   usuario_documentos usuario_documentos_usuario_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY cursos.usuario_documentos
    ADD CONSTRAINT usuario_documentos_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES cursos.usuarios(id);
 _   ALTER TABLE ONLY cursos.usuario_documentos DROP CONSTRAINT usuario_documentos_usuario_id_fkey;
       cursos       postgres    false    2741    209    207            �
           2606    18398    usuarios usuarios_id_rol_fkey    FK CONSTRAINT        ALTER TABLE ONLY cursos.usuarios
    ADD CONSTRAINT usuarios_id_rol_fkey FOREIGN KEY (id_rol) REFERENCES cursos.roles(id_rol);
 G   ALTER TABLE ONLY cursos.usuarios DROP CONSTRAINT usuarios_id_rol_fkey;
       cursos       postgres    false    205    209    2733            8   `  x�͖�n�8���SA3 ��I�fܤ��q���8A7I��/jui���9��J9N�"q�L��ZX� ����_�<�PT���U����{ۏ�b� �)�X<!�0�����]7�#_�f�L�����=Z�K@u��`��5�PӢ.Z�����z]&cD>נ�4A �0��!��~�M b.�br.Ðq�|�Wu�"�HVK�H_)4Ucʼ�C��aI5[���+4*��b��̧��1��|�b�v���]��q�t��As1�=��yu(�٤���}8�I�V��#�|O�M�KD$(I ��Vr�%�\�1�w.�`83���̡G����.�%XƂ�a����ִ�ߨ�ev���J���Ƨ����%��γ��ZR?8�|�>��C�|V��|"��z���n�7��NO���+�WT1��0��s��Ɛ����
�!�cE�9�Q��EC�4SF�lSV���	�!^�]+���"Q�C�̷!B���M3�:0e�����-me�l�Ky�o��.��6M�0>��r+��5�X��D�&����^��{��1��Yy���Kt^��ޱ;�}Mǧ�^}��r:��q��Q������^`�_�
2�I�f�jϙp$62�l��I]��#|U�w��!�"D�)�?���ͧ��^�p$&s�7�� ��3G���}Y�Yn�͋٭#'	�X*&���:�9wR*���I&	;���8|��`)��B��0�d�R���ϴZ�������X�.��q �L�j+����@����3Xi[��	@��x,4!�d7��n�T��xK��G�����0V����/�e9���.����
�pc�      :   b   x����0�7UE���x�.��#� �#S%&O��8�����/�3[��au���ʻb�
�:tΎ�F�/	�E���\&����J�]�����w�      <   s   x�3�4�,(*MMJ�QF�FF&�����ũ��y�E��Ŝ1~�FF�%����>�y��:��E% *��ʢ�������Z�^+s+#3��(?�4�$h�=����+F��� s,1      >   !   x�3�4�,(*MMJ�Qh|.#Fh*b���� I*I      @   ;   x�3�L-.I�KI,�2�,(���/�/�2�L,ҙU�)@�	gbJnf^fqI����� /�      B      x������ � �      D   5  x�e��r�@ D��wx��Y���[��V���Yq$*h��S�9$�;t��U#P���2�����駼�쪺��A�6��*l�j��2��~4�Ѵޕͧ.�
��f�(Wσе1$#�3� @ �b�7�`RnC4��*(H 4�Ğ>����=�eԛd�a�����%��\3�������f�
ܯ]��j��W�!���Ɔc�@J�|E1��J�V�m00��w��J����*�N��̒���o/�X
����>V�.�si��m��Xc��W�!�2$���""���*���y_ʘ�     