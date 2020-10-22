<?php

// configuration
require '../includes/bootstrap.php';

$action = array_key_exists('aid', $_GET);

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

if ($action) {
    // Recuperamos los datos del asociado de la base de datos
    $data = getAsociadoPorId();
    // Asignamos el id del asociado a la variable de sesión para saber que registro editar
    $_SESSION['aid'] = $data['id_asociado'];
    $data['fecha_nacimiento'] = dateToPage( $data['fecha_nacimiento'] );
    $localidades = getLocalidadesPorIdProvincia( (int) $data['id_provincia'] );
} else {
    /**
     * El campo EMAIL es un campo unique en la base de datos, y no es un campo obligatorio
     * en el formulario de registro, de manera que nunca puede estar vacío (''). Sí así fuese,
     * generaría un error cuando se intente insertar registros, puesto que no puede haber dos
     * registros con un mismo valor vacío, lo mismo sucede con el campo telefono_linea aunque
     * aquí no habría ningún problema ya que no es un campo unique.
     */
    $data = ['id_asociado' => '','apellido' => '','nombre' => '','fecha_nacimiento' => '','tipo_documento' => '0',
    'num_documento' => '','num_cuil' => '','condicion_ingreso' => '0','email' => null,'telefono_movil' => '',
    'telefono_linea' => null,'domicilio' => '','id_provincia' => '0','id_localidad' => '0','sexo' => ''
    ];
}

