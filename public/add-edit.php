<?php

// configuration
require '../includes/bootstrap.php';

$data = [];
$errors = [];
$localidades = [];

$minlength = 3;
$maxlength = 40;

$messages = [
    'required' => 'Este campo es requerido.',
    'onlyLetters' => 'Solo se permiten letras (a-zA-Z), y espacios en blanco.',
    'minLength' => 'Aumenta la longitud a ' . $minlength . ' caracteres como mínimo.',
    'maxLength' => 'Reduce la longitud a ' . $maxlength . ' caracteres o menos.',
    'valid_date' => 'El formato o la fecha ingresada no es válida.',
    'valid_email' => 'El correo electrónico no es válido.',
    'valid_document_type' => 'El tipo de documento seleccionado no es válido.',
    'valid_entry_condition' => 'La condición de ingreso seleccionada no es válida.',
    'valid_sex' => 'El sexo seleccionado no es válido.',
    'valid_legal_age' => 'Debes tener 18 años o más para poder asociarte. Asegúrate de usar tu fecha de nacimiento real.', /* Age of majority (also known as the "age of maturity") */
    'valid_document' => 'El formato o el número de documento ingresado no es válido.',
    'valid_cuil' => 'El formato o el número de cuil ingresado no es válido.',
    'valid_mobile_phone' => 'El formato o el número de teléfono móvil ingresado no es válido.',
    'valid_phone' => 'El formato o el número de teléfono de línea ingresado no es válido.',
    'unique' => 'Este :f ya se encuentra registrado.'
];

$action = array_key_exists('aid', $_GET);

if ($action) {
    // Recuperamos los datos del asociado de la base de datos
    $data = getAsociadoPorId();
    // Asignamos el id del asociado a la variable de sesión para saber que registro editar, también podemos utilizar el array $_GET
    $_SESSION['aid'] = $data['id_asociado'];
    // Formateamos la fecha de nacimiento: ejm: 2000-03-06 a 06/03/2000
    $data['fecha_nacimiento'] = dateToPage( $data['fecha_nacimiento'] );
    // Recuperamos las localidades por el id de la provincia
    $localidades = getLocalidadesPorIdProvincia( (int) $data['id_provincia'] );

} else {
    /**
     * El campo EMAIL, es un campo unique en la base de datos, y no es un campo obligatorio
     * en el formulario de registro, de manera que nunca puede estar vacío (''). Sí así fuese,
     * generaría un error cuando se intente insertar registros, puesto que no puede haber dos
     * registros con un mismo valor vacío, lo mismo sucede con el campo telefono_linea aunque
     * aquí no habría ningún problema ya que no es un campo unique.
     */
    $data = [
        'id_asociado' => null,'apellido' => null,'nombre' => null,'fecha_nacimiento' => null,'tipo_documento' => null,
        'num_documento' => null,'num_cuil' => null,'condicion_ingreso' => null,'email' => null,'telefono_movil' => null,
        'telefono_linea' => null,'domicilio' => null,'id_provincia' => '0','id_localidad' => '0','sexo' => null
    ];
}

