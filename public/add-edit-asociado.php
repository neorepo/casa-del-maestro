<?php

// configuration
require '../includes/bootstrap.php';

$data = [];
$errors = [];

// Las localidades estarán disponibles solo cuando exista el id de la provincia
$localidades = [];

$action = array_key_exists('aid', $_GET);

if ($action) {
    // Recuperamos los datos del asociado de la base de datos
    $data = getAsociadoPorId();
    // Asignamos el id del asociado a la variable de sesión para saber que registro editar, también podemos utilizar el array $_GET
    $_SESSION['aid'] = $data['id_asociado'];
    // Formateamos la fecha de nacimiento: ejm: 2000-03-06 a 06/03/2000
    $data['fecha_nacimiento'] = dateToTemplate( $data['fecha_nacimiento'] );
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
        'telefono_linea' => null,'domicilio' => null,'id_provincia' => null,'id_localidad' => null,'sexo' => null
    ];
}

/**
 * MÉTODO POST
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ( !empty( $_POST['token'] ) && Token::validate( $_POST['token'] ) ) {

        foreach ($data as $key => $value) {

            if ( array_key_exists( $key, $_POST ) ) {

                $data[$key] = escape( $_POST[$key] );
            }
        }

        // El array de mensajes se encuentra en la carpeta includes/bootstrap

        // Apellido
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
        if ( !$data['fecha_nacimiento'] ) {
            $errors['fecha_nacimiento'] = $messages['required'];
        } else if ( !validate_date( $data['fecha_nacimiento'] ) ) {
            $errors['fecha_nacimiento'] = $messages['valid_date'];
        } else if ( !validLegalAge( calculateAge( $data['fecha_nacimiento'] ) ) ) {
            $errors['fecha_nacimiento'] = $messages['valid_legal_age'];
        }

        // Tipo de documento
        if ( !$data['tipo_documento'] ) {
            $errors['tipo_documento'] = $messages['required'];
        } else if ( !in_array( $data['tipo_documento'], ['DNI', 'LC', 'LE'] ) ) {
            $errors['tipo_documento'] = $messages['valid_document_type'];
        }

        // Número de documento
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
        if ( !$data['condicion_ingreso'] ) {
            $errors['condicion_ingreso'] = $messages['required'];
        } else if ( !in_array( $data['condicion_ingreso'], ['ACTIVO', 'ADHERENTE', 'JUBILADO'] ) ) {
            $errors['condicion_ingreso'] = $messages['valid_entry_condition'];
        }

        // E-mail, no es un campo requerido
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
        if ( !$data['telefono_linea'] ) {
            // $errors['telefono_linea'] = $messages['required'];
        } else if ( !validar_tel( $data['telefono_linea'] ) ) {
            $errors['telefono_linea'] = $messages['valid_phone'];
        }

        // Domicilio
        if ( !$data['domicilio'] ) {
            $errors['domicilio'] = $messages['required'];
        }

        // Provincia
        if ( !$data['id_provincia'] ) {
            $errors['id_provincia'] = $messages['required'];
        } else if( isValidProvinceId( $data['id_provincia'] ) ) {

            // Cargamos las localidades despues de que tenemos el id de provincia
            $localidades = getLocalidadesPorIdProvincia( (int) $data['id_provincia'] );
            
        } else {
            $errors['id_provincia'] = "Seleccione una provincia de la lista.";
        }

        // Localidad, aquí se verifica que la localidad pertenezca a la provincia selecionada
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

$title = $action ? 'Editar asociado' : 'Agregar asociado';

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