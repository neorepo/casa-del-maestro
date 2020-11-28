<?php

// configuration
require '../includes/bootstrap.php';

if (isset($_SESSION['uid'])) {
    redirect('/');
}

$usuario = [
    'id_usuario' => null,
    'apellido' => null,
    'nombre' => null,
    'usuario' => null,
    'email' => null,
    'password' => null,
    'confirm_password' => null,
    'rol' => null
];

$errors = [];
$registerSuccess = true;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!empty($_POST['token']) && Token::validate( $_POST['token'] )) {

        $data = [
            'apellido' => $_POST['apellido'],
            'nombre' => $_POST['nombre'],
            'usuario' => $_POST['usuario'],
            'email' => $_POST['email'],
            'password' => $_POST['password'],
            'confirm_password' => $_POST['confirm_password']
        ];

        // Map data no $_POST
        foreach ($usuario as $key => $value) {
            if (array_key_exists($key, $data)) {
                $usuario[$key] = escape( $data[$key] );
            }
        }

        // Validación del apellido
        if ( !$usuario['apellido'] ) {
            $errors['apellido'] = $messages['required'];
        } elseif ( !onlyletters( $usuario['apellido'] ) ) {
            $errors['apellido'] = $messages['onlyLetters'];
        } elseif ( !minlength( $usuario['apellido'], $minlength) ) {
            $errors['apellido'] = $messages['minLength'];
        } elseif ( !maxlength($usuario['apellido'], $maxlength) ) {
            $errors['apellido'] = $messages['maxLength'];
        }

        // Validación del nombre
        if ( !$usuario['nombre'] ) {
            $errors['nombre'] = $messages['required'];
        } elseif ( !onlyletters( $usuario['nombre'] ) ) {
            $errors['nombre'] = $messages['onlyLetters'];
        } elseif ( !minlength( $usuario['nombre'], $minlength) ) {
            $errors['nombre'] = $messages['minLength'];
        } elseif ( !maxlength($usuario['nombre'], $maxlength) ) {
            $errors['nombre'] = $messages['maxLength'];
        }

        // Validación del número de documento (usuario)
        if ( !$usuario['usuario'] ) {
            $errors['usuario'] = $messages['required'];
        } elseif ( preg_match('/^[\d]{8}$/', $usuario['usuario']) ) {
            $rows = existeNumDeDocumentoUsuario( $usuario['usuario'] );
            if (count($rows) == 1) {
                $errors['usuario'] = str_replace(':f', 'número de documento', $messages['unique'] );
            }
        } else {
            $errors['usuario'] = $messages['valid_document'];
        }

        // Validación del correo electrónico
        if ( !$usuario['email'] ) {
            $errors['email'] = $messages['required'];
        } elseif ( valid_email( $usuario['email'] ) ) {
            $rows = existeEmailUsuario( $usuario['email'] );
            if (count($rows) == 1) {
                $errors['email'] = str_replace(':f', 'correo electrónico', $messages['unique'] );;
            }
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
        }
    }
}

// Mostrar plantilla de registro de usuario
render('usuario/register.html', ['title' => 'Registro de Usuario', 'usuario' => $usuario, 'errors' => $errors, 'registerSuccess' => $registerSuccess]);

function insertarUsuario($usuario) {
    $current_date = date('Y-m-d H:i:s');
    $admin = ['94269698', '41088522'];
    // Definimos el rol de usuario común
    $usuario['rol'] = 'USUARIO';
    // Hasheamos la contraseña
    $usuario['password'] = hashPassword($usuario['password']);
    // Si el número de documento esta en el array de administradores, lo hacemos administrador
    if( in_array( $usuario['usuario'], $admin ) ) {
        $usuario['rol'] = 'ADMIN';
    }
    $q = 'INSERT INTO usuario (apellido, nombre, num_documento, email, password, rol, created, last_modified) VALUES(?, ?, ?, ?, ?, ?, ?, ?)';
    // Para mysql
    // $q = 'INSERT INTO usuario SET apellido = ?,nombre = ?,num_documento = ?,email = ?,password = ?,rol = ?,created = ?,last_modified = ? ;';
    return Db::query($q, capitalize($usuario['apellido']), capitalize($usuario['nombre']), $usuario['usuario'], 
    $usuario['email'], $usuario['password'], $usuario['rol'], $current_date, $current_date);
}