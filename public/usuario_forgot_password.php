<?php

// configuration
require '../includes/bootstrap.php';

if (isset($_SESSION['uid'])) {
    redirect('/');
}

$usuario = [
    'email' => null,
];

$errors = [];
$sentSuccessfully = false;

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

    if (array_key_exists('email', $_POST)) {
        $usuario['email'] = escape($_POST['email']);
    }

    // Validación del correo electrónico
    if ( !$usuario['email'] ) {
        // Error, campo vacío
        $errors['email'] = $messages['required'];
    } elseif ( valid_email( $usuario['email'] ) ) {
        $rows = existeEmailUsuario( $usuario['email'] );
        // Error: no existe el correo electrónico del usuario
        if (count($rows) != 1) {
            $errors['email'] = 'No existe el usuario según el correo electrónico.';
        }
    } else {
        // Error: e-mail no válido
        $errors['email'] = $messages['valid_email'];
    }

    // Si no hay errores
    if( empty( $errors ) ) {
        // Create a recovery token 32 caracteres, ejemplo: cc58481ee70ce0027209abf27af17199
        // $token = bin2hex(openssl_random_pseudo_bytes(16));

        $token = bin2hex(random_bytes(16));
        // https://github.com/nowakowskir/php-jwt
        // https://www.youtube.com/watch?v=mbsmsi7l3r4

        if ( insertarCodigoRecuperar($usuario, $token) ) {
            $link = 'http://localhost:8000/usuario_reset_password.php?e=' . urlencode($usuario['email']) . '&t=' . urlencode($token);
            $to = $usuario['email'];
            $subject = 'Restablecimiento de contraseña';
            $headers = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
            $headers .= 'From: Casa del Maestro y Previsión Social <noreply@app.com>' . "\r\n";
            
            $message = <<<EMAIL
            <h2>Para restablecer su contraseña haga clic en el siguiente enlace:</h2>
            <p><a href="$link">Clic en este enlace</a> o copie el siguiente código en la URL de su navegador.</p>
            <code style="background-color: #000; color: #fff; padding: 4px;">$link</code>
            <p>El enlace expirará en 20 minutos.</p>
            <p>Si usted no realizó esta solicitud, ignore el presente mensaje.</p>
            EMAIL;

            if ( mail($to, $subject, $message, $headers) ) {
                $sentSuccessfully = true;
            } else {
                echo '<p>Lo sentimos, hubo un problema al procesar su solicitud, intentelo de nuevo más tarde.</p>
                <a href="/usuario_login.php">Volver a inicio de sesión</a>';
                exit;
            }
        } else {
            echo '<p>Lo sentimos, hubo un problema al procesar su solicitud, intentelo de nuevo más tarde.</p>
                <a href="/usuario_login.php">Volver a inicio de sesión</a>';
            exit;
        }

    }
}

// Mostrar plantilla de registro de usuario
render('usuario/forgot_password.html', 
    [
        'title' => '¿Olvidaste tu contraseña?',
        'usuario' => $usuario,
        'errors' => $errors,
        'sentSuccessfully' => $sentSuccessfully
    ]
);

function insertarCodigoRecuperar($usuario, $token) {
    // https://www.php.net/manual/es/function.date
    $time = time(); // Alternativa más confiable según documentación => mktime(date("H"), date("i"), date("s"));
    // strftime('%F %T', $time) => 2020-11-29 04:17:54
    // date( 'Y-m-d H:i:s', $time ) => 2020-11-29 04:17:54
    // Ejemplo: días, hs, min, seg => 7 * 24 * 60 * 60
    // echo '<p>Time contiene la hora actual más 20 minutos: ' . date( 'Y-m-d H:i:s', time() + (20 * 60) ) . '</p>';
    try {
        $db = Db::getInstance();
        // begin the transaction
        $db->beginTransaction();
        $sql = 'INSERT INTO recuperar (email, token, time, estado) VALUES 
        (?, ?, ?, ?) ON CONFLICT(email) DO UPDATE SET token = ?, time = ?, estado = "pendiente";';
        Db::query($sql, $usuario['email'], $token, $time, 'pendiente', $token, $time);
        // commit the transaction
        $db->commit();
    } catch (PDOException $e) {
        // roll back the transaction if something failed
        $db->rollback();
        return false;
    }
    return true;
}