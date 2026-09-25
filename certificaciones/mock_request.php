<?php
session_start();
$_POST = [
    'action' => 'guardar',
    'id_materia' => 0,
    'id_curso' => 1,
    'nombre_materia' => 'Prueba SQL',
    'duracion_bimestres' => '1',
    'total_horas' => 20,
    'modalidad' => 'Virtual',
    'docente_id' => 1,
    'lapso_academico' => 1,
    'temario' => '',
    'fecha_inicio' => '',
    'fecha_fin' => ''
];
$_SESSION['user_id'] = 1;
require 'C:/laragon/www/certificaciones/controllers/gestion_materia.php';
