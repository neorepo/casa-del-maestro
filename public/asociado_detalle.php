<?php

// configuration
require '../includes/bootstrap.php';

$data = [];

unset($_SESSION['aid']);

$data = getAsociadoPorId();

$data['telefono_linea'] = $data['telefono_linea'] ?? '';
$data['fecha_nacimiento'] = dateToPage( $data['fecha_nacimiento'] );

$data['sexo'] = ($data['sexo'] == 'F') ? 'Femenino' : 'Masculino';

render('asociado/detalle.html', ['title' => 'Datos del asociado', 'data' => $data]);