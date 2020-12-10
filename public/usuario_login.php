<?php

// configuration
require '../includes/bootstrap.php';

if (isset($_SESSION['uid'])) {
    redirect('/');
}

$usuario = [
    'usuario' => null,
    'password' => null
];

$errors = [];
$logonSuccess = true;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (array_key_exists('token', $_POST)) {
        if (!Token::validate($_POST['token'])) {
            // Si el token CSRF que enviaron no coincide con el que enviamos.
            redirect('/usuario_logout.php');
        }
    }
    // No existe la key token
    else {
        redirect('/usuario_logout.php');
    }

    foreach ($usuario as $key => $value) {
        if (array_key_exists($key, $_POST)) {
            $usuario[$key] = escape( $_POST[$key] );
        }
    }

    // Si no hay errores
    if ( authenticateUser( $usuario['usuario'], $usuario['password']) ) {
        // the CSRF token they submitted does not match the one we sent
        unset($_SESSION['_token']);
        redirect('/');
    } else {
        $logonSuccess = false;
    }
}

// Mostrar plantilla de login de usuario
render('usuario/login.html', ['title' => 'Acceso', 'usuario' => $usuario, 'errors' => $errors, 'logonSuccess' => $logonSuccess]);

/**
 * Funciones de persistencia
 */
function authenticateUser($usuario, $password) {
    $q = 'SELECT * FROM usuario WHERE num_documento = ? LIMIT 1;';
    $rows = Db::query($q, $usuario);
    if ( count($rows) == 1 ) {
        // first (and only) row
        $usuario = $rows[0];
        if ( verifyPassword( $password, $usuario['password'] ) ) {
            // unset any session variables
            $_SESSION = [];
            $_SESSION['uid'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol'];
            return true;
        }
    }
    return false;
}