<?php
$file = 'c:\\laragon\\www\\certificaciones\\models\\curso.php';
$content = file_get_contents($file);

// Fix statements followed by } on the same line
$content = preg_replace('/([^;\n]+;)\s*\}\s*public function/', "$1\n    }\n    public function", $content);
$content = preg_replace('/([^;\n]+;)\s*\}\s*\/\//', "$1\n    }\n    //", $content);

$content = preg_replace('/([^;\n]+;)\s{4}\}/', "$1\n    }", $content);

// Fix } followed by another keyword on the same line
$content = preg_replace('/(\})\s{4}(public function|\/\/)/', "$1\n    $2", $content);

// Fix comment followed by public function on the same line
$content = preg_replace('/(\/\/[^\n]*?)\s{4}(public function)/', "$1\n    $2", $content);

file_put_contents($file, $content);
echo "Fixed curso.php\n";
