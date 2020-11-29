<?php

// configuration
require '../includes/bootstrap.php';

if (isset($_SESSION['uid'])) {
    redirect('/');
}

$usuario = [
    'password' => null,
    'confirm_password' => null
];
$errors = [];

// Aquí surge la duda de si, sanitizar y validar el email y el token
if( array_key_exists('e', $_GET) && array_key_exists('t', $_GET) ) {
    // Datos provenientes del correo electrónico del usuario.
    $usuario['email'] = $_GET['e'];
    $token = $_GET['t'];

    if(!validarCredenciales($usuario, $token)) {
        $html = <<<HTML
        <p>Email o token no válidos. O la solicitud de restablecimiento caducó.</p>
        <p><a href="http://localhost:8000/usuario_forgot_password.php">Clic aquí</a> para restablecer nuevamente la contraseña.</p>
        HTML;
        echo $html;
        exit;
    } else {
        // Mostrar plantilla de restablecimiento de contraseña.
        render('usuario/reset_password.html', ['title' => 'Restablecer contraseña', 'usuario' => $usuario, 'errors' => $errors]);
    }

}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ( !empty( $_POST['token'] ) && Token::validate( $_POST['token'] ) ) {

        $data = [
            'email' => $_POST['usuario']['email'],
            'password' => $_POST['usuario']['password'],
            'confirm_password' => $_POST['usuario']['confirm_password']
        ];
        
        if( array_key_exists('email', $data) ) {
            $usuario['email'] = escape($data['email']);
        }

        if( array_key_exists('password', $data) ) {
            $usuario['password'] = escape($data['password']);
        }

        if( array_key_exists('confirm_password', $data) ) {
            $usuario['confirm_password'] = escape($data['confirm_password']);
        }

        // Validación del correo electrónico
        if ( !$usuario['email'] ) {
            $errors['email'] = $messages['required'];
        } elseif ( valid_email( $usuario['email'] ) ) {
            // $rows = existeEmailUsuario( $usuario['email'] );
            // if (count($rows) == 1) {
            //     $errors['email'] = str_replace(':f', 'correo electrónico', $messages['unique'] );;
            // }
        } else {
            $errors['email'] = $messages['valid_email'];
        }

        // Validación de las contraseñas
        if ( !$usuario['password'] ) {
            $errors['password'] = $messages['required'];
        } elseif ( !$usuario['confirm_password'] ) {
            $errors['confirm_password'] = 'Confirme su contraseña.';
        } else {
            // Comparación segura a nivel binario sensible a mayúsculas y minúsculas.
            if (strcmp($usuario['password'], $usuario['confirm_password']) !== 0) {
                $errors['confirm_password'] = 'Las contraseñas que ingresó no coinciden.';
            }
        }

        echo 'Datos en el array de errores:';
        echo '<pre>';
        print_r( $errors );
        echo '</pre>';

        /**
         * Si no existen errores
         */
        if( empty( $errors ) ) {
            if ( insertarUsuario( $usuario ) ) {
                // the CSRF token they submitted does not match the one we sent
                unset($_SESSION['_token']);
                Flash::addFlash('Ahora puedes acceder al sistema.');
                redirect('/');
            } else {
                $registerSuccess = false;
            }
        } else {
            // Mostrar plantilla de restablecimiento de contraseña con feedback.
            render('usuario/reset_password.html', ['title' => 'Restablecer contraseña', 'usuario' => $usuario, 'errors' => $errors]);
        }
    }
}


/**
 * Funciones de persistencia
 */
function validarCredenciales($usuario, $token) {
    // Consulta por los datos de la solicitud de restablecimiento.
    $sql = 'SELECT * FROM recuperar WHERE email = ? AND token = ? LIMIT 1;';
    $rows = Db::query($sql, $usuario['email'], $token);
    if(count($rows) == 1) {
        $row = $rows[0];
        // Una alternativa no muy confiable según documentación es: => time() - (20 * 60);
        $time = mktime(date("H"), date("i")-20, date("s"));
        
        if($row['time'] >= $time && $row['estado'] == 'pendiente') {
            return true;
        }
    }
    return false;
}