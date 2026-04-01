<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Constancia de Docente</title>
    <style>
        @page {
            margin: 0;
            size: letter portrait;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #000;
        }

        .constancia-container {
            width: 100%;
            height: 1036px;
            /* <---- Ajusta este número para manipular a qué altura reposa el pie de página */
            position: relative;
            background-color: #fff;
        }

        /* Banner Superior e Inferior */
        .header-banner {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 80px;
            object-fit: contain;
        }

        .footer-banner {
            position: absolute;
            bottom: 0px;
            left: 0;
            width: 100%;
            height: 60px;
            z-index: 10;
        }

        /* Área de contenido de la constancia */
        .content-area {
            position: absolute;
            top: 150px;
            left: 10%;
            width: 80%;
            text-align: justify;
            z-index: 2;
        }

        .titulo-constancia {
            font-size: 18px;
            text-align: center;
            margin-bottom: 50px;
            text-transform: uppercase;
        }

        .texto-principal {
            font-size: 15px;
            line-height: 1.5;
            margin-bottom: 40px;
            text-indent: 50px;
        }

        .texto-fecha {
            font-size: 15px;
            margin-bottom: 40px;
            text-indent: 50px;
        }

        .texto-agradecimiento {
            font-size: 15px;
            text-align: center;
            margin-bottom: 60px;
        }

        .atentamente {
            font-size: 15px;
            text-align: center;
            margin-bottom: 110px;
        }

        /* Firmas flotantes abajo */
        .firmas-container {
            width: 100%;
            text-align: center;
        }

        .firma-box {
            display: inline-block;
            width: 45%;
            vertical-align: top;
            text-align: center;
        }

        .firma-linea {
            border-top: 1px solid #000;
            width: 80%;
            margin: 0 auto 5px auto;
        }

        .firma-nombre {
            font-size: 14px;
        }

        .firma-cargo {
            font-size: 13px;
        }

        /* Información de Contacto Inferior */
        .contacto-info {
            position: absolute;
            bottom: 100px;
            left: 10%;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            z-index: 3;
        }
    </style>
</head>

<body>
    <div class="constancia-container">

        <img src="<?php echo $data['bannerPath']; ?>" class="header-banner">

        <div class="content-area">
            <h1 class="titulo-constancia">CONSTANCIA</h1>

            <?php
            $nombre_curso = mb_strtoupper($data['nombre_curso'], 'UTF-8');
            $nombre_materia = mb_strtoupper($data['nombre_materia'], 'UTF-8');
            $nombre_docente = mb_strtoupper($data['nombre_docente'], 'UTF-8');
            $cedula = htmlspecialchars($data['cedula']);
            $lapso_academico = htmlspecialchars($data['lapso_academico']);
            ?>

            <p class="texto-principal">
                Por medio de la presente, hacemos constar que el/la ciudadano(a)
                <strong><?php echo htmlspecialchars($nombre_docente); ?></strong>, titular de la cédula de identidad N°
                V-<strong><?php echo $cedula; ?></strong>, se encuentra actualmente desempeñándose en calidad de
                <strong>Facilitador / Docente</strong> en la unidad curricular
                <strong><?php echo htmlspecialchars($nombre_materia); ?></strong>, correspondiente al <strong>Periodo
                    Académico N° <?php echo $lapso_academico; ?></strong> del programa formativo titulado
                <strong><?php echo htmlspecialchars($nombre_curso); ?></strong>, organizado por la <strong>Coordinación
                    de Formación Permanente</strong> de la Universidad Politécnica Territorial Agroindustrial del Estado
                Táchira.
            </p>

            <?php
            function formatearFechaConstanciaDocente()
            {
                $meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
                $d = date('d');
                $m = $meses[date('n') - 1];
                $y = date('Y');
                return "los {$d} días del mes de {$m} de {$y}";
            }
            ?>
            <p class="texto-fecha">
                La presente constancia se expide en San Cristóbal, <?php echo formatearFechaConstanciaDocente(); ?>.
            </p>

            <p class="texto-agradecimiento">
                Agradecemos su invaluable compromiso y dedicación con la formación de excelencia.
            </p>

            <p class="atentamente">Atentamente,</p>

            <div class="firmas-container">
                <div class="firma-box">
                    <div class="firma-linea"></div>
                    <div class="firma-nombre"><?php echo htmlspecialchars($data['nombre_coordinador']); ?></div>
                    <div class="firma-cargo">Coordinación de Formación Permanente</div>
                </div>
                <div class="firma-box">
                    <div class="firma-linea"></div>
                    <div class="firma-nombre">
                        <?php echo htmlspecialchars(isset($data['nombre_vicerrector']) ? $data['nombre_vicerrector'] : 'Vicerrectorado Académico'); ?>
                    </div>
                    <div class="firma-cargo">Vicerrectorado Académico</div>
                </div>
            </div>

        </div>
        <div class="contacto-info">
            Correo: <?php echo htmlspecialchars($data['correo_contacto']); ?><br>
            Teléfono: <?php echo htmlspecialchars($data['telefono_contacto']); ?>
        </div>

        <img src="<?php echo $data['footerPath']; ?>" class="footer-banner">
    </div>
</body>

</html>