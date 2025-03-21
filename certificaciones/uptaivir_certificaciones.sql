PGDMP                     	    |            uptaivir_certificaciones    10.23    10.23 B    H           0    0    ENCODING    ENCODING        SET client_encoding = 'UTF8';
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
            cursos       postgres    false    198            �            1259    18304    certificaciones    TABLE     B  CREATE TABLE cursos.certificaciones (
    id_certificacion integer NOT NULL,
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
    cursos       postgres    false    197   oW       :          0    18304    certificaciones 
   TABLE DATA               �   COPY cursos.certificaciones (id_certificacion, id_usuario, curso_id, valor_unico, completado, nota, fecha_inscripcion, pago, tomo, folio) FROM stdin;
    cursos       postgres    false    199   Qu       <          0    18313    cursos 
   TABLE DATA               *  COPY cursos.cursos (id_curso, promotor, nombre_curso, descripcion, tiempo_asignado, inicio_mes, tipo_curso, autorizacion, limite_inscripciones, estado, dias_clase, horario_inicio, horario_fin, nivel_curso, costo, conocimientos_previos, requerimientos_implemento, desempeno_al_concluir) FROM stdin;
    cursos       postgres    false    201   w       >          0    18321    modulos 
   TABLE DATA               p   COPY cursos.modulos (id_modulo, id_curso, nombre_modulo, contenido, numero, actividad, instrumento) FROM stdin;
    cursos       postgres    false    203   �y       @          0    18329    roles 
   TABLE DATA               3   COPY cursos.roles (id_rol, nombre_rol) FROM stdin;
    cursos       postgres    false    205   k{       B          0    18334    usuario_documentos 
   TABLE DATA               f   COPY cursos.usuario_documentos (documento_id, usuario_id, documento_path, documento_type) FROM stdin;
    cursos       postgres    false    207   �{       D          0    18339    usuarios 
   TABLE DATA               |   COPY cursos.usuarios (id, nombre, apellido, correo, password, cedula, id_rol, token, confirmado, firma_digital) FROM stdin;
    cursos       postgres    false    209   �{       R           0    0    auditoria_id_auditoria_seq    SEQUENCE SET     J   SELECT pg_catalog.setval('cursos.auditoria_id_auditoria_seq', 301, true);
            cursos       postgres    false    198            S           0    0 $   certificaciones_id_certificacion_seq    SEQUENCE SET     S   SELECT pg_catalog.setval('cursos.certificaciones_id_certificacion_seq', 59, true);
            cursos       postgres    false    200            T           0    0    cursos_id_curso_seq    SEQUENCE SET     B   SELECT pg_catalog.setval('cursos.cursos_id_curso_seq', 44, true);
            cursos       postgres    false    202            U           0    0    modulos_id_modulo_seq    SEQUENCE SET     D   SELECT pg_catalog.setval('cursos.modulos_id_modulo_seq', 97, true);
            cursos       postgres    false    204            V           0    0    roles_id_rol_seq    SEQUENCE SET     ?   SELECT pg_catalog.setval('cursos.roles_id_rol_seq', 1, false);
            cursos       postgres    false    206            W           0    0 #   usuario_documentos_documento_id_seq    SEQUENCE SET     R   SELECT pg_catalog.setval('cursos.usuario_documentos_documento_id_seq', 1, false);
            cursos       postgres    false    208            X           0    0    usuarios_id_seq    SEQUENCE SET     >   SELECT pg_catalog.setval('cursos.usuarios_id_seq', 15, true);
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
       cursos       postgres    false    205    209    2733            8      x��]�r9�][_�P̢;�]F"�Ԧ�_���e[���S�-�E��13˻����?v��"MYdU�&�eS^H%Y,�<H 3�D�����h��_������k�����3.�`�Pۜ�
��Y�VI����w�7��ð���߷@ѯ��Ӻ.�����s:��}z��FP�i[�Ёd\s�߁�p�^��&�ΪA�� ���]���ڿFt����ŋx��W�5;~��|��/��hEѫǣ�y��~�I������I�=��I���?������g��ݓǣ/�q?�}��u�w<����>��=<���Q|���������u�#Bk�S-QZe2��D&I�P�0�ΰ�$��޾���\�.�@	Ɣm�����t�)��G��E�B�z2�Fi^�vY�����^�jLް�k�� ���ku����P������v�;::��Go���:��s�&�4"QĀ�*�)(,
Mj�,�%P5B�J.��'Z�)4�&��kC<�_fR�"��A@1�ѫ/
�;����y���%����_�F����8�[����4��{��������p�OF1ͦ}����n[���ǖf6�-�����3�S_/���%���Ӵ{�$~�n�3z��2yr��>�s�>5z�������ţ�Q�����wX�?�Y�ie<��*�LC��,�@�Q����ux&���rS�d�-�/�V��Ȝ֖���AJ����@���a��ݬ�q�߻�bH��T��Pj��C��������':da�4��Xc��J���9j�L
�b��H��ei����<��7D�OurF!ō�K3 ��Ұ�
\�
��S�9�Kl��J2����l3�F�.����;�m͘�Fr^g'$s�������
�i�E-��2ˏ�8�~�"g�@^�.� ,���ۄ�{����)M�5����m0��EXf48�t�8��il���KA5��aB��H�TMk��0!�-H���9F�"d��ڌ�]`��L4��p��E)�*I��d��M���<���,�%F/�(R��ie��H6�]�?+)�.PN�Z��P���ZH���
�y��+�Ӕ�I�ǉ�ZZr����`��_Z6J�bi�z�h��`�tjCL��Q���$���XFG����FRd"�Y边�K73�*���n6u����~VkPS�k;ڴ)`����.#Ё߷dm_�q"�S1��˛�j�ltF�y��/�!z�S�ܰ ��Z��2��,[�ס�M]
A�jf{���K�$VӨw��Vj���lIQ�"����2�E��4.���C#�@;�	{��tK�J��aD�䙵z�ztK�:4
0�u�6aoQ�n���&����8��V�"�g~�/�~���?�Ń{w�p��&gQzv�5;=L��w�֧g�g����C}p|��雴#��ibR-��P�4V���e�!�&����m�H+<�I<|U�����?���@��������5���OG�;�����ٍr��� n_����=|e�	� m�Oj�,
#�Y��Hj/�b5D�Ʌ`*�	=��L']���e9F�lo�����^�ѯ������������ǯoc����}����?%�:�����f�p��2�#$b̵R:ND.Ԓ�� $p�;�73F�M���ˢ|z�F�i2��޿��=���=����>��~z�=:?�o>���{Bb�f����(.�������q3mĢD%�v��X���HJ�D��?���������q�^�=�{��}h8<KNF���u�ď?��-Do����-�}w#�y����y���c(bɬ�ef����!7 �4Q�d�XK�	"7� ���fo�~H�=_�����rp��#��ݿ�xp����^`���~|u���z���0�;���l۱�e�����2k��a��TGI�5dQ�k�[X�sC�9�_f?^���������7'#{ٓ[�����;�n���}�����6�������~/��ʟSi���8f��<3���c�1ʧ.5?auZg��QX�on�{q:��$/��={���ͳ��������E 񓧇���/���;$�>��/��<�3������q�F|�rG���5w67N8�"��̥f�ZN� �2@�xQ��~�I����I��_H�����@�𓇋��,`0g;^����(0��
'�/��e7�� �$��2��
%�l��^=΋_d��c�1F\���(d��iNn��b ������C�YJA�0�`����[�Q�QB5J���WB��B(}n�b�U���Ȥ6�?���\$V�"�L���N���n��
��y����~*��Z��F��R�ؼ6�R�Δ���E ��Rʔn.W���<�)IM��ۯt
��r~�O��Ws��ʹx���Gw�"�=�\-3?�)���r>a�I��_"�a�kVs�E��v76��R������$�U.Z5q�B����:q,�M�e2��#9�T�#iX]>9�*,��h#�i�5U�M�����Q
�cYk 0��Ѕ���	�4K��.�Q�8�̆.�I��	�2�h��,Jp��
6��:8]��?�5�O���D�B��1$Z:��Df,B�j�ul�Lbō�����F�0�RL�`�%��*����D����a�e����V6OYo�v��].�<���x�L<Xfc�	�$���E(с�4:V�٘������<��܀���&����&��4d !�BXX�Щ���knXo���H1N?�jX���ܰ��lRx��ȸ`�a�^b{�Z�B�W�� �U�i�Wf8[�VlY~����h�7p���$r�M��B�&�Pg ����I���� q�%A�-�<34"Kc�i�u�=�T��U�2�?�l�t��(��56�C�X�:g4�Q��l.O�(.��:�,������j�/k������ž�-�c!N)�b	d�p�whx$��cƅǑI���:���^ғ�M�q���%M�L8[�D�FA.��oe6#5]�g��@�\fc��p�K���T`�\�̬���:2�oc�yZX%'�����/S�Ǭs��A|L�w��k
i'�O�E���7@��j�B���A`�+i����4�7ڜ�d��q�w�p7v��Z�z��M�^G=^Į4� \M�l�=�0�>1rWмa)��i�7ȉ�3��֟U�ը5o:�k��BiDM�q�(����vrm.�e�B��@qT�4����ƨ�̸��@H�T���#�hs���Ex������І��S�:^J�'@u$r/�r���g��<�>�ި�����y6�x���v�����eq���"PƢ?p�n�� L���L̩�7���Ā�i�j݀׏�QV��K��l��@I�W ����蘄3�x�eeXE���$ڇ�?#e;�\>�q�/e���	�Zv�B��U~�Uhv����(:��ם	L��׳����Iz���p�7�{p�;�!��g?PA�}�]����r�wNݓt�?�G��Ǯ�v:;�.w��v�}����_m���^�Ϸ����p����y7	����0ƫ��('�k/.j������Q�?���D��c 9ut����b��%�Q7�wK��/	�mw��HX=��d!,�7�F��u�~VI��gB>��^2U�4�"W���,�r�_ �LG�)���8��	�=��9��܆��A%_���P�f�	h�hc�g-KF~#�]�D���h����F���[ܥ�o�ˑ%1��(TC�a�r�����4�E�O�Q!��2&����IG_s"Ia�ij�*���o�3[<�lV�'�>��}]���9c�5������apN��i%2�[���|ck�C��"�o�Ҟ�_m���\��1�'R@` ���ߞ��h,Z����@�����V9��)�Y����B��L$�����|��+t�M$l�(s�>2��x���w�����ſ\� %��kDZ�������4���g��^�kѴ2����p�L�9�:��qS
�H�6�%�;R1�
� �  6��(IS��d��P�a��	#�ɬ�L%��k�#����/&>_j�ȻUX�D��Ò��e����V�if3#�3��UĲ4A�#&l%L�$L�bB'q���B:5ɲ�@'m���g�?C�V���~c~���=�g�T�:�cΜC��k����ֆ�ԼN��%ʅ<R5��Klm&'�F�.+��a���H�k�(=N��u��o���>t��NH_{�ތ�j��맟^�����>{r�f?|�o��n�8�����<:y7<;����H]*j��!!����?M$#���"ѱI}�+�+���A���pk�'U? UZI�Ġlo��ᮟ�{���h��&"�?��.��F{������q��_�w��;ib�O��?��%�G{O����<z����wSA�,�BKn�@H���H�'�4$	S.�}��$�;��_�?����U�M�o�A�>-N�Id�p#-țG���Rs!���������Bu�����	����J�i����e��t�,4~�~���cG%%�8�7+�BieQ����p�:���N^
�'=9�)|���Z��$/f�t_ۡ/�WN������j�����VL��A)��W�YP��f��x���r��3.��J��B�P�\]I���1]��Ěs;?���R7y�$��.� �O��0�Z�=�θd��z�8�2��r�dd�̆�yA,N�g���_�r��7`/����լ���N7�:Ć���k�Z)��6j�"�	�`�H���6�<��e�9�H���^9�_K(.�c�XF��_I!����5�3�s`̤�ײ�1ⲹ�bQ����J�Z3ùZT�����^���r��Ӵ����-�V�+�,	��h1oU~�^�/��#����a��Ҹ\b9����3Uj� ;�G:SG7����\-l]��R��z)����̈́���,�D�j��r@Oj��5|�ꅭ0|�F3��'׍7'�����-�Y&����:���̈-7|fW(:P,���Ͳ�1�bF9���+��,t`Pi*F�^�s��0|Ȃ1�Wl�����󏢺���l�ڎA�E��"��m|��r>�/-�^�9��1�;�u����:�e��� -|塞������op"�Gl@�_M��x�����P���8&PV(�+�^���U�1d��9q�V�r��s��T�hڌ�b����?'߿�}�u��tS�w����n���U�Q�yN|���.��8�6�E+�L��=8�*���ˡ�_栣1���eb����\�9�U��І�
MHma=י
K����Ama=�^n2�r$t�2%�J#�	U�f�9JSe<�k�����j���� �2�4;T�Q!(�i��h�^i�Ҡ����z�m�=_eZ�'N���c��˄/��t�a��T��|5
��'��9񥦕�/�6��`q������籛(#�<���F�q����-A�_{���W� �l�7�K�o��w&�ܶ���w�SJQ��� ω/5���V�n�)�l��������窈/_�����*�Ϝ;��x^�8K��E�W�HT�T�jq���W�v�R�.�Γo��R�J؁���*�_^���>��g_�YJ|�q���+�1�n��*�J�M ��� ~y!�^�ᗭ��!^�MN���8۪Ι4�^�T�_=��o�y�~�KPJ�}��82�T/�TT|[��ep���LC)����Pk�o4��l�������[�ep�8�/�Yp�}-�m�з��l� ��9nQ�h���qd�/^���3��<k���*2����DE��
��՜YE�����vW�����Wnf�屏����
��Z��ep���?*� )W�]�[g����ɔ�Vk�fW�YEfy �}a|��Ei�����%�dJ��c�*�2@d�\?P���%�t�I���s����1��X�Dڮj��r���A��J�5�Ƃh����%�䄂/v��%en����<�~��.������o GhyL���tC�K�[��u����:�����"@[���|�����Q8�������~�rm�����>{�Z�{O���n�s�7z߹�?����)<�O���
��٧����<��Q��ہNYr��0�b��0�L�Ze�6
���`�$��-��k��)F�x]v�5��=� )NQF�q��8av:u�eX���Ngg�H?U=�N��s��B��/�z�Щ��� ��ڇ/bᏃ��ľ:qǹ���I�L
W���^?�t�k���Qxލ���x�_m���4����G��5:)�~�s�����p���J0;�g��9Sט?���k}E�y�%c�`��	����B�Y�D��ʀr��;;+7�jP*���&H�A����w����0[Vz*gV��Fd�ض�ѾwĖ��Ģ�B������l�8]=���Q��Su5��Jk��?N��__@�D�(���%W>�i5�1����|N岹�1��I�|N*��t�(!�}�kxd5�� u�7ς�Kwz�b�7���iw��~,�b�O�^�أI���i���& �յ7���n��me�_g����u��kIUcظ~W�<��9��fզ���Jz^W3*V�r�Ё�h���F���z;�v��K]D�������E�HjN��M`_�^�v`=����l*O�t�WGʹ��*VA��o�@1)�����ڐ�
 D�����WsT,7����w�aM�3���_K�]]G9L ��k	;��T�>u;�v2���BA��z $h����Ҵ�zJ������[SC�TjH�j�_�Br�7�t�ʧ���I��l0W�uT���6������M��'Q���5�ɦz�"5P�j�XK�bU~5����WhN�^� ^�@�~-���(����T��.��X���O˾f-)ϲ�h�`L�e+�5\m��NQTME�=��\�4�Z%=<ҊNI'H�[%�JZH�z���"��+���а���~n�vTd_:Dt�e_
��ZT�Z�'�ٷ�ae^煢 lp�hS���4r੖n@�n��EHf�h�da[�<��J��i:�@�*�	�lnA�'�S	,�B:��U�F�U�mdh�$v:Q����tӄZ�حh#A�'�S��uO�+	!�=ڮzٳ��l��N#T�����"+����0�6Ob�%'ŽJ_�HI��N�m#A�'�S����\%�֠�ՒhA�'��ܪP	MQ(m�5X^�5m��[ܖ�c�8#-e$\�Um��[tax�JX*]L��ϛ���͓ةD����u�������#h�$v*Qr�<W	M��"���=�6�y;�(Y��*!�.�@�fW��H�4�d��R:��T���s��H��I�Eۛ*a�V\��+�;�(Y�t*A���2�b�%(�H��I�T�d�2W	�+�����涑�͓ةD��W	��q�Ue��86@�-d%��^%�մL ��j#A�'�S���K�RQ�l����]	�<���lmm�?��#[      :   �  x�u�ˍ\!Eׯ�p~����p�
"p�ruKcklYB��p�)��/�<ЫH�'�%��J�
O	�ASu!�=�L`LXg��~^h�w�^�P��O�;S�����|h\v�_j�n,>ko]��l��c�5$��9X;�k&o��ռ~�7��<��,7�!�y��м�E�ԱO�P�c��� ��$�,p�Qd�8t�:\��y��J73�"���C�� ��\�:k��+�:/F&�υ�`�c�YzFo}n |i�U���0uy���=�^�^Kff �c��ё1�>e����rTᎠ�y��I��,~��x�ȼ1�;�� =��A�d��~0�`���@�ۮ�!Mk��)C�(��{n�)0�'��'�����sbH^�. څK�J�y%[l���Au�V�]=�+d�^���m�?8�ݭ����q?�_�޶�      <   �  x�Ŗ��� ���~�d��n|ɥ�V�^*����YcW��<L�=��bl��ī��J��L ��3��1$_ke3IC�"FX��dI)��P���ئ=}A���Q�~�����GDV)c)!�l:C�4Y-*o"r]c������Z>s�*�P���O��|DRJ�7���b������oJ��S�����|O�>��ԕ��&Q��ԯ�_�<3���1�=6������\υ>�K��S�I�ͼtz'��yή=v=K�������o�`Ɔu��e�C��Ә4d�#�r���g�-�zz<�,A]�m%K����t�h7{�lx1���a�	����<ߙ��D��Eȥ�j��7.�M Ϋ���L�浖V��B�pۓ ɇ~�4i�v�#W�5����[�46���;�_D�(���9�Ϩ.wR�m��n��Ҫ�gA$	���_�ᭋ����u���B��������P�i2���.��C����ث��JiO�ۭ��bo��NP�r��MprXsr��`#G�Q�A-�c�@�����
i�Շ�����`Tj��n���Vn��8
}��X�k=���H����3'�6B
���A}Q�4\Qs�d���&7;H�>��sE��'�|%?�a��Š6P      >   �  x���Mn�0���Sp�,R����UP �@��j`�I���P]���u�g��m�'񛙧G)����E�/
�g�3�3�
�3�V3D	�B=@9�(`j��@�9f��Ȯ�l�#�v�as���-���I룬�9P`�闱�����u�PF��(����ɡ�fܪH�2�rQ$�:�;������/۰u�OG�Ci;��o��w^S8��(;�f�x��ɚ}��h�w�ɿ�&�фEy���)��6l{h�3<H��+��e�^D��"�&P�|�� v'��aeq�$�}��*�÷�_)Ɠb�$G!��E�x���Tv=i≮&���o��-�pj]6=�ґ�h*�`��}Q쩅,��M�*�T�N���~U�.��dbE�9^Dq~xRB��+�����Yb���F#>��\A��ZSm$��/}M\n�!��h��      @   ;   x�3�L-.I�KI,�2�,(���/�/�2�L,ҙU�)@�	gbJnf^fqI����� /�      B      x������ � �      D   �  x�u�I��F�תSx�{9*3w.�f*F1��M� @�t��G�Y�P�lS��?V�?2"��ͣ��Lc{�.�<E��e,�mI���]�@�E�k�qy%���m�]�4��$]o���+���i,u�.��h�Cp�	��G��E�QL����Pc��i�w���`�f�<�:�Y6���~`�z�g~嫚S�_�Zx��6�����Ǔ1�����5\��=g��!-,%���Aɔ�R�$�wrw�����VI����Ǟ�7�n�m����L��uv�YN��T/�]�Γa�&����z�
'������վ�A-���Q�����L?Ñ��U��u�\�8�Q}gk�4{ݣ�Ć�?��q
����3W���E�
,�9Ą	@��NI�P�[G��=����'���H�؅�2��e�`�U5��ruS=!T���+k�Nw�2�Ѻ���[0
;-���0�b��}��3��=ڟ�g��h
s�3�3�̮��7��%�.��_\f����9���P�y�c�Ґ8� �>��Xe|cX� F߱ģ|�W�����y��MG��8�5y����L�.,�(Z�����=�T��=^0A'��R:K4gB"��
2��ܙ x�Or{�?��\�M�&��]��������$d�M�yV���?S�hJ�_ǧk֛�@\�GTaD�rD!|B���2e��B�MT�O�3Twzz�2Y�q.����I����Ϫ�C'��U[é��m�A��^a����Q'�����XB�1a��A�>��h��|�/z���T�ʥx7����u�lz;�à�M�4��f%v��a�^a ��-������XA� ^��J�?F���'oiZ)���#�v� ќ�'��ܯ&�����1�6��F�2L�r�Cw�b%l9��� Dڷ�B_@�$�\���Y*�w��F�,{<�]���l�A��R��+���K�/�x��\��V�$��W�#��=�`ޓ����&��X�A��+�^k �^妜��aʒ�A�g3�>��w/�si�8 ��@B1b ��#-����;G���߾��<��,M���o`=�h�D�0J�Q��u��f��mG	����R��Kg6�;p�{����(LUJ����Dh�@�����G�����G�����7a�     