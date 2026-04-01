<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Certificado</title>
    <style>
        @font-face {
            font-family: 'Edwardian Script';
            src: url('<?php echo realpath(__DIR__ . '/../../public/assets/vendor/edwardianscriptitc.ttf'); ?>') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'Cambria';
            src: url('<?php echo realpath(__DIR__ . '/../../public/assets/vendor/Cambria-Font-For-Windows.ttf'); ?>') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @page {
            margin: 0px;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Cambria', serif;
            color: #000;
        }

        /* Marca de agua repetitiva en todas las hojas (Absolute) sin usar transform */
        .watermark-container {
            position: absolute;
            top: 15%;
            left: 0;
            width: 100%;
            text-align: center;
            z-index: -100;
            opacity: 0.15;
        }

        .watermark-container img {
            width: 450px;
        }

        .header-banner {
            width: 100%;
            height: 80px;
            object-fit: cover;
            margin-top: 5px;
        }

        .footer-banner {
            position: absolute;
            bottom: 0px;
            left: 0;
            width: 100%;
            height: auto;
            z-index: -1;
        }

        .content-wrap {
            padding: 0 40px;
            text-align: center;
        }

        .republic-titulos {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
            line-height: 1.0;
        }

        .otorga {
            font-size: 18px;
            margin-top: 30px;
        }

        .nombre-estudiante {
            font-family: 'Edwardian Script';
            font-size: 60px;
            color: rgb(173, 4, 4);
            margin: 10px 0;
            font-weight: normal;
            display: block;
            text-align: center;
        }

        .cedula {
            font-weight: bold;
            font-size: 16px;
        }

        .certificacion-texto {
            margin-top: 25px;
            font-size: 19px;
            line-height: 1.5;
            text-align: center;
            padding: 0 30px;
        }

        .fecha-expedicion {
            margin-top: 30px;
            font-size: 14px;
        }

        /* Firmas */
        .firmas-container {
            width: 100%;
            position: absolute;
        }

        .firma-box {
            position: absolute;
            width: 250px;
            text-align: center;
        }

        .firma-box-img-wrapper {
            height: 40px;
            text-align: center;
            overflow: visible;
            position: relative;
        }
        
        .firma-box img {
            max-height: 65px;
            max-width: 90%;
            position: absolute;
            bottom: 55px; /* Control de altura forzado */
            left: 35px;
            right: 0;
            margin: auto;
            z-index: 10;
        }

        .firma-nombre {
            font-weight: bold;
            font-size: 14px;
            margin: 0;
            line-height: 1.2;
        }

        .firma-cargo {
            font-size: 13px;
            font-weight: bold;
            margin: 0;
        }

        /* Ajuste de posiciones */
        .pos-p1-inf-izq {
            left: 40px;
            bottom: 120px;
        }

        .pos-p1-inf-cen {
            left: 50%;
            margin-left: -125px;
            bottom: 120px;
        }

        .pos-p1-inf-der {
            right: 40px;
            bottom: 120px;
        }

        .pos-p2-inf-izq {
            left: 40px;
            bottom: 60px;
        }

        .pos-p2-inf-cen {
            left: 50%;
            margin-left: -125px;
            bottom: 60px;
        }

        .pos-p2-inf-der {
            right: 40px;
            bottom: 60px;
        }


        .page-break {
            page-break-before: always;
        }

        /* Segunda pagina */
        .qr-layer {
            float: right;
            margin-top: 30px;
            margin-right: 40px;
            margin-left: 20px;
            margin-bottom: 20px;
        }

        .qr-layer img {
            width: 120px;
        }

        .titulo-contenido {
            font-size: 20px;
            font-weight: bold;
            margin-top: 80px;
            text-align: center;
        }

        .modulos-lista {
            margin: 40px;
            font-size: 16px;
            text-align: left;
        }

        .modulos-lista div {
            margin-bottom: 8px;
        }

        .texto-inferior {
            margin: 40px;
            font-size: 16px;
            text-align: left;
            line-height: 1.1;
            position: absolute;
            bottom: 150px;
            left: 0;
            right: 0;
        }

        .texto-inferior p {
            margin: 0;
        }
    </style>
</head>

<body>

    <!-- ================= PÁGINA 1 ================= -->
    <div style="position: relative; width: 100%; height: 735px;">

        <!-- Marca de agua FIJA -->
        <div class="watermark-container">
            <img src="<?php echo $data['imagePath']; ?>">
        </div>

        <img src="<?php echo $data['bannerPath']; ?>" class="header-banner">

        <div class="content-wrap">
            <p class="republic-titulos">REPÚBLICA BOLIVARIANA DE VENEZUELA</p>
            <p class="republic-titulos">MINISTERIO DEL PODER POPULAR PARA LA EDUCACIÓN UNIVERSITARIA</p>
            <p class="republic-titulos">UNIVERSIDAD POLITÉCNICA TERRITORIAL AGROINDUSTRIAL DEL ESTADO TÁCHIRA</p>

            <p class="otorga">Otorga el presente certificado al ciudadano (a):</p>

            <?php
            $nombreC = mb_convert_case($data['nombreEstudiante'] . ' ' . $data['apellidoEstudiante'], MB_CASE_TITLE, "UTF-8");
            ?>
            <h1 class="nombre-estudiante"><?php echo htmlspecialchars($nombreC); ?></h1>
            <p class="cedula">C.I. V- <?php echo htmlspecialchars($data['cedula']); ?></p>

            <?php
            $esParticipacion = ($data['paso'] === "aprobado" && (empty($data['nota']) || $data['nota'] == 0));
            $textoAntesPaso = $esParticipacion ? "Por su " : "Por haber ";
            $pasoTexto = mb_strtoupper($esParticipacion ? "PARTICIPACION" : $data['paso'], 'UTF-8');
            $tipoCursoF = str_replace('_rectoria', '', $data['tipo_curso']);
            $articulo = $data['articulo_tipo_curso'];
            $nombreCurso = mb_strtoupper($data['nombre_curso'], 'UTF-8');
            ?>

            <div class="certificacion-texto">
                <?php echo $textoAntesPaso; ?> <b><?php echo htmlspecialchars($pasoTexto); ?></b> en
                <?php echo $articulo; ?> <?php echo htmlspecialchars($tipoCursoF); ?> de
                <b><?php echo htmlspecialchars($nombreCurso); ?></b>.
            </div>

            <?php
            function formatearFecha($f)
            {
                if (!$f)
                    return "Fecha no proporcionada";
                $m = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
                $ts = strtotime($f);
                if (!$ts)
                    return $f;
                return "los " . date('d', $ts) . " días del mes de " . $m[date('n', $ts) - 1] . " de " . date('Y', $ts);
            }
            ?>
            <p class="fecha-expedicion">Certificación expedida en la Ciudad de San Cristóbal,
                <?php echo formatearFecha($data['fechaInscripcion']); ?>
            </p>
        </div>

        <!-- Firmas Pag 1 -->
        <?php foreach ($data['firmantes'] as $f):
            if ($f['pagina'] == 1):
                $clasePos = 'pos-' . strtolower(str_replace('_', '-', $f['posicion_codigo']));
                $nombreF = !empty($f['titulo']) ? $f['titulo'] . ' ' . $f['nombre'] : $f['nombre'];
                $nombreF = mb_convert_case($nombreF, MB_CASE_TITLE, "UTF-8");
                ?>
                <div class="firma-box <?php echo $clasePos; ?>">
                    <?php if (!empty($f['firma_base64'])): ?>
                        <img src="<?php echo $f['firma_base64']; ?>">
                    <?php else: ?>
                        <div style="height: 65px;"></div>
                    <?php endif; ?>
                    <p class="firma-nombre"><?php echo htmlspecialchars($nombreF); ?></p>
                    <p class="firma-cargo"><?php echo htmlspecialchars(mb_convert_case($f['cargo'], MB_CASE_TITLE, "UTF-8")); ?>
                    </p>
                </div>
            <?php endif; endforeach; ?>

        <img src="<?php echo $data['footerPath']; ?>" class="footer-banner">
    </div>

    <!-- ================= PÁGINA 2 ================= -->
    <div class="page-break"></div>

    <div style="position: relative; width: 100%; height: 600px;">

        <!-- Marca de agua FIJA -->
        <div class="watermark-container">
            <img src="<?php echo $data['imagePath']; ?>">
        </div>

        <div class="qr-layer">
            <img src="<?php echo $qr; ?>" alt="QR Code">
        </div>

        <p class="titulo-contenido">CONTENIDO:</p>

        <div class="modulos-lista">
            <?php foreach ($data['modulos'] as $i => $mod): ?>
                <div><?php echo ($i + 1) . ". " . htmlspecialchars($mod['nombre_modulo']); ?></div>
            <?php endforeach; ?>
        </div>

        <div class="texto-inferior">
            <p>Registrado en formación permanente tomo
                <?php echo htmlspecialchars(isset($data['tomo']) ? $data['tomo'] : ''); ?> y folio
                <?php echo htmlspecialchars(isset($data['folio']) ? $data['folio'] : ''); ?>.
            </p>

            <?php if (!empty($data['nota']) && $data['nota'] != 0): ?>
                <p>Presentando una calificación final de <?php echo htmlspecialchars($data['nota']); ?> de una nota máxima
                    (20).</p>
            <?php endif; ?>

            <p>El programa tuvo una duración de <?php echo htmlspecialchars($data['horas_cronologicas']); ?> horas
                cronológicas.</p>

            <?php
            $fInicio = formatearFecha($data['inicioMesCurso']);
            $fFin = formatearFecha($data['fechaFinalizacionCurso']);
            if ($data['tipo_curso'] === 'masterclass') {
                echo "<p>Curso desarrollado el $fInicio.</p>";
            } else {
                echo "<p>Curso desarrollado entre $fInicio y $fFin.</p>";
            }
            ?>
        </div>

        <!-- Firmas Pag 2 -->
        <?php
        $esRectoria = strpos($data['tipo_curso'], '_rectoria') !== false;

        if ($esRectoria) {
            $facilitador = null;
            foreach ($data['firmantes'] as $f) {
                if (strtolower($f['cargo']) === 'facilitador') {
                    $facilitador = $f;
                    break;
                }
            }

            $director = [
                'nombre' => 'Msc. Emilio Losada',
                'cargo' => 'Director de PNF en Electrónica',
                'firma_base64' => isset($data['firma_director_rectoria_b64']) ? $data['firma_director_rectoria_b64'] : '',
                'posicion_codigo' => 'P2_INF_DER'
            ];

            $firmasP2 = [$director];
            if ($facilitador) {
                $facilitador['posicion_codigo'] = 'P2_INF_IZQ';
                $firmasP2[] = $facilitador;
            }
        } else {
            $firmasP2 = array_filter($data['firmantes'], function ($f) {
                return $f['pagina'] == 2;
            });
        }
        ?>

        <?php foreach ($firmasP2 as $f):
            $clasePos = 'pos-' . strtolower(str_replace('_', '-', $f['posicion_codigo']));
            $nombreF = !empty($f['titulo']) ? $f['titulo'] . ' ' . $f['nombre'] : $f['nombre'];
            $nombreF = mb_convert_case($nombreF, MB_CASE_TITLE, "UTF-8");
            ?>
            <div class="firma-box <?php echo $clasePos; ?>">
                <?php if (!empty($f['firma_base64'])): ?>
                <div class="firma-box-img-wrapper">
                    <img src="<?php echo $f['firma_base64']; ?>">
                </div>
            <?php else: ?>
                <div style="height: 40px;"></div>
            <?php endif; ?>
                <p class="firma-nombre"><?php echo htmlspecialchars($nombreF); ?></p>
                <p class="firma-cargo"><?php echo htmlspecialchars(mb_convert_case($f['cargo'], MB_CASE_TITLE, "UTF-8")); ?>
                </p>
            </div>
        <?php endforeach; ?>

    </div>

</body>

</html>