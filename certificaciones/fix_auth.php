<?php
$f = 'C:\laragon\www\certificaciones\controllers\autenticacion.php';
$c = file_get_contents($f);
$c = preg_replace("/\\\$_SESSION\['es_academico'\]\s*=\s*\\\$stmtExt->fetchColumn\(\);/", "\$val = \$stmtExt->fetchColumn();\n                \$_SESSION['es_academico'] = (\$val === true || \$val === 1 || \$val === '1' || \$val === 't' || \$val === 'true');", $c);
$c = preg_replace("/\\\$_SESSION\['es_academico'\]\s*=\s*\\\$extensiones\[0\]\['es_academico'\];/", "\$val = \$extensiones[0]['es_academico'];\n                                \$_SESSION['es_academico'] = (\$val === true || \$val === 1 || \$val === '1' || \$val === 't' || \$val === 'true');", $c);
file_put_contents($f, $c);
echo "Replaced in auth";
