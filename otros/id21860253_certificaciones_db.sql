PGDMP      :                |            certificaciones_DB    16.1    16.1 )    �           0    0    ENCODING    ENCODING        SET client_encoding = 'UTF8';
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
                        false    2            �            1259    16557    certificaciones    TABLE       CREATE TABLE cursos.certificaciones (
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
       cursos          postgres    false    222    7            �           0    0    cursos_id_curso_seq    SEQUENCE OWNED BY     K   ALTER SEQUENCE cursos.cursos_id_curso_seq OWNED BY cursos.cursos.id_curso;
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
       cursos          postgres    false    218    7            �           0    0    roles_id_rol_seq    SEQUENCE OWNED BY     E   ALTER SEQUENCE cursos.roles_id_rol_seq OWNED BY cursos.roles.id_rol;
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
          cursos          postgres    false    219            8           2604    16560     certificaciones id_certificacion    DEFAULT     �   ALTER TABLE ONLY cursos.certificaciones ALTER COLUMN id_certificacion SET DEFAULT nextval('cursos.certificaciones_id_certificacion_seq'::regclass);
 O   ALTER TABLE cursos.certificaciones ALTER COLUMN id_certificacion DROP DEFAULT;
       cursos          postgres    false    223    224    224            7           2604    16551    cursos id_curso    DEFAULT     r   ALTER TABLE ONLY cursos.cursos ALTER COLUMN id_curso SET DEFAULT nextval('cursos.cursos_id_curso_seq'::regclass);
 >   ALTER TABLE cursos.cursos ALTER COLUMN id_curso DROP DEFAULT;
       cursos          postgres    false    221    222    222            5           2604    16526    roles id_rol    DEFAULT     l   ALTER TABLE ONLY cursos.roles ALTER COLUMN id_rol SET DEFAULT nextval('cursos.roles_id_rol_seq'::regclass);
 ;   ALTER TABLE cursos.roles ALTER COLUMN id_rol DROP DEFAULT;
       cursos          postgres    false    218    217    218            6           2604    16533    usuarios id    DEFAULT     j   ALTER TABLE ONLY cursos.usuarios ALTER COLUMN id SET DEFAULT nextval('cursos.usuarios_id_seq'::regclass);
 :   ALTER TABLE cursos.usuarios ALTER COLUMN id DROP DEFAULT;
       cursos          postgres    false    220    219    220            �          0    16557    certificaciones 
   TABLE DATA           �   COPY cursos.certificaciones (id_certificacion, id_usuario, curso_id, valor_unico, completado, nota, fecha_inscripcion) FROM stdin;
    cursos          postgres    false    224   m0       �          0    16548    cursos 
   TABLE DATA           �   COPY cursos.cursos (id_curso, promotor, modalidad, nombre_curso, descripcion, duracion, periodo, tipo_evaluacion, tipo_curso, autorizacion, limite_inscripciones, estado) FROM stdin;
    cursos          postgres    false    222   71       �          0    16523    roles 
   TABLE DATA           3   COPY cursos.roles (id_rol, nombre_rol) FROM stdin;
    cursos          postgres    false    218   �2       �          0    16530    usuarios 
   TABLE DATA           m   COPY cursos.usuarios (id, nombre, apellido, correo, password, cedula, id_rol, token, confirmado) FROM stdin;
    cursos          postgres    false    220   3       �           0    0 $   certificaciones_id_certificacion_seq    SEQUENCE SET     S   SELECT pg_catalog.setval('cursos.certificaciones_id_certificacion_seq', 23, true);
          cursos          postgres    false    223            �           0    0    cursos_id_curso_seq    SEQUENCE SET     B   SELECT pg_catalog.setval('cursos.cursos_id_curso_seq', 23, true);
          cursos          postgres    false    221            �           0    0    roles_id_rol_seq    SEQUENCE SET     >   SELECT pg_catalog.setval('cursos.roles_id_rol_seq', 4, true);
          cursos          postgres    false    217            �           0    0    usuarios_id_seq    SEQUENCE SET     =   SELECT pg_catalog.setval('cursos.usuarios_id_seq', 9, true);
          cursos          postgres    false    219            E           2606    16562 $   certificaciones certificaciones_pkey 
   CONSTRAINT     p   ALTER TABLE ONLY cursos.certificaciones
    ADD CONSTRAINT certificaciones_pkey PRIMARY KEY (id_certificacion);
 N   ALTER TABLE ONLY cursos.certificaciones DROP CONSTRAINT certificaciones_pkey;
       cursos            postgres    false    224            G           2606    40961 /   certificaciones certificaciones_valor_unico_key 
   CONSTRAINT     q   ALTER TABLE ONLY cursos.certificaciones
    ADD CONSTRAINT certificaciones_valor_unico_key UNIQUE (valor_unico);
 Y   ALTER TABLE ONLY cursos.certificaciones DROP CONSTRAINT certificaciones_valor_unico_key;
       cursos            postgres    false    224            C           2606    16555    cursos cursos_pkey 
   CONSTRAINT     V   ALTER TABLE ONLY cursos.cursos
    ADD CONSTRAINT cursos_pkey PRIMARY KEY (id_curso);
 <   ALTER TABLE ONLY cursos.cursos DROP CONSTRAINT cursos_pkey;
       cursos            postgres    false    222            ;           2606    16528    roles roles_pkey 
   CONSTRAINT     R   ALTER TABLE ONLY cursos.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id_rol);
 :   ALTER TABLE ONLY cursos.roles DROP CONSTRAINT roles_pkey;
       cursos            postgres    false    218            =           2606    16541    usuarios usuarios_cedula_key 
   CONSTRAINT     Y   ALTER TABLE ONLY cursos.usuarios
    ADD CONSTRAINT usuarios_cedula_key UNIQUE (cedula);
 F   ALTER TABLE ONLY cursos.usuarios DROP CONSTRAINT usuarios_cedula_key;
       cursos            postgres    false    220            ?           2606    16539    usuarios usuarios_correo_key 
   CONSTRAINT     Y   ALTER TABLE ONLY cursos.usuarios
    ADD CONSTRAINT usuarios_correo_key UNIQUE (correo);
 F   ALTER TABLE ONLY cursos.usuarios DROP CONSTRAINT usuarios_correo_key;
       cursos            postgres    false    220            A           2606    16537    usuarios usuarios_pkey 
   CONSTRAINT     T   ALTER TABLE ONLY cursos.usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id);
 @   ALTER TABLE ONLY cursos.usuarios DROP CONSTRAINT usuarios_pkey;
       cursos            postgres    false    220            I           2606    57344 -   certificaciones certificaciones_id_curso_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY cursos.certificaciones
    ADD CONSTRAINT certificaciones_id_curso_fkey FOREIGN KEY (curso_id) REFERENCES cursos.cursos(id_curso);
 W   ALTER TABLE ONLY cursos.certificaciones DROP CONSTRAINT certificaciones_id_curso_fkey;
       cursos          postgres    false    224    222    4675            J           2606    16565 /   certificaciones certificaciones_id_usuario_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY cursos.certificaciones
    ADD CONSTRAINT certificaciones_id_usuario_fkey FOREIGN KEY (id_usuario) REFERENCES cursos.usuarios(id);
 Y   ALTER TABLE ONLY cursos.certificaciones DROP CONSTRAINT certificaciones_id_usuario_fkey;
       cursos          postgres    false    224    4673    220            H           2606    16542    usuarios usuarios_id_rol_fkey    FK CONSTRAINT        ALTER TABLE ONLY cursos.usuarios
    ADD CONSTRAINT usuarios_id_rol_fkey FOREIGN KEY (id_rol) REFERENCES cursos.roles(id_rol);
 G   ALTER TABLE ONLY cursos.usuarios DROP CONSTRAINT usuarios_id_rol_fkey;
       cursos          postgres    false    218    220    4667            �   �   x�=��m1D��*� '0`��
