<?php

// configuration
require '../includes/bootstrap.php';

$id_provincia = null;
$data['success'] = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(array_key_exists('id_provincia', $_POST)) {
        $id_provincia = $_POST['id_provincia'];
    }
    if($id_provincia) {
        $data['success'] = true;
        $data['localidades'] = getLocalidadesPorIdProvincia( (int) $id_provincia );
        echo json_encode($data);
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}