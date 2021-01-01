<?php

// configuration
require '../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_provincia = null;
    $response = null;
    if(array_key_exists('id_provincia', $_POST)) {
        $id_provincia = $_POST['id_provincia'];
    }
    if($id_provincia) {
        $response = getLocalidadesPorIdProvincia( (int) $id_provincia );
    }
    if (!$response) {
        $response = 'something went wrong!';
    }
    echo json_encode($response);
    exit;
}
header('Location: index.php');
exit;
