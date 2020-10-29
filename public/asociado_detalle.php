<?php

// configuration
require '../includes/bootstrap.php';

$data = getAsociadoPorId();

// $data['fecha_nacimiento'] = dateToTemplate( $data['fecha_nacimiento'] );

// $data['last_modified'] = formatDateTime( $data['last_modified'] );

$data['sexo'] = ($data['sexo'] == 'F') ? 'Femenino' : 'Masculino';

render('asociado/detalle.html', ['title' => 'Datos del asociado', 'data' => $data]);