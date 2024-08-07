PGDMP      8                |            certificaciones_DB    16.1    16.1 2    �           0    0    ENCODING    ENCODING        SET client_encoding = 'UTF8';
                      false            �           0    0 
   STDSTRINGS 
   STDSTRINGS     (   SET standard_conforming_strings = 'on';
                      false            �           0    0 
   SEARCHPATH 
   SEARCHPATH     8   SELECT pg_catalog.set_config('search_path', '', false);
                      false            �           1262    16398    certificaciones_DB    DATABASE     �   CREATE DATABASE "certificaciones_DB" WITH TEMPLATE = template0 ENCODING = 'UTF8' LOCALE_PROVIDER = libc LOCALE = 'Spanish_Spain.1252';
 $   DROP DATABASE "certificaciones_DB";
                postgres    false                        2615    16399    cursos    SCHEMA        CREATE SCHEMA cursos;
    DROP SCHEMA cursos;
                postgres    false                        3079    16495 	   uuid-ossp 	   EXTENSION     ?   CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA public;
    DROP EXTENSION "uuid-ossp";
                   false            �           0    0    EXTENSION "uuid-ossp"    COMMENT     W   COMMENT ON EXTENSION "uuid-ossp" IS 'generate universally unique identifiers (UUIDs)';
                        false    2            �            1255    65565 )   registrar_actualizacion_certificaciones()    FUNCTION     S  CREATE FUNCTION public.registrar_actualizacion_certificaciones() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    INSERT INTO cursos.auditoria (usuario, accion, tabla_afectada, fecha, dato_previo, dato_modificado)
    VALUES (current_user, 'ACTUALIZACION', 'certificaciones', now(), OLD::TEXT, NEW::TEXT);
    RETURN NEW;
