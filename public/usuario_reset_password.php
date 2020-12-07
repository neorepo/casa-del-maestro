<?php

// configuration
require '../includes/bootstrap.php';

if (!empty($_SESSION["user_id"])) {
    redirect("/");
}

$usuario = [
    'email' => null,
    'password' => null,
    'confirm_password' => null
];
$errors = [];

// graycen.doc@extraale.com
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

    foreach($usuario as $key => $value) {
        if(array_key_exists($key, $_POST)) {
            $usuario[$key] = escape($_POST[$key]);
        }
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

    // Si no existen errores
    if( empty( $errors ) ) {
        $usuario['email'] = $_SESSION['_e'];
        $token = $_SESSION['_t'];

        $usuario['password'] = hashPassword($usuario['password']);

        if(restablecerClaveUsuario($usuario, $token)) {
            // Eliminamos las variables contenidas en el array de $_SESSION utilizadas para la lógica de recuperación de contraseña.
            $_SESSION = [];
            // Re dirigimos al usuario a la página de login.
            Flash::addFlash('Su contraseña ha sido restablecida exitosamente, ahora puedes iniciar sesión.', 'success');
            redirect('/usuario_login.php');
        }
    } else {
        // Mostrar plantilla de restablecimiento de contraseña con feedback.
        render('usuario/reset_password.html', ['title' => 'Restablecer contraseña', 'usuario' => $usuario, 'errors' => $errors]);
    }

    Flash::addFlash('Algo salió mal. Vuelva a comprobar el enlace o póngase en contacto con el administrador del sistema.', 'info');
    redirect('/usuario_forgot_password.php');
}

// Aquí surge la duda de si, sanitizar y validar el email y el token.
if( array_key_exists('e', $_GET) && array_key_exists('t', $_GET) ) {
    // Datos provenientes del correo electrónico del usuario.
    $usuario['email'] = escape($_GET['e']);
    $token = escape($_GET['t']);

    if(validarCredenciales($usuario, $token)) {
        // Almacenamos en el array $_SESSION el email y el token.
        $_SESSION['_e'] = $usuario['email'];
        $_SESSION['_t'] = $token;
        // Mostrar plantilla de restablecimiento de contraseña.
        render('usuario/reset_password.html', ['title' => 'Restablecer contraseña', 'usuario' => $usuario, 'errors' => $errors]);
    }
    Flash::addFlash('Email o token no válidos. O la solicitud de restablecimiento caducó.', 'info');
    redirect('/usuario_forgot_password.php');
}
Flash::addFlash('Algo salió mal. Vuelva a comprobar el enlace o póngase en contacto con el administrador del sistema.', 'info');
redirect('/usuario_forgot_password.php');

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

function restablecerClaveUsuario($usuario, $token) {
    $now = date("Y-m-d H:i:s");
    try {
        $db = Db::getInstance();
        // begin the transaction
        $db->beginTransaction();

        // Consulta 1
        $sql = 'UPDATE usuario SET password = ?, last_modified = ? WHERE email = ? AND deleted = 0;';
        Db::query($sql, $usuario['password'], $now, $usuario['email']);

        // Consulta 2
        $sql = 'UPDATE recuperar SET estado = "usado" WHERE token = ? AND email = ?;';
        Db::query($sql, $token, $usuario['email']);

        // commit the transaction
        $db->commit();
    } catch (PDOException $e) {
        // roll back the transaction if something failed
        $db->rollback();
        return false;
    }
    return true;
}