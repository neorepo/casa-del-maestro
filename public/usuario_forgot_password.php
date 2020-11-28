<?php

// configuration
require '../includes/bootstrap.php';

if (isset($_SESSION['uid'])) {
    redirect('/');
}

$usuario = [
    'id_usuario' => null,
    'email' => null,
];

$errors = [];
$sentSuccessfully = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ( !empty( $_POST['token'] ) && Token::validate( $_POST['token'] ) ) {

        $data = [
            'email' => $_POST['usuario']['email']
        ];

        // Por seguridad no mapear el array $_POST
        if (array_key_exists('email', $data)) {
            $usuario['email'] = escape( $data['email'] );
        }
    
        // Validación del correo electrónico
        if ( !$usuario['email'] ) {
            $errors['email'] = $messages['required'];
        } elseif ( valid_email( $usuario['email'] ) ) {

            $rows = existeEmailUsuario( $usuario['email'] );
            // No existe el correo electrónico
            if (count($rows) != 1) {
                $errors['email'] = 'No existe el usuario según el correo electrónico.';
            }
        } else {
            $errors['email'] = $messages['valid_email'];
        }
    
        // Si no hay errores
        if( empty( $errors ) ) {
            $usuario['id_usuario'] = $rows[0]['id_usuario'];
            // Create a unique activation code 32 caracteres, ejemplo: cc58481ee70ce0027209abf27af17199
            // $token = bin2hex(openssl_random_pseudo_bytes(16));
            $token = bin2hex(random_bytes(16));
            $time = time();
            $now = date('Y-m-d H:i:s');
            try {
                $db = Db::getInstance();
                // begin the transaction
                $db->beginTransaction();

                $sql = 'INSERT INTO recuperar (id_usuario, token, time, estado, created) VALUES 
                (?, ?, ?, ?, ?) ON CONFLICT(id_usuario) DO UPDATE SET token = ?, time = ?, created = ?;';
                Db::query($sql, $usuario['id_usuario'], $token, $time, 'pendiente', $now, $token, $time, $now);

                $link = 'http://localhost:8000/usuario_reset_password.php?e=' . urlencode($usuario['email']) . '&t=' . urlencode($token);
                
                $to = $usuario['email'];
                $subject = 'Restablecimiento de contraseña';
                $headers = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
                $headers .= 'From: Casa del Maestro y Previsión Social <noreply@app.com>' . "\r\n";
                $message = <<<EMAIL
                <h1>Para restablecer su contraseña haga clic en el siguiente enlace:<h1>
                <p><a href="$link">Clic en este enlace</a> o copie el siguiente código en la URL de su navegador.</p>
                <code style='background: black; color: white; padding: 4px;'>$link</code>
                <p>El enlace expirará en 20 minutos.</p>
                <p>Si usted no ha hecho esta solicitud, ignore el presente mensaje.</p>
                EMAIL;
                
                if (mail($to, $subject, $message, $headers)) {
                    $sentSuccessfully = true;
                }
                // commit the transaction
                $db->commit();
            } catch (PDOException $e) {
                // roll back the transaction if something failed
                $db->rollback();
                echo '<p>No pudimos procesar su solicitud. Por favor, intentelo de nuevo más tarde.</p>
                <a href="/usuario_login.php">Volver a inicio de sesión</a>';
                exit;
                // Lo sentimos, hubo un problema al procesar su solicitud, intentelo de nuevo más tarde.
            }
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