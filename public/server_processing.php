<?php

// configuration
require '../includes/bootstrap.php';

$data['success'] = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if( isValidProvinceId( $_POST['id_provincia'] ) ) {

        $data['success'] = true;
        
        $data['localidades'] = getLocalidadesPorIdProvincia( (int) $_POST['id_provincia'] );
    }

    echo json_encode($data);
    exit;
}