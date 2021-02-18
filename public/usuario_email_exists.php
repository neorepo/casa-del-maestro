<?php
// Respuesta solicitud XMLHttpRequest por el email de usuario en el formulario de registro.
require_once '../includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    redirect('/');
}
require_once '../src/Db.php';
$email = $_POST['email'];
$sql = "SELECT id_usuario FROM usuario WHERE email = ? LIMIT 1;";
$rows = Db::query($sql, $email);
if (!$rows) {
    echo "ok";
} else {
    echo "error";
}