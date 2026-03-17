<?php
// Evitar ejecución directa
if (!isset($data)) {
    die("Acceso directo denegado.");
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Certificado de Participación - Edición Violeta</title>
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

        /* Tipografías base */
        html,
        body {
            margin: 0;
            padding: 0;
            font-family: 'Cambria', serif;
            color: #000;
        }

        /* Contenedor Principal. Tamaño Carta Horizontal */
        .certificado-container {
            width: 100%;
            height: 100%;
            position: relative;
            background-image: url('data:image/jpeg;base64,<?php echo base64_encode(file_get_contents(realpath(__DIR__ . '/../../public/assets/img/certificado_mujeres.jpeg'))); ?>');
            background-size: 100% 100%;
            /* Forzar a que encaje en toda la hoja sin recortar márgenes */
            background-repeat: no-repeat;
            background-position: center center;
        }

        /* Marca de agua centrada debajo del texto sin transform */
        .marca-agua-container {
            position: absolute;
            top: 25%;
            left: 0;
            width: 100%;
            text-align: center;
            opacity: 0.15;
            /* Nivel de transparencia de la marca de agua */
            z-index: 1;
            /* Para enviar al fondo pero arriba del bg principal */
        }

        .marca-agua-container img {
            width: 450px;
        }

        /* Area de Texto donde se inscribirán los nombres */
        .text-area {
            position: absolute;
            top: 18%;
            left: 10%;
            width: 80%;
            text-align: center;
            z-index: 2;
            /* Para asegurar que el texto se dibuja sobre la marca de agua */
            line-height: 1.2;
        }

        .republic-titulos {
            font-size: 15px;
            font-weight: bold;
            margin: 2px 0;
            color: #1c2331;
        }

        .otorga {
            font-size: 16px;
            margin-top: 25px;
            text-transform: uppercase;
        }

        .otorgado-a {
            font-size: 14px;
            color: #555;
            margin-top: 10px;
            text-transform: uppercase;
        }

        .nombre-estudiante {
            font-family: 'Edwardian Script', cursive, serif;
            font-size: 70px;
            color: #722f8a;
            /* Púrpura */
            margin: 10px 0 5px 0;
            border-bottom: 2px solid #722f8a;
            /* Línea morada */
            display: inline-block;
            padding: 0 40px;
            font-weight: normal;
        }

        .cedula {
            font-weight: bold;
            font-size: 16px;
            margin-top: 5px;
        }

        .certificacion-texto {
            margin-top: 20px;
            font-size: 16px;
        }

        .certificacion-texto strong {
            font-weight: bold;
            font-size: 17px;
        }

        .fecha-expedicion {
            margin-top: 15px;
            font-size: 13px;
            text-transform: uppercase;
            color: #333;
        }

        /* Firmas flotantes abajo */
        .firmas-container {
            position: absolute;
            bottom: 40px;
            width: 90%;
            left: 5%;
            text-align: center;
            z-index: 2;
        }

        .firma-box {
            display: inline-block;
            width: 23%;
            vertical-align: top;
            text-align: center;
            margin: 0 0.5%;
        }

        .firma-img {
            height: 50px;
            display: block;
            margin: 0 auto 5px auto;
        }

        .firma-linea {
            border-top: 1px solid #333;
            width: 90%;
            margin: 5px auto;
        }

        .firma-nombre {
            font-size: 12px;
            font-weight: bold;
            margin: 2px 0;
            text-transform: uppercase;
            line-height: 1.1;
        }

        .firma-cargo {
            font-size: 11px;
            color: #555;
            margin: 0;
            line-height: 1.1;
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
    <div class="certificado-container">

        <div class="text-area">
            <p class="republic-titulos">REPÚBLICA BOLIVARIANA DE VENEZUELA</p>
            <p class="republic-titulos">MINISTERIO DEL PODER POPULAR PARA LA EDUCACIÓN UNIVERSITARIA</p>
            <p class="republic-titulos">UNIVERSIDAD POLITÉCNICA TERRITORIAL AGROINDUSTRIAL DEL ESTADO TÁCHIRA</p>

            <div class="otorga">Otorga el presente certificado al ciudadano (a):</div>

            <?php
            $nombreC = mb_convert_case($data['nombreEstudiante'] . ' ' . $data['apellidoEstudiante'], MB_CASE_TITLE, "UTF-8");
            ?>
            <div class="nombre-estudiante"><?php echo htmlspecialchars($nombreC); ?></div>

            <div class="cedula">C.I. <?php echo htmlspecialchars($data['cedula']); ?></div>

            <?php
            $esParticipacion = ($data['paso'] === "aprobado" && (empty($data['nota']) || $data['nota'] == 0));
            $textoAntesPaso = $esParticipacion ? "Por su " : "Por haber ";

            // Mantener PASO y NOMBRE DEL CURSO en mayúsculas
            $pasoTexto = mb_strtoupper($esParticipacion ? "PARTICIPACION" : $data['paso'], 'UTF-8');
            $nombreCurso = mb_strtoupper($data['nombre_curso'], 'UTF-8');

            // Convertir tipo de curso y articulo a minúsculas
            $tipoCursoF = mb_strtolower(str_replace('_rectoria', '', $data['tipo_curso']), 'UTF-8');
            $articulo = mb_strtolower($data['articulo_tipo_curso'], 'UTF-8');
            ?>
            <div class="certificacion-texto">
                <?php echo $textoAntesPaso; ?> <strong><?php echo htmlspecialchars($pasoTexto); ?></strong> en
                <?php echo $articulo; ?>
                <?php echo htmlspecialchars($tipoCursoF); ?> de
                <strong><?php echo htmlspecialchars($nombreCurso); ?></strong>
            </div>

            <?php
            function formatearFechaC($f)
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
            <div class="fecha-expedicion">
                Certificación expedida en la Ciudad de San Cristóbal,
                <?php echo formatearFechaC($data['fechaFinalizacionCurso']); ?>
            </div>
        </div>

        <div class="firmas-container">
            <?php
            if (isset($data['firmantes']) && is_array($data['firmantes'])) {
                // Solo mostramos firmantes asignados a la página 1 para este diseño frontal
                $firmantes_frontales = array_filter($data['firmantes'], function ($f) {
                    return $f['pagina'] == 1;
                });

                foreach ($firmantes_frontales as $firmante) {
                    $nombreF = !empty($firmante['titulo']) ? $firmante['titulo'] . ' ' . $firmante['nombre'] : $firmante['nombre'];
                    echo '<div class="firma-box">';
                    if ($data['mostrar_firmas'] && !empty($firmante['firma_base64'])) {
                        echo '<img src="' . htmlspecialchars($firmante['firma_base64']) . '" class="firma-img">';
                    } else {
                        echo '<div style="height: 55px;"></div>';
                    }
                    echo '<div class="firma-linea"></div>';
                    echo '<div class="firma-nombre">' . htmlspecialchars(trim($nombreF)) . '</div>';
                    echo '<div class="firma-cargo">' . htmlspecialchars($firmante['cargo']) . '</div>';
                    echo '</div>';
                }
            }
            ?>
        </div>
    </div>

    <!-- ================= PÁGINA 2 ================= -->
    <div class="page-break"></div>

    <div style="position: relative; height: 100%;">

        <!-- Capa de marca de agua (se pone primero para quedar detrás del texto) -->
        <div class="marca-agua-container">
            <img
                src="data:image/png;base64,<?php echo base64_encode(file_get_contents(realpath(__DIR__ . '/../../public/assets/img/marca_agua.png'))); ?>">
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
            <p>Registrado en formación permanente tomo <?php echo htmlspecialchars($data['tomo'] ?? ''); ?> y folio
                <?php echo htmlspecialchars($data['folio'] ?? ''); ?>.
            </p>

            <?php if (!empty($data['nota']) && $data['nota'] != 0): ?>
                <p>Presentando una calificación final de <?php echo htmlspecialchars($data['nota']); ?> de una nota máxima
                    (20).</p>
            <?php endif; ?>

            <p>El programa tuvo una duración de <?php echo htmlspecialchars($data['horas_cronologicas']); ?> horas
                cronológicas.</p>

            <?php
            $fInicio = formatearFechaC($data['inicioMesCurso']);
            $fFin = formatearFechaC($data['fechaFinalizacionCurso']);
            if ($data['tipo_curso'] === 'masterclass') {
                echo "<p>Curso desarrollado el $fInicio.</p>";
            } else {
                echo "<p>Curso desarrollado entre $fInicio y $fFin.</p>";
            }
            ?>
        </div>

        <!-- Firmas Pag 2 -->
        <div class="firmas-container">
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
                    'firma_base64' => $data['firma_director_rectoria_b64'] ?? '',
                    'titulo' => ''
                ];

                $firmasP2 = [$director];
                if ($facilitador) {
                    $firmasP2[] = $facilitador;
                }
            } else {
                $firmasP2 = array_filter($data['firmantes'], function ($f) {
                    return $f['pagina'] == 2;
                });
            }
            ?>

            <?php foreach ($firmasP2 as $f):
                $nombreF = !empty($f['titulo']) ? $f['titulo'] . ' ' . $f['nombre'] : $f['nombre'];
                ?>
                <div class="firma-box">
                    <?php if (!empty($f['firma_base64'])): ?>
                        <img src="<?php echo $f['firma_base64']; ?>" class="firma-img">
                    <?php else: ?>
                        <div style="height: 55px;"></div>
                    <?php endif; ?>
                    <div class="firma-linea"></div>
                    <div class="firma-nombre"><?php echo htmlspecialchars(trim($nombreF)); ?></div>
                    <div class="firma-cargo"><?php echo htmlspecialchars($f['cargo']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</body>

</html>