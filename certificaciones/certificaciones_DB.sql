PGDMP                      |            certificaciones_DB    16.1    16.1 C               0    0    ENCODING    ENCODING        SET client_encoding = 'UTF8';
                      false                       0    0 
   STDSTRINGS 
   STDSTRINGS     (   SET standard_conforming_strings = 'on';
                      false                       0    0 
   SEARCHPATH 
   SEARCHPATH     8   SELECT pg_catalog.set_config('search_path', '', false);
                      false                       1262    16398    certificaciones_DB    DATABASE     �   CREATE DATABASE "certificaciones_DB" WITH TEMPLATE = template0 ENCODING = 'UTF8' LOCALE_PROVIDER = libc LOCALE = 'Spanish_Spain.1252';
 $   DROP DATABASE "certificaciones_DB";
                postgres    false                        2615    16399    cursos    SCHEMA        CREATE SCHEMA cursos;
    DROP SCHEMA cursos;
                postgres    false            �            1255    73877    cursos_audit()    FUNCTION     �  CREATE FUNCTION cursos.cursos_audit() RETURNS trigger
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
 %   DROP FUNCTION cursos.cursos_audit();
       cursos          postgres    false    7            �            1259    65556 	   auditoria    TABLE     A  CREATE TABLE cursos.auditoria (
    id_auditoria integer NOT NULL,
    usuario character varying(255),
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
       cursos          postgres    false    226    7                       0    0    auditoria_id_auditoria_seq    SEQUENCE OWNED BY     Y   ALTER SEQUENCE cursos.auditoria_id_auditoria_seq OWNED BY cursos.auditoria.id_auditoria;
          cursos          postgres    false    225            �            1259    16557    certificaciones    TABLE       CREATE TABLE cursos.certificaciones (
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
       cursos         heap    postgres    false    7            �            1259    16556 $   certificaciones_id_certificacion_seq    SEQUENCE     �   CREATE SEQUENCE cursos.certificaciones_id_certificacion_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 ;   DROP SEQUENCE cursos.certificaciones_id_certificacion_seq;
       cursos          postgres    false    7    224                       0    0 $   certificaciones_id_certificacion_seq    SEQUENCE OWNED BY     m   ALTER SEQUENCE cursos.certificaciones_id_certificacion_seq OWNED BY cursos.certificaciones.id_certificacion;
          cursos          postgres    false    223            �            1259    16548    cursos    TABLE     i  CREATE TABLE cursos.cursos (
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
       cursos         heap    postgres    false    7            �            1259    16547    cursos_id_curso_seq    SEQUENCE     �   CREATE SEQUENCE cursos.cursos_id_curso_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 *   DROP SEQUENCE cursos.cursos_id_curso_seq;
       cursos          postgres    false    222    7                       0    0    cursos_id_curso_seq    SEQUENCE OWNED BY     K   ALTER SEQUENCE cursos.cursos_id_curso_seq OWNED BY cursos.cursos.id_curso;
          cursos          postgres    false    221            �            1259    81930    modulos    TABLE     �   CREATE TABLE cursos.modulos (
    id_modulo integer NOT NULL,
    id_curso integer,
    nombre_modulo character varying(255),
    contenido text,
    numero integer,
    actividad character varying(255),
    instrumento character varying(255)
);
    DROP TABLE cursos.modulos;
       cursos         heap    postgres    false    7            �            1259    81929    cursos_modulos_id_modulo_seq    SEQUENCE     �   CREATE SEQUENCE cursos.cursos_modulos_id_modulo_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 3   DROP SEQUENCE cursos.cursos_modulos_id_modulo_seq;
       cursos          postgres    false    228    7                       0    0    cursos_modulos_id_modulo_seq    SEQUENCE OWNED BY     V   ALTER SEQUENCE cursos.cursos_modulos_id_modulo_seq OWNED BY cursos.modulos.id_modulo;
          cursos          postgres    false    227            �            1259    16523    roles    TABLE     b   CREATE TABLE cursos.roles (
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
       cursos          postgres    false    7    218                       0    0    roles_id_rol_seq    SEQUENCE OWNED BY     E   ALTER SEQUENCE cursos.roles_id_rol_seq OWNED BY cursos.roles.id_rol;
          cursos          postgres    false    217            �            1259    90129    usuario_documentos    TABLE     �   CREATE TABLE cursos.usuario_documentos (
    documento_id integer NOT NULL,
    usuario_id integer NOT NULL,
    documento_path character varying(255) NOT NULL,
    documento_type character varying(50) NOT NULL
);
 &   DROP TABLE cursos.usuario_documentos;
       cursos         heap    postgres    false    7            �            1259    90128 #   usuario_documentos_documento_id_seq    SEQUENCE     �   CREATE SEQUENCE cursos.usuario_documentos_documento_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 :   DROP SEQUENCE cursos.usuario_documentos_documento_id_seq;
       cursos          postgres    false    7    230                       0    0 #   usuario_documentos_documento_id_seq    SEQUENCE OWNED BY     k   ALTER SEQUENCE cursos.usuario_documentos_documento_id_seq OWNED BY cursos.usuario_documentos.documento_id;
          cursos          postgres    false    229            �            1259    16530    usuarios    TABLE     S  CREATE TABLE cursos.usuarios (
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
       cursos         heap    postgres    false    7            �            1259    16529    usuarios_id_seq    SEQUENCE     �   CREATE SEQUENCE cursos.usuarios_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 &   DROP SEQUENCE cursos.usuarios_id_seq;
       cursos          postgres    false    220    7                       0    0    usuarios_id_seq    SEQUENCE OWNED BY     C   ALTER SEQUENCE cursos.usuarios_id_seq OWNED BY cursos.usuarios.id;
          cursos          postgres    false    219            K           2604    65559    auditoria id_auditoria    DEFAULT     �   ALTER TABLE ONLY cursos.auditoria ALTER COLUMN id_auditoria SET DEFAULT nextval('cursos.auditoria_id_auditoria_seq'::regclass);
 E   ALTER TABLE cursos.auditoria ALTER COLUMN id_auditoria DROP DEFAULT;
       cursos          postgres    false    226    225    226            I           2604    16560     certificaciones id_certificacion    DEFAULT     �   ALTER TABLE ONLY cursos.certificaciones ALTER COLUMN id_certificacion SET DEFAULT nextval('cursos.certificaciones_id_certificacion_seq'::regclass);
 O   ALTER TABLE cursos.certificaciones ALTER COLUMN id_certificacion DROP DEFAULT;
       cursos          postgres    false    223    224    224            H           2604    16551    cursos id_curso    DEFAULT     r   ALTER TABLE ONLY cursos.cursos ALTER COLUMN id_curso SET DEFAULT nextval('cursos.cursos_id_curso_seq'::regclass);
 >   ALTER TABLE cursos.cursos ALTER COLUMN id_curso DROP DEFAULT;
       cursos          postgres    false    222    221    222            M           2604    81933    modulos id_modulo    DEFAULT     }   ALTER TABLE ONLY cursos.modulos ALTER COLUMN id_modulo SET DEFAULT nextval('cursos.cursos_modulos_id_modulo_seq'::regclass);
 @   ALTER TABLE cursos.modulos ALTER COLUMN id_modulo DROP DEFAULT;
       cursos          postgres    false    228    227    228            F           2604    16526    roles id_rol    DEFAULT     l   ALTER TABLE ONLY cursos.roles ALTER COLUMN id_rol SET DEFAULT nextval('cursos.roles_id_rol_seq'::regclass);
 ;   ALTER TABLE cursos.roles ALTER COLUMN id_rol DROP DEFAULT;
       cursos          postgres    false    218    217    218            N           2604    90132    usuario_documentos documento_id    DEFAULT     �   ALTER TABLE ONLY cursos.usuario_documentos ALTER COLUMN documento_id SET DEFAULT nextval('cursos.usuario_documentos_documento_id_seq'::regclass);
 N   ALTER TABLE cursos.usuario_documentos ALTER COLUMN documento_id DROP DEFAULT;
       cursos          postgres    false    230    229    230            G           2604    16533    usuarios id    DEFAULT     j   ALTER TABLE ONLY cursos.usuarios ALTER COLUMN id SET DEFAULT nextval('cursos.usuarios_id_seq'::regclass);
 :   ALTER TABLE cursos.usuarios ALTER COLUMN id DROP DEFAULT;
       cursos          postgres    false    220    219    220                      0    65556 	   auditoria 
   TABLE DATA                 cursos          postgres    false    226   �S                 0    16557    certificaciones 
   TABLE DATA                 cursos          postgres    false    224   �k                  0    16548    cursos 
   TABLE DATA                 cursos          postgres    false    222   �p                 0    81930    modulos 
   TABLE DATA                 cursos          postgres    false    228   �t       �          0    16523    roles 
   TABLE DATA                 cursos          postgres    false    218   �u                 0    90129    usuario_documentos 
   TABLE DATA                 cursos          postgres    false    230   kv       �          0    16530    usuarios 
   TABLE DATA                 cursos          postgres    false    220   �v                  0    0    auditoria_id_auditoria_seq    SEQUENCE SET     J   SELECT pg_catalog.setval('cursos.auditoria_id_auditoria_seq', 163, true);
          cursos          postgres    false    225                       0    0 $   certificaciones_id_certificacion_seq    SEQUENCE SET     S   SELECT pg_catalog.setval('cursos.certificaciones_id_certificacion_seq', 68, true);
          cursos          postgres    false    223                       0    0    cursos_id_curso_seq    SEQUENCE SET     B   SELECT pg_catalog.setval('cursos.cursos_id_curso_seq', 49, true);
          cursos          postgres    false    221                       0    0    cursos_modulos_id_modulo_seq    SEQUENCE SET     K   SELECT pg_catalog.setval('cursos.cursos_modulos_id_modulo_seq', 11, true);
          cursos          postgres    false    227                       0    0    roles_id_rol_seq    SEQUENCE SET     >   SELECT pg_catalog.setval('cursos.roles_id_rol_seq', 4, true);
          cursos          postgres    false    217                       0    0 #   usuario_documentos_documento_id_seq    SEQUENCE SET     R   SELECT pg_catalog.setval('cursos.usuario_documentos_documento_id_seq', 1, false);
          cursos          postgres    false    229                       0    0    usuarios_id_seq    SEQUENCE SET     >   SELECT pg_catalog.setval('cursos.usuarios_id_seq', 26, true);
          cursos          postgres    false    219            ^           2606    65564    auditoria auditoria_pkey 
   CONSTRAINT     `   ALTER TABLE ONLY cursos.auditoria
    ADD CONSTRAINT auditoria_pkey PRIMARY KEY (id_auditoria);
 B   ALTER TABLE ONLY cursos.auditoria DROP CONSTRAINT auditoria_pkey;
       cursos            postgres    false    226            Z           2606    16562 $   certificaciones certificaciones_pkey 
   CONSTRAINT     p   ALTER TABLE ONLY cursos.certificaciones
    ADD CONSTRAINT certificaciones_pkey PRIMARY KEY (id_certificacion);
 N   ALTER TABLE ONLY cursos.certificaciones DROP CONSTRAINT certificaciones_pkey;
       cursos            postgres    false    224            \           2606    40961 /   certificaciones certificaciones_valor_unico_key 
   CONSTRAINT     q   ALTER TABLE ONLY cursos.certificaciones
    ADD CONSTRAINT certificaciones_valor_unico_key UNIQUE (valor_unico);
 Y   ALTER TABLE ONLY cursos.certificaciones DROP CONSTRAINT certificaciones_valor_unico_key;
       cursos            postgres    false    224            `           2606    81937    modulos cursos_modulos_pkey 
   CONSTRAINT     `   ALTER TABLE ONLY cursos.modulos
    ADD CONSTRAINT cursos_modulos_pkey PRIMARY KEY (id_modulo);
 E   ALTER TABLE ONLY cursos.modulos DROP CONSTRAINT cursos_modulos_pkey;
       cursos            postgres    false    228            X           2606    16555    cursos cursos_pkey 
   CONSTRAINT     V   ALTER TABLE ONLY cursos.cursos
    ADD CONSTRAINT cursos_pkey PRIMARY KEY (id_curso);
 <   ALTER TABLE ONLY cursos.cursos DROP CONSTRAINT cursos_pkey;
       cursos            postgres    false    222            P           2606    16528    roles roles_pkey 
   CONSTRAINT     R   ALTER TABLE ONLY cursos.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id_rol);
 :   ALTER TABLE ONLY cursos.roles DROP CONSTRAINT roles_pkey;
       cursos            postgres    false    218            b           2606    90134 *   usuario_documentos usuario_documentos_pkey 
   CONSTRAINT     r   ALTER TABLE ONLY cursos.usuario_documentos
    ADD CONSTRAINT usuario_documentos_pkey PRIMARY KEY (documento_id);
 T   ALTER TABLE ONLY cursos.usuario_documentos DROP CONSTRAINT usuario_documentos_pkey;
       cursos            postgres    false    230            R           2606    16541    usuarios usuarios_cedula_key 
   CONSTRAINT     Y   ALTER TABLE ONLY cursos.usuarios
    ADD CONSTRAINT usuarios_cedula_key UNIQUE (cedula);
 F   ALTER TABLE ONLY cursos.usuarios DROP CONSTRAINT usuarios_cedula_key;
       cursos            postgres    false    220            T           2606    16539    usuarios usuarios_correo_key 
   CONSTRAINT     Y   ALTER TABLE ONLY cursos.usuarios
    ADD CONSTRAINT usuarios_correo_key UNIQUE (correo);
 F   ALTER TABLE ONLY cursos.usuarios DROP CONSTRAINT usuarios_correo_key;
       cursos            postgres    false    220            V           2606    16537    usuarios usuarios_pkey 
   CONSTRAINT     T   ALTER TABLE ONLY cursos.usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id);
 @   ALTER TABLE ONLY cursos.usuarios DROP CONSTRAINT usuarios_pkey;
       cursos            postgres    false    220            k           2620    73878 '   certificaciones certificaciones_trigger    TRIGGER     �   CREATE TRIGGER certificaciones_trigger AFTER INSERT OR DELETE OR UPDATE ON cursos.certificaciones FOR EACH ROW EXECUTE FUNCTION cursos.cursos_audit();
 @   DROP TRIGGER certificaciones_trigger ON cursos.certificaciones;
       cursos          postgres    false    241    224            j           2620    73879    cursos cursos_trigger    TRIGGER     �   CREATE TRIGGER cursos_trigger AFTER INSERT OR DELETE OR UPDATE ON cursos.cursos FOR EACH ROW EXECUTE FUNCTION cursos.cursos_audit();
 .   DROP TRIGGER cursos_trigger ON cursos.cursos;
       cursos          postgres    false    241    222            h           2620    73880    roles roles_trigger    TRIGGER     �   CREATE TRIGGER roles_trigger AFTER INSERT OR DELETE OR UPDATE ON cursos.roles FOR EACH ROW EXECUTE FUNCTION cursos.cursos_audit();
 ,   DROP TRIGGER roles_trigger ON cursos.roles;
       cursos          postgres    false    218    241            i           2620    73881    usuarios usuarios_trigger    TRIGGER     �   CREATE TRIGGER usuarios_trigger AFTER INSERT OR DELETE OR UPDATE ON cursos.usuarios FOR EACH ROW EXECUTE FUNCTION cursos.cursos_audit();
 2   DROP TRIGGER usuarios_trigger ON cursos.usuarios;
       cursos          postgres    false    220    241            d           2606    57344 -   certificaciones certificaciones_id_curso_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY cursos.certificaciones
    ADD CONSTRAINT certificaciones_id_curso_fkey FOREIGN KEY (curso_id) REFERENCES cursos.cursos(id_curso);
 W   ALTER TABLE ONLY cursos.certificaciones DROP CONSTRAINT certificaciones_id_curso_fkey;
       cursos          postgres    false    222    4696    224            e           2606    16565 /   certificaciones certificaciones_id_usuario_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY cursos.certificaciones
    ADD CONSTRAINT certificaciones_id_usuario_fkey FOREIGN KEY (id_usuario) REFERENCES cursos.usuarios(id);
 Y   ALTER TABLE ONLY cursos.certificaciones DROP CONSTRAINT certificaciones_id_usuario_fkey;
       cursos          postgres    false    224    220    4694            f           2606    81938 $   modulos cursos_modulos_id_curso_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY cursos.modulos
    ADD CONSTRAINT cursos_modulos_id_curso_fkey FOREIGN KEY (id_curso) REFERENCES cursos.cursos(id_curso);
 N   ALTER TABLE ONLY cursos.modulos DROP CONSTRAINT cursos_modulos_id_curso_fkey;
       cursos          postgres    false    4696    222    228            g           2606    90135 5   usuario_documentos usuario_documentos_usuario_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY cursos.usuario_documentos
    ADD CONSTRAINT usuario_documentos_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES cursos.usuarios(id);
 _   ALTER TABLE ONLY cursos.usuario_documentos DROP CONSTRAINT usuario_documentos_usuario_id_fkey;
       cursos          postgres    false    220    230    4694            c           2606    16542    usuarios usuarios_id_rol_fkey    FK CONSTRAINT        ALTER TABLE ONLY cursos.usuarios
    ADD CONSTRAINT usuarios_id_rol_fkey FOREIGN KEY (id_rol) REFERENCES cursos.roles(id_rol);
 G   ALTER TABLE ONLY cursos.usuarios DROP CONSTRAINT usuarios_id_rol_fkey;
       cursos          postgres    false    4688    220    218                  x��]�R;�}�� ����#j��L]��h�1w��2�R��m膾``b>�<����cG���@Wa6["��K��2�̔2�vs�������{[sv��uz��~��2s���N�J����:ɜ���i's}����پ��?��C���~�']w���㸓���5y�������w�~���?N:��A�����{��o?/4�nm�7����o�Ɔ��D�A���� �§�qH(KP&X�\ʂ��9J ˍՠB�I�Mn����,0f9d�p�b&@IgM�I2�1� ,��2N������>��9���\^�ܛ[ښ���[y������/c�A�$��G��a��?I
����K{��ը�뤩9J�7�N��jDU�9$]s��^���>86���v������)��ŗ����ך�˽���y����U���.���ml�׳����[�x���xh����\�U�Դ(к\2ZH�.��>՗�_ߙ�f�a[4IQ�<.�c����?^58K��� �J�bd[܇m� �����I�bd[z����3�@�8��e�~R<��<�J���ۘ�.F��HW��7�J�dQ����2%�hBR L���i�Lo�<��m�\�J"EQ�2�ؾou�s�k��N۷�̛��Μ9�z�	�dx#��~b;�`�u9$��w߬W�n:�,R��ʥ_C(r)��`,f�������s؉��,�`ާ��}D"Ð5��o��8q`��;����`��ֻ�_�?��y��^�{ݭ���D��U�Ӆ��zݭ�����[���۰���'�	�l�YRdVY�PE!��"ˍ΅"��*�:@������Rjb��{�.�M�����E�������Zd��oݵ����q۫���^_o5�-�l�����م��O��Ҡ��яǘZ�
GeA�3�Q-�ZZkotƋ�t���#��yED�5C&�F�|�k�kۖW������a�.8��p�5<o�u�}Oz���Ī�j8���#�AYn+/�:F�c@��DI�i`2S�QNCͳ�
��JY�L3Gh^0�r�9��{����s��H��TWt؀�yo�xe�T�����/��-< 5u�\
F�L)\��ΰ�3�8 �B�s�h�E�AYfE���w��N9Sh�ф3?���@����j1R�:Cen���yduH��
C��AF��Q��
� F�"�QZ< .t�"R���y1D��ᩐ p�FF��h��;<Lb�^n�D��C���園�A4RPX��x���0>����'o��j�#(�X�#=b�qb�U��G�d=i�B��Y���3����I��YNF����C��HSM��bm4Q�G�<��Lߜ
�T��`����?����w�Q��*Ĵ��˔�!qy�[Ej��ի���c)�\��`����!���n�o���t����M���'J��
�<+���e�mz5�<��b������S�]	*��,J�+�aVNL�Th�#�d��Αw,zy�;\�t����Vv�:0��2�ղ�}�+�XL�@� �#ȟ�gɈLVL�J?����W��N�`5gTKx��Y`��Lg�2hd��R��H�,0e-0Q��gy�^$s���,M�9�/�y���&�r����6�~Q�dU�&k�Đ}��E2g�Y�7� �AE�5c���ɼfU�T��b�"�3���� �%y.R���s&�U�(3K����E2g�Y���2��cʹƟU�_U2+�?~"�#&O�����Ĳ����D��{��`VvJ0)M����ː9̙KI����a�a�Y�CJ(2���&<��Sn�[�-E?�[�9��fV1]�(��BS�9�d��7]��R$t��r��J�aT������	��^���T#J<.��*Q6q㾿]����2?�%�r6�]λ�zz�u�Ӷn�ppl��a����Zg�ܳ�7GG�#,�G�g�WsɆ����P
��0���<�]"�y5�D���T�\1��0�j�L���r�_V�f�Y�C�e�FW\�����j���4����Vb�$8l� ��`�������fn_�!�	!�pZ�`C�X'G�V/9����27g�������¹~��"�?���m.}K;ݭ���a_�����v����ܸ���
�8�L(d^M�d *�J�e�ȳ,׹r��&B9٫:�P,=���n���S�B-4���z%�*��)�dJ��4!���(F�E�*%�iHae
�h���|ƺ��C�����҈��l�(	�h���na�'�(��`�����ɸWq"�<B���Q�����I9B*�D�x$���J�c©YP�RV��Z"xt�����m�u�q?=|s~�~w�ԵDs��d����b3={�lv2n�V�^ne�/�x�\�!�<���AeM��Q�#��o.�ʃ�_X�6xG/���-�/��ǯ=�_�\�j���Ռ��]�l}\��k�JN�j�t|�����hiW7�?|=_YC�'_�x0X�7ߴٮ�-?m��]�����Ƣ���_��A��q�R�j�f��o�VO�K�w��W��a�t�m�Ey�?Xɻ͍�/�o�b��ŧWk�o��_�p]� ��qѠ��:� ��.W"9q'nXz�#����zG}�����·?:���lioe��ʞ�g���'o�Z�{M���n2UPp?�g,(�yn-#�P����,V��-#�0�L%&C\�k�F!�X�Z<N_ؓ��!g��g�@�2��5)��*�E���jg?b�#�s>��N���.o,���:_���@�t��ԛ�;����knM-�����Ǐ�>�]�&�����������!�Gr��,�v{�⼀���-r�J�]�/͏G��Χ����߾_�(�n���%�ĵ*����c0�Q���jz�7bt�HRx[�f�pD��r��C&\�P��F+�u�LA�%HyN��΄���}�3�t����h�4(S�A��6�D�ENV̄�ix(��R(]�O�1��y��������ּo�mf��"[ln�/�_[�o�K��^�d��\�+'��旃_bT�v&Ә�P���H)ǚ��GA8�E�y��np.*B�k�hkyg����W���ˎkwZ�����+�t�xu��T�ѕ8ܵg��������װ>*�Q^Fg��B��3�<[��dCT5{���",���L ����;�g��ZR�q�e�`�%���eL��%r��M�	 ie�q4V���[-Fjq�J����"�;E�8��*:���t��g����f�����fd�x<��A�@V"��2� eJ�(�E�C˫�1�v�������	H��]LƢ�S7�Њ�O�匲Q�?�Ѭ���f���zx��K{������k��8P��[W�����k��]�|A�VlΏ���{����+�V��iV�V�靎���OEe�`6�2^H�v �8�0˰aF�\ZK��T��JdZ(�:�w�u��B:��@?<�̄��<��o7�i 'XV�ʆ��=L�H5*?��(F<����%/w�R��v:�!j�xC���h�sE�Q�r�-�6��"�Bf�"/�?8 F��K�Q���t��ʥ�)�<$��L�����<�GD1^�Ԥ�\���<�����<)=�tV�<<�U4S^�ɨ��B2A��D�d�#.4��~hȹ`���d�r�i}��C�Y�,ב\>��%ԗ�P}��I������q�G�s�r�r���G� ���2[)���M�<�2]�WxF�����d�r���r�`~j�����#��X�'h�byۄ��p	%�8M�goF���Iw�23~���WvvXLK��>���;�a}ruq�r�Y )��.R�\_�Y<%�߁����R-q���'������Y�P�k�3����tT���LV�̿���2߫{�l$ :�hy���찢���^"�J9��N�<�&���(jIe�ޫ+�gD��G2�)SD���K�X���`�gM�����=U�OB6ޙ�Rm*R��r\4j���T�ߟ���]?#��24QB;�����^Jh    5�hnZ@�@�q�`�Yr�����!/)�*�;Pu}x�Bx������Α�|�r�`��(�V�(/Ff�SZ�<�bTy�0�b;ڿҘ����F���̿ɯI,�ix���#x�O|����H(�Gq�X��Y�+���L�������X29@����O\�g�S|`?�C8C�����xJ$���~~<W9�x�g�`��Q�T4�ާ3�gƴ�5d~/F?������JM��D0�� Y�T�\�����b�rCb�I4�(˙%,˵�Xn3��"C^��P��&�#7{�e��ME��d��{ƈgsJ�#���i�� �t@�S��M@Xa�"S��D�hA��! Z"����"��rM��TA30�n2^ф���4o$)����="�1�&֖� %�]C���\�S��

�Νa$38p̲\{�)Gt�Ì2*xFs$Tl�
i:r�M�+���l�Gc�0��^����c��l�{�f��C�oNd��0��e�s�Q�s#1�h���B������Z��
P�QNM��h;ө&�Mq/��a		h6L�zLc�MYS6q.D��0KA�kS:OED�6%�D����2󦓿%��=�L
�[O55��"�B10�!��b��Y᤯�yB�66��8S�M��Eq�&N�+��<a��CV�&������~o�1��3����Xo0׏������{vLW�2-B%��3�gT�7l��M�LW8�m�i@�|އ��aWo�d���rc�)5��K$*2l��M�LW8�n�i�-ԋJ?=�+��N�{�R-)'�1�_��k��p@��i�(�:2l��M�LW8o~´�i%��p��6����t��(�!E*g�e�~zLW��aRV*�����~rLc��(s�I�|�Ȟ�W@�*T�
Lq�
�T�*��K��F��ey%g�T\z�%��}�-,|�oo�����T}��n5ߕ�w�[=;n���d۟m���\���;B+��3�(uL"g�)͔�r��<�W�!�j��Oi��d���+��~�U���ޫ�������}�p��k%Q�y[����
 �x���-">ts�FOp���R	�dާք�'�@��{�G��~������^}#A)M\��F��K��e�Qx�0w��<s�{U�ҿǉ�_s�X�)�GrbU��d��ō����W)�����SJ�@4l��24T
�G��t/����i�׷�����Y���i-��޷��������N�p�����w����j��B���vii�0T�0d�+�u<�TF�0�jUh���
6�d�;X�;q�-f)�mzH6��O%��T+��̶]�Wߡ�)����?���ǉ�zv"(yj�� }��2 $�j9Z���}��a��yWux�!k�q�G�X��8�N�I��C��(9����睑�oP�W@�3п��L]�l��#6���T��C�9iP�
Z��O�x�C<����_A�C���L=��?���*Y�\Ɗr�n={�y����?�)Q��M�%�x���Л2�,�9hZhK
���eJi�K�2�Jˑ��y81�V��Z�цY�S��c�H���E�P���zWJ9���͔�Fr���"�R1�0��`&<�Tey�a�(����4/���L@�so�H��x�bt��^�uP�g�S��2����)���@�����3�Dx	_��(�q�|��O�;^�"�G��J��\�'�\;���d�L^�8
�-�a��r����~�]|��+�|�ۙ%_��m��GR@��A�l��8y��n��uu�}t��k��VJD����'�b	�k�;�{?�N������J�h#��wVS-�x"�Ց^���;�H��#���R��B���v�矡B�ڢs�����TҨ4�^}��FZ-���Î-��
����oe٢�����^��S��Է3�� 96Q���J�i#�exJQ���0����1�����h�0�!8/Ɓ�}���lQ)#mȱl�d�j��T�ŕ�5�(�)���׊���~F���r�i~�����5Y���!1ϰ���O]������r-&g         �  x��W�nG��+�F����i�8N"@��X�U�gB��=���%K��䚅F��UMuU����w?]MgW�S}������~�p�������O���/϶�?y��w���鯮m;��?��]?������O�Cn����!o�����ps_��N��)�r�f������O�y;��QyXn�� ��Ds�Q�fh����c㟘?���t������7���������W�w����Տg?|����V��t�Us,��Lm�m@�k�rW�����9�"�G�<K2mN��DFƵ�E:��r�K�h(f�$a.	�F��J��O�C~�{\/b9�.�C��38T	�k�Jr?2����oiC@<#͈�^�xǒB��5�d:���@�4C���� eQ8�U��e"!�m��J5R
:e�q�����\��C?�cL���g�s����NH{I{��QH�����I�q�6T�\C�CSnM}��;u(IKag��ϨYz3��T�<!�E�w*�dU�ED�YD�8@l�$֨ړ˃!��J�Hj-�" ����#���wo��QtG�P���`;�Ѻd���@X��[�b��J���2S+Q��՞��:��H���^�(��k_t��	
A'+B�u�X0���&�\����1��H�Bi����D�o�RL:`�����-U���DJ�GYHJ^�WJAN$-�����;67q{6�Q�*Y��h��C�S�nZ��J#Jɴ���QH��w(�޾*��N��ld)T��^��̭"�'��T�m��*�����Z,]��.��W�Ӭ���^`O�s.��EQG�d����\��P	"���΢}P��Y�f�5X�љtu+
�^�(C-x��w�����4�������Ե����p��@)�,���T�D�y��ގ"�4VMB��#|jF6O@��$�2h���W=E>/��#�¾�w_�z�.� ��_4/���C��)D1�^&�*r���3����J5�'�d�}pUx�fP���K���*]�]�����b�^�����jk�ҿ�dI>j�:Z�rZ'���%y3*�0�<ݻ���:�6��ą�nE�꣺e�v`a�_l�z�.�&`�l]}^F��]|\␌)7�)�##����}d�߽*VA#u'��d��?�ǯ�4'��+�Y\Y��t���9����:�a�r���*ǐ)�*VY|o��x�r/�>��Js�*"�K�?1�l�v�"���ЫW I��2          �  x���n�0����H-J�}��j*���m�&^k�؝��av�O����j�1�Kmzb;'v~��縣����h4���8SZ��zƒ�3{h�d*�T=$d:W�,O��[�L�2��+9#�-I��	5��j[	U�U$W��/�,e�Θ�\��T�#aD�bN4���TD���o}~���`���w��6�G��Sa����g|+z�QU��t�ijm7 0p&E�3�������W�Y4��ppG.����ep�|�� ��	[q��`�=���p;�ɧ�����h:A���xtq�^M�dz�v4y��`�1>#�
���%�0�-Q)%
U���CLm���˒d�Fe�c7á�ca��MJ�Hw[4���|�Fm4���6��*u�/�Fk��J���P�}��x��4]@{ԨlP=�e������}J���1�p�E^��B��pE�y��tK�.��M�G^���B���܁$:�$i�@��ݒ9�s� v��
�<�.W�r�������"�7e�n���l�EA�g�z�ҵSc�M��h��D
%ɗV����Ω��^2}����:�J5�Y6�ۙ�k���ݬ)F^�]2<�B�A���z_HD�2�w�Wc�Xc�v9�Z��<��"u�������W����}k� ĭ���݊0�χl�+me#wE��t$ڎx�NP��Q�P�P��|"���Fq#A�v$(�=�@;j3��|��+P��/�aܠ��Uf��N�[{{���%��Z�2Ψ�Y�nV�#)zuv��6�f]�9iX��7��쒨|��]�1������̎�[�T��`�0�l���a��a��g>���D�I�S7^��vu���;�4��/4�,n��oA��Oq��T�Q�j�������b�7��==�؟�I�ޣ�A��۠n#��r��/�\��g�i)�{�W�B?l�������]��I���Ϛ���i?����3ܿ�         �   x�Փ]�0����N�fVJWa_�� ��P煐[l��oH�
݄^�����$�#�$��VR	5.��B����;۠㫵��"�Y]����ٳ��L�7N������o\�R�y)Fp^N������&�,��τ��#�J�D��@h��d�4������N�������{ޫ��+��W~��3 �#�aO� .�m��墉��k���Qs�/�e��j`�+      �   �   x��ͽ
�0@�Oq��P6'�U�lt��d�\�I���	��L�8tg��q�Hq���*gn���l���/� ղ��Ƥ��\� Z��^�
�P����_ᫌ?�<%�=�θ���^����̾�.���á(�R�u�         
   x���          �      x�՘ْ����)���#*�ے��kQlf���fB�dc���,ou��ؑ������v]@*��#�ϔ_F��t^y�튓�I�hi������x�����"���"|�8aK��$9���Ƒ"ݲ��/����S�nd�_�~�c�MW�/�{��mT^�E{V���Ju��IU��X|����?���r�wt��w#0S�bi5b9X��~^���m��.�rB�k�t������[���#(�:R6T[�p9#�: ���cHc�0G�.�B]v�S�h1��أJ�u/�y�eWF���2z�Ϸ����"K�y��apa[y��(wj^��S��.��O��1K���ˌ|o��Z �l�o_���C�~�:Ld�3A*���s7wq6:\�����S/j=���m/�:u��(`&��p�>�;_{I3��!2�q�;��A�0t�2 ��sl����>F�@�������}w��f��٫sܸõ��/��I���}I|m2��٩ͦnԣ��b��(u�B
