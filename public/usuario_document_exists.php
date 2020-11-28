<?php
require_once '../includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../src/Db.php';
    $usuario = $_POST['usuario'];
    $sql = "SELECT id_usuario FROM usuario WHERE num_documento = ? LIMIT 1;";
    $rows = Db::query($sql, $usuario);
    if (count($rows) == 1) {
        echo "error";
    } else {
        echo "ok";
    }
}
else {
    redirect('/');
}