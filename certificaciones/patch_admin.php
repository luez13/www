<?php
$file = 'public/editar_cursos.php';
$c = file_get_contents($file);

// 1. Hide Plantilla for Postgrado
$search = '<div class="mb-3">
                                            <label class="form-label">Plantilla del Certificado a emitir:</label>';
$replace = '<?php if ($_SESSION[\'es_academico\']): ?>
                                        <div class="mb-3">
                                            <label class="form-label">Plantilla del Certificado a emitir:</label>';
$c = str_replace($search, $replace, $c);

// 2. Close the if block and allow PNFA to show Fechas y Horarios
$search2 = '<div class="form-text">Si se deja vacío, el curso usará la plantilla que esté configurada como global.</div>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($_SESSION[\'es_academico\']): ?>';
$replace2 = '<div class="form-text">Si se deja vacío, el curso usará la plantilla que esté configurada como global.</div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if ($_SESSION[\'es_academico\'] || $curso[\'tipo_curso\'] === \'PNFA\'): ?>';
$c = str_replace($search2, $replace2, $c);

// 3. Allow PNFA to see the module management button
$search3 = '$es_diplomado = in_array($curso[\'tipo_curso\'], [\'diplomado\', \'diplomado_rectoria\']);';
$replace3 = '$es_diplomado = in_array($curso[\'tipo_curso\'], [\'diplomado\', \'diplomado_rectoria\', \'PNFA\']);';
$c = str_replace($search3, $replace3, $c);

// 4. Also fix Detalles Academicos to show for PNFA
$search4 = '<?php if ($_SESSION[\'es_academico\']): ?>
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Detalles Académicos</h6>';
$replace4 = '<?php if ($_SESSION[\'es_academico\'] || $curso[\'tipo_curso\'] === \'PNFA\'): ?>
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Detalles Académicos</h6>';
$c = str_replace($search4, $replace4, $c);

file_put_contents($file, $c);
echo "Patch applied.\n";