/**
 * MÉTODO POST
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!empty($_POST['token']) && Token::validate( $_POST['token'] )) {

        unset($_SESSION['_token']);

        // Validación del apellido
        if (!empty($_POST['apellido'])) {

            $data['apellido'] = escape( $_POST['apellido'] );

            if ( !onlyletters( $data['apellido'] ) ) {

                $errors['apellido'] = $messages['onlyLetters'];

            } elseif ( !minlength( $data['apellido'], $minlength) ) {

                $errors['apellido'] = $messages['minLength'];

            } elseif ( !maxlength($data['apellido'], $maxlength) ) {

                $errors['apellido'] = $messages['maxLength'];
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

        // Validación de la fecha de nacimiento
        if (!empty($_POST['fecha_nacimiento'])) {

            $data['fecha_nacimiento'] = escape( $_POST["fecha_nacimiento"] );
            
            if ( !validate_date( $data['fecha_nacimiento'] ) ) {
                $errors['fecha_nacimiento'] = $messages['valid_date'];
            } else {

                $edad = calculateAge( $data['fecha_nacimiento'] );
                
                if ( !validLegalAge($edad) ) {
                    $errors['fecha_nacimiento'] = $messages['valid_legal_age'];
                }
            }
        } else {
            $errors['fecha_nacimiento'] = $messages['required'];
        }

        // Validación de tipo de documento
        if (!empty($_POST['tipo_documento'])) {

            $data['tipo_documento'] = escape( $_POST["tipo_documento"] );

            if( !in_array( $data['tipo_documento'], ['DNI', 'LC', 'LE'] ) ) {

                $errors['tipo_documento'] = $messages['valid_document_type'];

            }
        } else {
            $errors['tipo_documento'] = $messages['required'];
        }

        // Validación de número de documento
        if (!empty($_POST['num_documento'])) {

            $data['num_documento'] = escape( $_POST["num_documento"] );

            if ( preg_match('/^[\d]{8}$/', $data['num_documento']) ) {
                
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
        } else {
            $errors['num_documento'] = $messages['required'];
        }

        // Validación del número de cuil
        if (!empty($_POST['num_cuil'])) {

            $data['num_cuil'] = escape( $_POST["num_cuil"] );

            if ( validar_cuit( $data['num_cuil'] ) ) {

                if( isset( $_SESSION['aid'] ) ) {
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
        } else {
            $errors['num_cuil'] = $messages['required'];
        }

        // Validación de la condición de ingreso
        if (!empty($_POST['condicion_ingreso'])) {

            $data['condicion_ingreso'] = escape( $_POST["condicion_ingreso"] );

            if( !in_array( $data['condicion_ingreso'], ['ACTIVO', 'ADHERENTE', 'JUBILADO'] ) ) {

                $errors['condicion_ingreso'] = $messages['valid_entry_condition'];

            }
        } else {
            $errors['condicion_ingreso'] = $messages['required'];
        }

        // Validación del email
        if (!empty($_POST['email'])) {

            $data['email'] = escape( $_POST['email'] );

            if ( valid_email( $data['email'] ) ) {
                
                if( isset( $_SESSION['aid'] ) ) {
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
        }
        // No es un campo requerido
        // else {
        //     $errors['email'] = 'Por favor, ingrese su correo electrónico.';
        // }

        // Validación del teléfono móvil
        if (!empty($_POST['telefono_movil'])) {

            $data['telefono_movil'] = escape( $_POST["telefono_movil"] );

            if ( validar_tel( $data['telefono_movil'] ) ) {
                
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
        } else {
            $errors['telefono_movil'] = $messages['required'];
        }

        // Validación del teléfono de línea
        if (!empty($_POST['telefono_linea'])) {

            $data['telefono_linea'] = escape( $_POST["telefono_linea"] );

            if ( !validar_tel( $data['telefono_linea'] ) ) {
                $errors['telefono_linea'] = $messages['valid_phone'];
                
            }
        }

        // No esta validado el domicilio
        if (!empty($_POST['domicilio'])) {
            $data['domicilio'] = escape( $_POST["domicilio"] );
        } else {
            $errors['domicilio'] = $messages['required'];
        }

        // Validación del id de la provincia
        if (!empty($_POST['id_provincia'])) {

            $data['id_provincia'] = escape( $_POST["id_provincia"] );
            
            if( isValidProvinceId( $data['id_provincia'] ) ) {
                // Cargamos las localidades despues de que tenemos el id de provincia
                $localidades = getLocalidadesPorIdProvincia( (int) $data['id_provincia'] );
            } else {
                $errors['id_provincia'] = "Seleccione una provincia de la lista.";
            }
        } else {
            $errors['id_provincia'] = $messages['required'];
        }

        // Validación del id de la localidad, aquí también se verifica que la localidad pertenezca a la provincia selecionada
        if ( !empty($_POST['id_localidad']) ) {

            $data['id_localidad'] = escape( $_POST["id_localidad"] );

            if( isPositiveInt( $data['id_localidad'] ) )  {

                // Si el id de la provincia es vacío, devolvera 0 filas
                $rows = existeLocalidadDeProvincia( (int) $data['id_localidad'], (int) $data['id_provincia'] );

                if (count($rows) == 0) {
                    $errors['id_localidad'] = 'Seleccione una localidad de la lista.';
                }
            } else {
                $errors['id_localidad'] = 'Seleccione una localidad de la lista.';
            }
        } else {
            $errors['id_localidad'] = $messages['required'];
        }

        // Validación del sexo
        if (!empty($_POST['sexo'])) {

            $data['sexo'] = escape( $_POST["sexo"] );

            if( !in_array( $data['sexo'], ['F', 'M'] ) ) {

                $errors['sexo'] = $messages['valid_sex'];

            }
    
        } else {
            $errors['sexo'] = $messages['required'];
        }

        /**
         * Si no existen errores
         */
        if( empty($errors) ) {
    
            // Insertar o actualizar datos
            if ( save( $data ) ) {
                // Recuperamos el id del asociado seteado en el metódo insertarAsociado() para re dirigir a la página de detalle
                $id_asociado = $_SESSION['aid'];
                //Despues de procesar todo eliminamos el id almacenado en el array session
                unset($_SESSION['aid']);
                Flash::addFlash('Los datos fueron guardados correctamente.', 'primary');
                redirect('/asociado_detalle.php?aid=' . $id_asociado);
            } else {
                unset($_SESSION['aid']);
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
        //trigger_error('Error:' . $e->getMessage(), E_USER_ERROR);
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

        // Seteamos el id del nuevo asociado insertado en la base de datos en la variable de sessión, 
        // Para re dirigir a la página de detalle
        $data['id_asociado'] = $_SESSION['aid'] = Db::getInstance()->lastInsertId();

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