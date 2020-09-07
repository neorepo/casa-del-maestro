<?php

// configuration
require '../includes/bootstrap.php';

if (isset($_SESSION['uid'])) {
    redirect('/');
}

$data = [
    'apellido' => '',
    'nombre' => '',
    'usuario' => '',
    'email' => '',
    'password' => '',
    'confirm_password' => ''
];

$errors = [];
$minlength = 3;
$maxlength = 40;
$registerError = true;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!empty($_POST['token']) && Token::validate( $_POST['token'] )) {
        // the CSRF token they submitted does not match the one we sent
        unset($_SESSION['_token']);

        // Validación del apellido
        if (!empty($_POST['apellido'])) {

            $data['apellido'] = escape( $_POST['apellido'] );

            if ( !onlyletters( $data['apellido'] ) ) {
                $errors['apellido'] = 'Solo se permiten letras (a-zA-Z), y espacios en blanco.';
            } elseif ( !minlength( $data['apellido'], $minlength) ) {
                $errors['apellido'] = 'Aumenta la longitud a ' . $minlength . ' caracteres como mínimo.';
                // Aumenta la longitud de este texto a ? caracteres o más (actualmente, el ? tiene ? caracteres)
            } elseif ( !maxlength($data['apellido'], $maxlength) ) {
                $errors['apellido'] = 'Reduce la longitud a ' . $maxlength . ' caracteres o menos.';
                // Reduce la longitud de este texto a 3 caracteres o menos (actualmente, el ? tiene ? caracteres)
            }
        } else {
            $errors['apellido'] = 'Por favor, ingrese su apellido.';
        }

        // Validación del nombre
        if (!empty($_POST['nombre'])) {

            $data['nombre'] = escape( $_POST['nombre'] );

            if ( !onlyletters( $data['nombre'] ) ) {
                $errors['nombre'] = 'Solo se permiten letras (a-zA-Z), y espacios en blanco.';
            } elseif ( !minlength( $data['nombre'], $minlength) ) {
                $errors['nombre'] = 'Aumenta la longitud a ' . $minlength . ' caracteres como mínimo.';
            } elseif ( !maxlength($data['nombre'], $maxlength) ) {
                $errors['nombre'] = 'Reduce la longitud a ' . $maxlength . ' caracteres o menos.';
            }
        } else {
            $errors['nombre'] = 'Por favor, ingrese su nombre.';
        }

        // Validación del número de documento
        if (!empty($_POST['usuario'])) {

            $data['usuario'] = escape( $_POST["usuario"] );

            if ( preg_match('/^[\d]{8}$/', $data['usuario']) ) {
                $rows = existeNumDeDocumentoUsuario( $data['usuario'] );
                
                if (count($rows) == 1) {
                    $errors['usuario'] = 'Este número de documento ya se encuentra registrado.';
                }
            } else {
                $errors['usuario'] = 'El formato o el número de documento ingresado no es válido.';
            }
        } else {
            $errors['usuario'] = "Ingrese el número de documento.";
        }

        // Validación del correo electrónico
        if (!empty($_POST['email'])) {

            $data['email'] = escape( $_POST['email'] );

            if ( valid_email( $data['email'] ) ) {
                $rows = existeEmailUsuario( $data['email'] );
                if (count($rows) == 1) {
                    $errors['email'] = 'Este correo electrónico ya se encuentra registrado.';
                }
            } else {
                $errors['email'] = 'El correo electrónico no es válido.';
            }
        } else {
            $errors['email'] = 'Por favor, ingrese su correo electrónico.';
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
            $errors['password'] = 'Ingrese una contraseña.';
        }

        /**
         * Si no existen errores
         */
        if( empty( $errors ) ) {
            
            if ( insertarUsuario( $data ) ) {
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