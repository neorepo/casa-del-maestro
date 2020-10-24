<?php

// configuration
require '../includes/bootstrap.php';

$data = [];

unset($_SESSION['aid']);

$data = getAsociadoPorId();

$data['fecha_nacimiento'] = dateToTemplate( $data['fecha_nacimiento'] );

$data['created'] = formatDateTime( $data['created'] );
$data['last_modified'] = formatDateTime( $data['last_modified'] );

$data['sexo'] = ($data['sexo'] == 'F') ? 'Femenino' : 'Masculino';

render('asociado/detalle.html', ['title' => 'Datos del asociado', 'data' => $data]);