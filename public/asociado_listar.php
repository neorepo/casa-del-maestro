<?php

// configuration
require '../includes/bootstrap.php';

$title = 'Lista de asociados';
$asociados = listarAsociados();

// Cada vez que volvamos al index.php se debe eliminar el aid
unset( $_SESSION['aid'] );

// Mostrar lista de asociados
render('asociado/listar.html', ['title' => $title, 'asociados' => $asociados]);