/**
 * MÉTODO POST
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ( !empty( $_POST['token'] ) && Token::validate( $_POST['token'] ) ) {

        // Apellido
        if (array_key_exists('apellido', $_POST)) {
            $data['apellido'] = escape( $_POST['apellido'] );
        }
        if ( !$data['apellido'] ) {
            $errors['apellido'] = $messages['required'];
        } else if ( !onlyletters( $data['apellido'] ) ) {
            $errors['apellido'] = $messages['onlyLetters'];
        } else if ( !minlength( $data['apellido'], $minlength) ) {
            $errors['apellido'] = $messages['minLength'];
        } else if ( !maxlength($data['apellido'], $maxlength) ) {
            $errors['apellido'] = $messages['maxLength'];
        }

        // Nombre
        if (array_key_exists('nombre', $_POST)) {
            $data['nombre'] = escape( $_POST['nombre'] );
        }
        if ( !$data['nombre'] ) {
            $errors['nombre'] = $messages['required'];
        } else if ( !onlyletters( $data['nombre'] ) ) {
            $errors['nombre'] = $messages['onlyLetters'];
        } else if ( !minlength( $data['nombre'], $minlength) ) {
            $errors['nombre'] = $messages['minLength'];
        } else if ( !maxlength($data['nombre'], $maxlength) ) {
            $errors['nombre'] = $messages['maxLength'];
        }

        // Fecha de nacimiento
        if (array_key_exists('fecha_nacimiento', $_POST)) {
            $data['fecha_nacimiento'] = escape( $_POST['fecha_nacimiento'] );
        }
        if ( !$data['fecha_nacimiento'] ) {
            $errors['fecha_nacimiento'] = $messages['required'];
        } else if ( !validate_date( $data['fecha_nacimiento'] ) ) {
            $errors['fecha_nacimiento'] = $messages['valid_date'];
        } else if ( !validLegalAge( calculateAge( $data['fecha_nacimiento'] ) ) ) {
            $errors['fecha_nacimiento'] = $messages['valid_legal_age'];
        }

        // Tipo de documento
        if (array_key_exists('tipo_documento', $_POST)) {
            $data['tipo_documento'] = escape( $_POST['tipo_documento'] );
        }
        if ( !$data['tipo_documento'] ) {
            $errors['tipo_documento'] = $messages['required'];
        } else if ( !in_array( $data['tipo_documento'], ['DNI', 'LC', 'LE'] ) ) {
            $errors['tipo_documento'] = $messages['valid_document_type'];
        }

        // Número de documento
        if (array_key_exists('num_documento', $_POST)) {
            $data['num_documento'] = escape( $_POST['num_documento'] );
        }
        if ( !$data['num_documento'] ) {
            $errors['num_documento'] = $messages['required'];
        } else if ( preg_match('/^[\d]{8}$/', $data['num_documento']) ) {
            
            if ( isset( $_SESSION['aid'] ) ) {
                $rows = existeNumDeDocumentoAsociado( $data['num_documento'], $_SESSION['aid'] );
            } else {
                $rows = existeNumDeDocumentoAsociado( $data['num_documento'] );
            }

            // Unique
            if (count($rows) == 1) {
                $errors['num_documento'] = str_replace(':f', 'número de documento', $messages['unique'] );
            }
        } else {
            $errors['num_documento'] = $messages['valid_document'];
        }

        // Número de cuil
        if (array_key_exists('num_cuil', $_POST)) {
            $data['num_cuil'] = escape( $_POST['num_cuil'] );
        }
        if ( !$data['num_cuil'] ) {
            $errors['num_cuil'] = $messages['required'];
        } else if ( validar_cuit( $data['num_cuil'] ) ) {

            if ( isset( $_SESSION['aid'] ) ) {
                $rows = existeNumDeCuilAsociado( $data['num_cuil'], $_SESSION['aid'] );
            } else {
                $rows = existeNumDeCuilAsociado( $data['num_cuil'] );
            }
            
            // Unique
            if (count($rows) == 1) {
                $errors['num_cuil'] = str_replace(':f', 'número de cuil', $messages['unique'] );
            }
        } else {
            $errors['num_cuil'] = $messages['valid_cuil'];
        }

        // Condición de ingreso
        if (array_key_exists('condicion_ingreso', $_POST)) {
            $data['condicion_ingreso'] = escape( $_POST['condicion_ingreso'] );
        }
        if ( !$data['condicion_ingreso'] ) {
            $errors['condicion_ingreso'] = $messages['required'];
        } else if ( !in_array( $data['condicion_ingreso'], ['ACTIVO', 'ADHERENTE', 'JUBILADO'] ) ) {
            $errors['condicion_ingreso'] = $messages['valid_entry_condition'];
        }

        // E-mail, no es un campo requerido
        if (array_key_exists('email', $_POST)) {
            $data['email'] = escape( $_POST['email'] );
        }
        if ( !$data['email'] ) {
            // $errors['email'] = $messages['required'];
        } else if ( valid_email( $data['email'] ) ) {
            
            if ( isset( $_SESSION['aid'] ) ) {
                $rows = existeEmailAsociado( $data['email'], $_SESSION['aid'] );
            } else {
                $rows = existeEmailAsociado( $data['email'] );
            }
            
            // Unique
            if (count($rows) == 1) {
                $errors['email'] = str_replace(':f', 'correo electrónico', $messages['unique'] );
            }
        } else {
            $errors['email'] = $messages['valid_email'];
        }

        // Teléfono móvil
        if (array_key_exists('telefono_movil', $_POST)) {
            $data['telefono_movil'] = escape( $_POST['telefono_movil'] );
        }
        if ( !$data['telefono_movil'] ) {
            $errors['telefono_movil'] = $messages['required'];
        } else if ( validar_tel( $data['telefono_movil'] ) ) {
            if( isset( $_SESSION['aid'] ) ) {
                $rows = existeTelefonoMovilAsociado( $data['telefono_movil'], $_SESSION['aid'] );
            } else {
                $rows = existeTelefonoMovilAsociado( $data['telefono_movil'] );
            }
            
            // Unique
            if (count($rows) == 1) {
                $errors['telefono_movil'] = str_replace(':f', 'teléfono móvil', $messages['unique'] );
            }
        } else {
            $errors['telefono_movil'] = $messages['valid_mobile_phone'];
        }

        // Teléfono de línea, no es un campo requerido
        if (array_key_exists('telefono_linea', $_POST)) {
            $data['telefono_linea'] = escape( $_POST["telefono_linea"] );
        }
        if ( !$data['telefono_linea'] ) {
            // $errors['telefono_linea'] = $messages['required'];
        } else if ( !validar_tel( $data['telefono_linea'] ) ) {
            $errors['telefono_linea'] = $messages['valid_phone'];
        }

        // Domicilio
        if (array_key_exists('domicilio', $_POST)) {
            $data['domicilio'] = escape( $_POST['domicilio'] );
        }
        if ( !$data['domicilio'] ) {
            $errors['domicilio'] = $messages['required'];
        }

        // Provincia
        if (array_key_exists('id_provincia', $_POST)) {
            $data['id_provincia'] = escape( $_POST["id_provincia"] );
        }
        if ( !$data['id_provincia'] ) {
            $errors['id_provincia'] = $messages['required'];
        } else if( isValidProvinceId( $data['id_provincia'] ) ) {
            // Cargamos las localidades despues de que tenemos el id de provincia
            $localidades = getLocalidadesPorIdProvincia( (int) $data['id_provincia'] );
        } else {
            $errors['id_provincia'] = "Seleccione una provincia de la lista.";
        }

        // Localidad, aquí se verifica que la localidad pertenezca a la provincia selecionada
        if (array_key_exists('id_localidad', $_POST)) {
            $data['id_localidad'] = escape( $_POST["id_localidad"] );
        }
        if ( !$data['id_localidad'] ) {
            $errors['id_localidad'] = $messages['required'];
        } else if( isPositiveInt( $data['id_localidad'] ) )  {
            // Si el id de la provincia es vacío, devolvera 0 filas
            $rows = existeLocalidadDeProvincia( (int) $data['id_localidad'], (int) $data['id_provincia'] );
            
            if (count($rows) == 0) {
                $errors['id_localidad'] = 'Seleccione una localidad de la lista.';
            }
        } else {
            $errors['id_localidad'] = 'Seleccione una localidad de la lista.';
        }
        
        // Sexo
        if (array_key_exists('sexo', $_POST)) {
            $data['sexo'] = escape( $_POST["sexo"] );
        }
        if ( !$data['sexo'] ) {
            $errors['sexo'] = $messages['required'];
        } else if( !in_array( $data['sexo'], ['F', 'M'] ) ) {
            $errors['sexo'] = $messages['valid_sex'];
        }

        /**
         * Si no existen errores
         */
        if( empty($errors) ) {
    
            // Insertar o actualizar datos
            if ( save( $data ) ) {
                /**
                 * Recuperamos el último id insertado seteado en el metódo insertarAsociado(), si la acción fue insertar o
                 * o recuperamos el id que estamos editando si la acción fue de actualización.
                 */
                $id_asociado = isset( $_SESSION['lastInsertId'] ) ? $_SESSION['lastInsertId'] : $_SESSION['aid'];
                // Despues de procesar, eliminamos las variables almacenadas en el array session.
                unset( $_SESSION['aid'] );
                unset( $_SESSION['lastInsertId'] );
                unset( $_SESSION['_token'] );
                // Seteamos el mensaje flash para la vista
                Flash::addFlash('Los datos fueron guardados correctamente.', 'primary');
                // Re dirigimos al usuario a la vista de detalle.
                redirect('/asociado_detalle.php?aid=' . $id_asociado);

            } else {

                Flash::addFlash('Lo sentimos, no pudimos guardar el registro.', 'danger');
                redirect('/');
            }
        }
    }
}

