<?php

// configuration
require '../includes/bootstrap.php';

$title = 'Lista de asociados';
$asociados = listarAsociados();

// Mostrar lista de asociados
render('asociado/listar.html', ['title' => $title, 'asociados' => $asociados]);