<?php

// configuration
require '../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idProvincia = null;
    $response = ['success' => false, 'localidades' => null];
    if(array_key_exists('id_provincia', $_POST)) {
        $idProvincia = $_POST['id_provincia'];
    }
    if($idProvincia) {
        $response['localidades'] = getLocalidadesPorIdProvincia( (int) $idProvincia );
    }
    if ($response['localidades']) {
        $response['success'] = true;
    }
    echo json_encode($response);
    exit;
}
header('Location: index.php');
exit;
