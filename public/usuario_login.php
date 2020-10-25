<?php

// configuration
require '../includes/bootstrap.php';

if (isset($_SESSION['uid'])) {
    redirect('/');
}

$data = [
    'usuario' => null,
    'password' => null
];

$errors = [];
$logonSuccess = true;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!empty($_POST['token']) && Token::validate( $_POST['token'] )) {

        if (!empty($_POST['usuario'])) {
            // Escapamos caracteres no permitidos
            $data['usuario'] = escape( $_POST['usuario'] );
        } else {
            $errors['usuario'] = 'Ingrese su usuario.';
        }
    
        if (!empty($_POST['password'])) {
            // Escapamos caracteres no permitidos
            $data['password'] = escape( $_POST['password'] );
        } else {
            $errors['password'] = 'Ingrese su contraseña.';
        }

        // Si no hay errores
        if ( empty( $errors ) ) {
            if ( authenticateUser( $data['usuario'], $data['password']) ) {
                // the CSRF token they submitted does not match the one we sent
                unset($_SESSION['_token']);
                redirect('/');
            } else {
                $logonSuccess = false;
            }
        }
    }
}

$title = 'Acceso';

// Mostrar plantilla de login de usuario
render('usuario/login.html', ['title' => $title, 'data' => $data, 'errors' => $errors, 'logonSuccess' => $logonSuccess]);

/**
 * Funciones de persistencia
 */
function authenticateUser($usuario, $password) {
    $q = 'SELECT * FROM usuario WHERE num_documento = ? OR email = ? ;';

    $rows = Db::query($q, $usuario, $usuario);
    
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