���В��Ȓ���.~~*�^Z�˸d�Z��~Ss��a=��ۚ{���Qd�9��K/���9�͙��4�É����1�gi�L�6W�e7��ɸ� �fv&��a�Z�դr);/s��JH\(�)���T�Qײ,H!x���U$��)Û�c�>+�7y��{�ke��7�F��pWV�r\7?��M�����ZT��Mo��3E�{i�suk��7|��U\��EA<h���	+eb_����*��ƫxK���3<�l�M�t���h��ޟK��&�4����ލs���iA�,�@�j��AD�ĈX�I$���8x�.�`��/��k��$�|{����ּ饱97W��K��)&~����n��t`��Q�ԃ�x��%q��)�\%C��ҙe�ЀT�p�CbAt�E��0,��g�O�sE�����C7��R6z��C��(]U7f;?�<D�'�5�ۨ�m P�Vu`���4�4kͻ�Mw.I��^����3��1��֟icТL2Qb�qt ]
 0u��I��,�_��^��w�=S��똎��E'9GZ3���3�&��2�`>����ꮦ���f8�EC�2��#,�1��U��XAt�56?;�
��ȸ�������sj/[��λ�X���7^]��[��V�X�#�̺]F'9y޳�����q� ���� g�d!�\�1 ��w�0�j@�����F��$�r{�s6�yM����"Sj���Y�$������z���G���TQ�B٪wwTt�C(&��$uŎY¤2f[7O���>w���.@�3�go�}?�'2}�9�$�z�V��Z����n�fN96�»��g��P�/G�
�@B9���\X�J��L�ȃ�/��;�z	0�c>��z�[eVܚ�hp�j��2������Bn�ש�9�V���|�k �=ѐ�����Z;�j�����`ؕ�����Ǵ'f�cBe/�Q4��G�m��_�-5?�ԔJ�`C':�tʸ09\Y����WմZ����T�Jj�p����:�xL)�-
��A����!     