<?php

// configuration
require '../includes/bootstrap.php';

if (isset($_SESSION['uid'])) {
    redirect('/');
}

$data = [
    'apellido' => null,
    'nombre' => null,
    'usuario' => null,
    'email' => null,
    'password' => null,
    'confirm_password' => null
];

$errors = [];
$minlength = 3;
$maxlength = 40;
$registerError = true;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!empty($_POST['token']) && Token::validate( $_POST['token'] )) {

        // Validación del apellido
        if (!empty($_POST['apellido'])) {

            $data['apellido'] = escape( $_POST['apellido'] );

            if ( !onlyletters( $data['apellido'] ) ) {
                $errors['apellido'] = $messages['onlyLetters'];
            } elseif ( !minlength( $data['apellido'], $minlength) ) {
                $errors['apellido'] = $messages['minLength'];
                // Aumenta la longitud de este texto a ? caracteres o más (actualmente, el ? tiene ? caracteres)
            } elseif ( !maxlength($data['apellido'], $maxlength) ) {
                $errors['apellido'] = $messages['maxLength'];
                // Reduce la longitud de este texto a 3 caracteres o menos (actualmente, el ? tiene ? caracteres)
            }
        } else {
            $errors['apellido'] = $messages['required'];
        }

        // Validación del nombre
        if (!empty($_POST['nombre'])) {

            $data['nombre'] = escape( $_POST['nombre'] );

            if ( !onlyletters( $data['nombre'] ) ) {
                $errors['nombre'] = $messages['onlyLetters'];
            } elseif ( !minlength( $data['nombre'], $minlength) ) {
                $errors['nombre'] = $messages['minLength'];
            } elseif ( !maxlength($data['nombre'], $maxlength) ) {
                $errors['nombre'] = $messages['maxLength'];
            }
        } else {
            $errors['nombre'] = $messages['required'];
        }

        // Validación del número de documento
        if (!empty($_POST['usuario'])) {

            $data['usuario'] = escape( $_POST["usuario"] );

            if ( preg_match('/^[\d]{8}$/', $data['usuario']) ) {
                $rows = existeNumDeDocumentoUsuario( $data['usuario'] );
                
                if (count($rows) == 1) {
                    $errors['usuario'] = str_replace(':f', 'número de documento', $messages['unique'] );;
                }
            } else {
                $errors['usuario'] = $messages['valid_document'];
            }
        } else {
            $errors['usuario'] = $messages['required'];
        }

        // Validación del correo electrónico
        if (!empty($_POST['email'])) {

            $data['email'] = escape( $_POST['email'] );

            if ( valid_email( $data['email'] ) ) {
                $rows = existeEmailUsuario( $data['email'] );

                if (count($rows) == 1) {
                    $errors['email'] = str_replace(':f', 'correo electrónico', $messages['unique'] );;
                }
                
            } else {
                $errors['email'] = $messages['valid_email'];
            }
        } else {
            $errors['email'] = $messages['required'];
        }

        // Validación de las contraseñas
        if (!empty($_POST['password'])) {
            
            $data['password'] = escape( $_POST['password'] );
    
            if (!empty($_POST['confirm_password'])) {

                $data['confirm_password'] = escape( $_POST['confirm_password'] );
                
                // Comparación segura a nivel binario sensible a mayúsculas y minúsculas.
                if (strcmp($data['password'], $data['confirm_password']) !== 0) {
                    $errors['confirm_password'] = 'Las contraseñas que ingresó no coinciden.';
                }
            } else {
                $errors['confirm_password'] = 'Confirme su contraseña.';
            }
        } else {
            $errors['password'] = $messages['required'];
        }

        /**
         * Si no existen errores
         */
        if( empty( $errors ) ) {
            
            if ( insertarUsuario( $data ) ) {

                // the CSRF token they submitted does not match the one we sent
                unset($_SESSION['_token']);
                Flash::addFlash('Ahora puedes acceder al sistema.');
                redirect('/');
            } else {
                $registerError = false;
            }
        }
    }
}

$title = 'Registro de Usuario';

// Mostrar plantilla de registro de usuario
render('usuario/register.html', ['title' => $title, 'data' => $data, 'errors' => $errors, 'registerError' => $registerError]);

function insertarUsuario($data) {
    
    $admin = ['94269698', '41088522'];

    $created = $last_modified = date('Y-m-d H:i:s');
    $rol = 'usuario';
    
    $data['password'] = hashPassword($data['password']);

    if( in_array( $data['usuario'], $admin ) ) {
        $rol = 'admin';
    }

    $q = 'INSERT INTO usuario (apellido, nombre, num_documento, email, password, rol, created, last_modified) 
              VALUES(?, ?, ?, ?, ?, ?, ?, ?)';

    // El usuario contiene el número de documento
    return Db::query($q, capitalize($data['apellido']), capitalize($data['nombre']), $data['usuario'], $data['email'], $data['password'], $rol, $created, $last_modified);
}