$title = 'Actualizar registro';

$values = [
    'title' => $title,
    'data' => $data,
    'errors' => $errors,
    'localidades' => $localidades,
    'action' => $action
];

render('asociado/add-edit.html', $values);

function save($data) {

    $data['id_asociado'] = $_SESSION['aid'] ?? null;

    if ( $data['id_asociado'] === null ) {
        return insertarAsociado($data);
    }
    return actualizarAsociado($data);
}

function actualizarAsociado($data) {
    $last_modified = date('Y-m-d H:i:s');
    $data['fecha_nacimiento'] = dateToDb($data['fecha_nacimiento']);
    try {
        $db = Db::getInstance();
        // begin the transaction
        $db->beginTransaction();

        // Consulta 1
        $sql = 'UPDATE asociado set apellido = ?, nombre = ?, sexo = ?, fecha_nacimiento = ?, tipo_documento = ?, num_documento = ?, num_cuil = ?,
        condicion_ingreso = ?, email = ?, domicilio = ?, id_localidad = ?, last_modified = ? WHERE id_asociado = ? ; ';
    
        Db::query($sql, capitalize($data['apellido']), capitalize($data['nombre']), $data['sexo'], $data['fecha_nacimiento'], $data['tipo_documento'], 
        $data['num_documento'], $data['num_cuil'], $data['condicion_ingreso'], $data['email'], $data['domicilio'], $data['id_localidad'], 
        $last_modified, $data['id_asociado']);

        // Consulta 2
        $sql = 'UPDATE telefono set telefono_movil = ?, telefono_linea = ?, last_modified = ? WHERE id_asociado = ? ; ';
    
        Db::query($sql, $data['telefono_movil'], $data['telefono_linea'], $last_modified, $data['id_asociado']);

        // commit the transaction
        $db->commit();
    } catch (PDOException $e) {
        // roll back the transaction if something failed
        $db->rollback();
        trigger_error('Error:' . $e->getMessage(), E_USER_ERROR);
        return false;
    }
    return true;
}