END;
$$;
 @   DROP FUNCTION public.registrar_actualizacion_certificaciones();
       public          postgres    false            �            1259    65556 	   auditoria    TABLE     J  CREATE TABLE cursos.auditoria (
    id_auditoria integer NOT NULL,
    usuario character varying(255) NOT NULL,
    accion character varying(20) NOT NULL,
    tabla_afectada character varying(50) NOT NULL,
    fecha timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    dato_previo text,
    dato_modificado text
);
    DROP TABLE cursos.auditoria;
       cursos         heap    postgres    false    7            �            1259    65555    auditoria_id_auditoria_seq    SEQUENCE     �   CREATE SEQUENCE cursos.auditoria_id_auditoria_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 1   DROP SEQUENCE cursos.auditoria_id_auditoria_seq;
       cursos          postgres    false    226    7            �           0    0    auditoria_id_auditoria_seq    SEQUENCE OWNED BY     Y   ALTER SEQUENCE cursos.auditoria_id_auditoria_seq OWNED BY cursos.auditoria.id_auditoria;
          cursos          postgres    false    225            �            1259    16557    certificaciones    TABLE       CREATE TABLE cursos.certificaciones (
    id_certificacion integer NOT NULL,
    id_usuario integer,
    curso_id integer,
    valor_unico character varying,
    completado boolean DEFAULT false,
    nota integer,
    fecha_inscripcion timestamp without time zone
);
 #   DROP TABLE cursos.certificaciones;
       cursos         heap    postgres    false    7            �            1259    16556 $   certificaciones_id_certificacion_seq    SEQUENCE     �   CREATE SEQUENCE cursos.certificaciones_id_certificacion_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 ;   DROP SEQUENCE cursos.certificaciones_id_certificacion_seq;
       cursos          postgres    false    224    7            �           0    0 $   certificaciones_id_certificacion_seq    SEQUENCE OWNED BY     m   ALTER SEQUENCE cursos.certificaciones_id_certificacion_seq OWNED BY cursos.certificaciones.id_certificacion;
          cursos          postgres    false    223            �            1259    16548    cursos    TABLE     �  CREATE TABLE cursos.cursos (
    id_curso integer NOT NULL,
    promotor character varying(255),
    modalidad character varying(255),
    nombre_curso character varying(255),
    descripcion text,
    duracion character varying,
    periodo date,
    tipo_evaluacion boolean,
    tipo_curso character varying(255),
    autorizacion character varying(255),
    limite_inscripciones integer,
    estado boolean
);
    DROP TABLE cursos.cursos;
       cursos         heap    postgres    false    7            �            1259    16547    cursos_id_curso_seq    SEQUENCE     �   CREATE SEQUENCE cursos.cursos_id_curso_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 *   DROP SEQUENCE cursos.cursos_id_curso_seq;
       cursos          postgres    false    7    222            �           0    0    cursos_id_curso_seq    SEQUENCE OWNED BY     K   ALTER SEQUENCE cursos.cursos_id_curso_seq OWNED BY cursos.cursos.id_curso;
          cursos          postgres    false    221            �            1259    16523    roles    TABLE     b   CREATE TABLE cursos.roles (
    id_rol integer NOT NULL,
    nombre_rol character varying(100)
);
    DROP TABLE cursos.roles;
       cursos         heap    postgres    false    7            �            1259    16522    roles_id_rol_seq    SEQUENCE     �   CREATE SEQUENCE cursos.roles_id_rol_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 '   DROP SEQUENCE cursos.roles_id_rol_seq;
       cursos          postgres    false    7    218            �           0    0    roles_id_rol_seq    SEQUENCE OWNED BY     E   ALTER SEQUENCE cursos.roles_id_rol_seq OWNED BY cursos.roles.id_rol;
          cursos          postgres    false    217            �            1259    16530    usuarios    TABLE     )  CREATE TABLE cursos.usuarios (
    id integer NOT NULL,
    nombre character varying(100),
    apellido character varying(100),
    correo character varying(100),
    password text,
    cedula character varying(100),
    id_rol integer,
    token character varying(255),
    confirmado boolean
);
    DROP TABLE cursos.usuarios;
       cursos         heap    postgres    false    7            �            1259    16529    usuarios_id_seq    SEQUENCE     �   CREATE SEQUENCE cursos.usuarios_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 &   DROP SEQUENCE cursos.usuarios_id_seq;
       cursos          postgres    false    7    220            �           0    0    usuarios_id_seq    SEQUENCE OWNED BY     C   ALTER SEQUENCE cursos.usuarios_id_seq OWNED BY cursos.usuarios.id;
          cursos          postgres    false    219            @           2604    65559    auditoria id_auditoria    DEFAULT     �   ALTER TABLE ONLY cursos.auditoria ALTER COLUMN id_auditoria SET DEFAULT nextval('cursos.auditoria_id_auditoria_seq'::regclass);
 E   ALTER TABLE cursos.auditoria ALTER COLUMN id_auditoria DROP DEFAULT;
       cursos          postgres    false    226    225    226            >           2604    16560     certificaciones id_certificacion    DEFAULT     �   ALTER TABLE ONLY cursos.certificaciones ALTER COLUMN id_certificacion SET DEFAULT nextval('cursos.certificaciones_id_certificacion_seq'::regclass);
 O   ALTER TABLE cursos.certificaciones ALTER COLUMN id_certificacion DROP DEFAULT;
       cursos          postgres    false    223    224    224            =           2604    16551    cursos id_curso    DEFAULT     r   ALTER TABLE ONLY cursos.cursos ALTER COLUMN id_curso SET DEFAULT nextval('cursos.cursos_id_curso_seq'::regclass);
 >   ALTER TABLE cursos.cursos ALTER COLUMN id_curso DROP DEFAULT;
       cursos          postgres    false    221    222    222            ;           2604    16526    roles id_rol    DEFAULT     l   ALTER TABLE ONLY cursos.roles ALTER COLUMN id_rol SET DEFAULT nextval('cursos.roles_id_rol_seq'::regclass);
 ;   ALTER TABLE cursos.roles ALTER COLUMN id_rol DROP DEFAULT;
       cursos          postgres    false    218    217    218            <           2604    16533    usuarios id    DEFAULT     j   ALTER TABLE ONLY cursos.usuarios ALTER COLUMN id SET DEFAULT nextval('cursos.usuarios_id_seq'::regclass);
 :   ALTER TABLE cursos.usuarios ALTER COLUMN id DROP DEFAULT;
       cursos          postgres    false    220    219    220            �          0    65556 	   auditoria 
   TABLE DATA           w   COPY cursos.auditoria (id_auditoria, usuario, accion, tabla_afectada, fecha, dato_previo, dato_modificado) FROM stdin;
    cursos          postgres    false    226    =       �          0    16557    certificaciones 
   TABLE DATA           �   COPY cursos.certificaciones (id_certificacion, id_usuario, curso_id, valor_unico, completado, nota, fecha_inscripcion) FROM stdin;
    cursos          postgres    false    224   �=       �          0    16548    cursos 
   TABLE DATA           �   COPY cursos.cursos (id_curso, promotor, modalidad, nombre_curso, descripcion, duracion, periodo, tipo_evaluacion, tipo_curso, autorizacion, limite_inscripciones, estado) FROM stdin;
    cursos          postgres    false    222   F@       �          0    16523    roles 
   TABLE DATA           3   COPY cursos.roles (id_rol, nombre_rol) FROM stdin;
    cursos          postgres    false    218   B       �          0    16530    usuarios 
   TABLE DATA           m   COPY cursos.usuarios (id, nombre, apellido, correo, password, cedula, id_rol, token, confirmado) FROM stdin;
    cursos          postgres    false    220   iB       �           0    0    auditoria_id_auditoria_seq    SEQUENCE SET     H   SELECT pg_catalog.setval('cursos.auditoria_id_auditoria_seq', 2, true);
          cursos          postgres    false    225            �           0    0 $   certificaciones_id_certificacion_seq    SEQUENCE SET     S   SELECT pg_catalog.setval('cursos.certificaciones_id_certificacion_seq', 52, true);
          cursos          postgres    false    223            �           0    0    cursos_id_curso_seq    SEQUENCE SET     B   SELECT pg_catalog.setval('cursos.cursos_id_curso_seq', 37, true);
          cursos          postgres    false    221            �           0    0    roles_id_rol_seq    SEQUENCE SET     >   SELECT pg_catalog.setval('cursos.roles_id_rol_seq', 4, true);
          cursos          postgres    false    217            �           0    0    usuarios_id_seq    SEQUENCE SET     >   SELECT pg_catalog.setval('cursos.usuarios_id_seq', 21, true);
          cursos          postgres    false    219            Q           2606    65564    auditoria auditoria_pkey 
   CONSTRAINT     `   ALTER TABLE ONLY cursos.auditoria
    ADD CONSTRAINT auditoria_pkey PRIMARY KEY (id_auditoria);
 B   ALTER TABLE ONLY cursos.auditoria DROP CONSTRAINT auditoria_pkey;
       cursos            postgres    false    226            M           2606    16562 $   certificaciones certificaciones_pkey 
   CONSTRAINT     p   ALTER TABLE ONLY cursos.certificaciones
    ADD CONSTRAINT certificaciones_pkey PRIMARY KEY (id_certificacion);
 N   ALTER TABLE ONLY cursos.certificaciones DROP CONSTRAINT certificaciones_pkey;
       cursos            postgres    false    224            O           2606    40961 /   certificaciones certificaciones_valor_unico_key 
   CONSTRAINT     q   ALTER TABLE ONLY cursos.certificaciones
    ADD CONSTRAINT certificaciones_valor_unico_key UNIQUE (valor_unico);
 Y   ALTER TABLE ONLY cursos.certificaciones DROP CONSTRAINT certificaciones_valor_unico_key;
       cursos            postgres    false    224            K           2606    16555    cursos cursos_pkey 
   CONSTRAINT     V   ALTER TABLE ONLY cursos.cursos
    ADD CONSTRAINT cursos_pkey PRIMARY KEY (id_curso);
 <   ALTER TABLE ONLY cursos.cursos DROP CONSTRAINT cursos_pkey;
       cursos            postgres    false    222            C           2606    16528    roles roles_pkey 
   CONSTRAINT     R   ALTER TABLE ONLY cursos.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id_rol);
 :   ALTER TABLE ONLY cursos.roles DROP CONSTRAINT roles_pkey;
       cursos            postgres    false    218            E           2606    16541    usuarios usuarios_cedula_key 
   CONSTRAINT     Y   ALTER TABLE ONLY cursos.usuarios
    ADD CONSTRAINT usuarios_cedula_key UNIQUE (cedula);
 F   ALTER TABLE ONLY cursos.usuarios DROP CONSTRAINT usuarios_cedula_key;
       cursos            postgres    false    220            G           2606    16539    usuarios usuarios_correo_key 
   CONSTRAINT     Y   ALTER TABLE ONLY cursos.usuarios
    ADD CONSTRAINT usuarios_correo_key UNIQUE (correo);
 F   ALTER TABLE ONLY cursos.usuarios DROP CONSTRAINT usuarios_correo_key;
       cursos            postgres    false    220            I           2606    16537    usuarios usuarios_pkey 
   CONSTRAINT     T   ALTER TABLE ONLY cursos.usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id);
 @   ALTER TABLE ONLY cursos.usuarios DROP CONSTRAINT usuarios_pkey;
       cursos            postgres    false    220            U           2620    65566 %   certificaciones certificaciones_audit    TRIGGER     �   CREATE TRIGGER certificaciones_audit AFTER UPDATE ON cursos.certificaciones FOR EACH ROW EXECUTE FUNCTION public.registrar_actualizacion_certificaciones();
 >   DROP TRIGGER certificaciones_audit ON cursos.certificaciones;
       cursos          postgres    false    237    224            S           2606    57344 -   certificaciones certificaciones_id_curso_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY cursos.certificaciones
    ADD CONSTRAINT certificaciones_id_curso_fkey FOREIGN KEY (curso_id) REFERENCES cursos.cursos(id_curso);
 W   ALTER TABLE ONLY cursos.certificaciones DROP CONSTRAINT certificaciones_id_curso_fkey;
       cursos          postgres    false    224    4683    222            T           2606    16565 /   certificaciones certificaciones_id_usuario_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY cursos.certificaciones
    ADD CONSTRAINT certificaciones_id_usuario_fkey FOREIGN KEY (id_usuario) REFERENCES cursos.usuarios(id);
 Y   ALTER TABLE ONLY cursos.certificaciones DROP CONSTRAINT certificaciones_id_usuario_fkey;
       cursos          postgres    false    4681    224    220            R           2606    16542    usuarios usuarios_id_rol_fkey    FK CONSTRAINT        ALTER TABLE ONLY cursos.usuarios
    ADD CONSTRAINT usuarios_id_rol_fkey FOREIGN KEY (id_rol) REFERENCES cursos.roles(id_rol);
 G   ALTER TABLE ONLY cursos.usuarios DROP CONSTRAINT usuarios_id_rol_fkey;
       cursos          postgres    false    220    218    4675            �   �   x���=��@��:�b����L��.X	�n�6b3�sGҨh�?Z[(,b}���bu>]��ŮU����ߛ]����Tj�i,�&O��J@~	��0ö#�-���z��H) ����4RLLy�6�����R�^P̔��Vb��+��$��7��ο�k�s���ǉ0 ��~'6���o꺾����      �   O  x�]Sɭ$7=�����]d�梍L����mH(@�Xo�#7��h�1�-�p�r���v��0��e��W�K�4l�F����!U2!�H���ac�*Ѧ���To)��ܷ�!	茌_%HR��=P"�pa��}i��1�C����O�A3-�ʺD���JܢէN��o���>�o��/��Ӷ(:�.;V��T��XH)79�cjFQ>v�t�<(��p����em������R��t��0*J��(�~I���M�8�V'W�M�J^�!ɊO-�E��8XL��.�4?�,�?Hh/�G��x��]�?����fYꓗ�H�#����YM�)�6'��uv�\CΎ���KB~!?"���D\.-E�QD�<9�7�nv��r�}�d�G֢� <�cLHt�����B{��AI�K+O�Jg߽'�}�=VP�=���3b���Hd)�9�����F�$�H?@7	+k�|"��lz�L*�]�����k���m��\�\ 8�k��M~��z��ۃ�-Nq���h������+t�s n߱)��:�e���l%"��w>��������QD/�~����o��f      �   �  x�m�Ar�0E��)t�Pc�LM�w�d6��F���H"�'�)|��1��TQ��BO�5�d	���W�
��R���
�:X ��oҒP١u�[�Z 4�����P�(gB�c��y+ʛʊZ�h�G��l#�3��#���,��`�����u&��V����8���xz���#���	B^đ���˳����͛�\Ο�z�Wh��T��	��J�93T�������G��.'�\�)�s�1��l��ӋzS�r������}��J�g
`w�J1���t8jǑ5��Z91W<HLg
���"��A��������S|�
K��䓭����(��փpfh�=�E��v���\z�ס3�
���2�z�u�����(���,��}�$(a;o��(�c����VG�l"Ԭ�Y����=X�f��b�/�9Z���]�$� �      �   ;   x�3�L-.I�KI,�2�,(���/�/�2�L,ҙU�)@�	gbJnf^fqI����� /�      �   �  x�}T�r�8}��ώ����lBR�"Y2��6�����FP�3LF�*��*Kg����dֺ�%���}���*��5������R^B���*��|m�q�(˸������F����$F�4���q ,��b��b�т�B�a�#�!����!���x�ғ^���6�]����|�>aEY�+����[3���
���_/�i ��y6i�V��-�V�ٝ��T�v`��睇�{���}���C�jN�,OGr�?ʽa�1%�,�g�F�!H�1D	A�@�syJ��髬�R��B)yW��gtX�ϋd2z>](E�W���6	�$�W��h��f��C9/E�Xg����r�!�����&&5�/=��5_y]~���Z+{Vu��̲�ߠ.?�?���7�Z9�MA3�����Nup�+Ћ���7�g[��� �6�h���*ۂaf�dV����Aq���ЙT4�l�k7�Vusk/��O�^N��:��r6lm�͉N��f�G�������S+��wzʎ^�b�C���Pi�FXBG�PH;l��J�IS�v��}bo��w�Ju�G;ދ� jwAV���e@'��v	{ir�3��m��Zb]ԮS���a0��$�1.��Xe���(ȅ���50�I�,�"�����[8O��n6<���������bQ�� �*L^;��|b�=�A3�¡��m����4�Z��V�#�$unG�
JcL�������E3�<,����4[��8}�rX��1�ۑ�H�߯�<��#��˖���(�	��;��QA��Rˍ��W��j4�Y	
�N�S]��x����M"����~{̃v��v�>ks��)$������L�Wo>��l�� �pj�(�
"�}X�D�K�b�E�Q}}M̧E��:}|���ğz���U$٥�%t_u���y���8ܽ�x[d~�5���f6}]�c��,���a��#��o��")a����Dd��m)׺�
���n�     