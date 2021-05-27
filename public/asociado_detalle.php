<?php

// configuration
require '../includes/bootstrap.php';

$asociado = getAsociadoPorId();

// $asociado['last_modified'] = formatDateTime( $asociado['last_modified'] );

$asociado['sexo'] = ($asociado['sexo'] == 'F') ? 'Femenino' : 'Masculino';

render('asociado/detalle.html', ['title' => 'Datos del asociado', 'asociado' => $asociado]);