function insertarAsociado($data) {
    $created = $last_modified = date('Y-m-d H:i:s');
    $data['fecha_nacimiento'] = dateToDb($data['fecha_nacimiento']);
    try {
        $db = Db::getInstance();
        // begin the transaction
        $db->beginTransaction();

        // Consulta 1
        $sql = 'INSERT INTO asociado (apellido, nombre, sexo, fecha_nacimiento, tipo_documento, num_documento, num_cuil, condicion_ingreso, 
        email, domicilio, id_localidad, created, last_modified) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
    
        Db::query($sql, capitalize($data['apellido']), capitalize($data['nombre']), $data['sexo'], $data['fecha_nacimiento'], $data['tipo_documento'],
        $data['num_documento'], $data['num_cuil'], $data['condicion_ingreso'], $data['email'], $data['domicilio'], $data['id_localidad'], $created, $last_modified);

        // Seteamos el id del nuevo asociado insertado en la base de datos en la variable de sessión, para re dirigir a la página de detalle
        $data['id_asociado'] = $_SESSION['lastInsertId'] = Db::getInstance()->lastInsertId();

        // Consulta 2
        $sql = 'INSERT INTO telefono (telefono_movil, telefono_linea, id_asociado, created, last_modified) VALUES(?, ?, ?, ?, ?)';
    
        Db::query($sql, $data['telefono_movil'], $data['telefono_linea'], $data['id_asociado'], $created, $last_modified);
    
        // commit the transaction
        $db->commit();
    } catch (PDOException $e) {
        // roll back the transaction if something failed
        $db->rollback();
        //trigger_error('Error:' . $e->getMessage(), E_USER_ERROR);
        return false;
    }
    return true;
}