���K�_�"e5#$��r�ܥbn�2B]���qG�#wqA����RP`X}z��q�$)0�׾y0ͻ=��A�T�l�X9u�bFmu7ra��vJ�<'y�F�*��J|Y����\~�9=M���y�Ė˓��8�Ш	rC�/��ڛ�%:�������m�@      �   ~  x�mRKn�0]ON�P�N(t��؁�MWl��WI���� �z�\��@�P$o<c���Ka�F!�q-��]�0S��/ܣ͌j�e�#7,�{H��`A�"qA��Z�M�-�1�WQ�	NZYg��筱� �G۝*�!̨[S��R`��h}#1NN5:8x�!b!E.��e�B�+�2][Y�ʰ���\� ����^�����e,��/�ɪ;:Rj���8� �ih�{#�7¯lg#��+��A�+U�sL��4��ҰY�Ʃ����)�iKb"��^h�����dꍾ�d�U�{����A��Z��� ��W�5D�!c���ɆA؞���%-�� k���|^�k�v��Eo�T�U��%�1���=��%��?UJ��      �   ;   x�3�L-.I�KI,�2�,(���/�/�2�L,ҙU�)@�	gbJnf^fqI����� /�      �   F  x�}��N�0��Ϟ��vc�� H4&"�h0��l-s���������A�d&�����;���)uOZ�"��#d�1�`$�XmJI2]A�Uc��_�@#'>����0V�h�BY*$�3}��B�(C�Ka4�hu��r6�%��}�!8�H�mQC�+Ki�I��
���{��1:��R`�ZCΈ�v5;]%� 1��Z:D+��������-*{�"�$�V���]��Lh���w���a��ޚ�>�����H6�� �<���1\Lw�ڿU���\���&Y3��lԭ��S�bq��4�+��"�dA��G.m�%�<�����     