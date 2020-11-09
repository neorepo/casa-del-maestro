<?php

// configuration
require '../includes/bootstrap.php';

$data = [];
$errors = [];
$numberOferrors = null;

// Las localidades estarán disponibles solo cuando exista el id de la provincia.
// Son 22793 localidades, solo listaremos las que pertenezcan a la provincia seleccionada
$localidades = [];

$edit = array_key_exists('aid', $_GET);

if ($edit) {
    // Recuperamos los datos del asociado de la base de datos
    $data = getAsociadoPorId();
    // Asignamos el id del asociado a la variable de sesión para saber que registro editar, también podemos utilizar el array $_GET
    $_SESSION['aid'] = $data['id_asociado'];
    // Formateamos la fecha de nacimiento: ejm: 2000-03-06 a 06/03/2000
    // $data['fecha_nacimiento'] = dateToTemplate( $data['fecha_nacimiento'] );
    // Recuperamos las localidades por el id de la provincia
    $localidades = getLocalidadesPorIdProvincia( (int) $data['id_provincia'] );

} else {
    if ( isset($_SESSION['aid']) ) {
        unset( $_SESSION['aid'] );
    }
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

        // for security reasons, do not map the whole $_POST
        $data = [
            'apellido' => $_POST['apellido'],
            'nombre' => $_POST['nombre'],
            'fecha_nacimiento' => $_POST['fecha_nacimiento'],
            'tipo_documento' => $_POST['tipo_documento'],
            'num_documento' => $_POST['num_documento'],
            'num_cuil' => $_POST['num_cuil'],
            'condicion_ingreso' => $_POST['condicion_ingreso'],
            'email' => $_POST['email'],
            'telefono_movil' => $_POST['telefono_movil'],
            'telefono_linea' => $_POST['telefono_linea'],
            'domicilio' => $_POST['domicilio'],
            'id_provincia' => $_POST['id_provincia'],
            'id_localidad' => $_POST['id_localidad'],
            'sexo' => $_POST['sexo'] ?? null // Si el campo sexo no es seleccionado 
        ];

        // Map $data no $_POST
        foreach ($data as $key => $value) {
            if ( array_key_exists( $key, $_POST ) ) {
                $data[$key] = escape( $_POST[$key] );
            }
        }

        // Validaciones, el array de mensajes de error se encuentra en la carpeta includes/bootstrap

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

            // Si no tengo el email, no puedo insertar un string vácio por que el campo es unique. Leer README.txt
            $data['email'] = null;
            
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

            // Si no tengo el telefono de línea, puedo insertar un string vácio por que el campo no es unique
            // pero, para mantener todo bien, se insertará un valor null
            $data['telefono_linea'] = null;

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

        // Número de errores
        $numberOferrors = count( $errors );

        /**
         * Si no existen errores en el array
         */
        if( /*empty($errors)*/ $numberOferrors === 0 ) {

            $returnValue = save( $data );
            // Si el valor de retorno es true 
            if ( $returnValue ) {
                $lastInsertId = null;
                // Si el valor de retorno no es un valor booleano, entonces tenemos el último id insertado
                if( !is_bool($returnValue) ) {
                    // Recuperamos el último id insertado
                    $lastInsertId = $returnValue;
                }

                $id_asociado = $lastInsertId ?? $_SESSION['aid'];
                // Despues de procesar, eliminamos las variables almacenadas en el array session.
                unset( $_SESSION['aid'] );
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

$title = $edit ? 'Editar asociado' : 'Registrar asociado';

$values = [
    'title' => $title,
    'data' => $data,
    'errors' => $errors,
    'numberOferrors' => $numberOferrors,
    'localidades' => $localidades,
    'edit' => $edit
];

render('asociado/agregar-editar.html', $values);

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
    // Devuelve verdadero en caso de éxito, falso en caso de error
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

        // Seteamos el id del nuevo asociado insertado en la base de datos, para re dirigir a la página de detalle
        $data['id_asociado'] = Db::getInstance()->lastInsertId();

        // Consulta 2
        $sql = 'INSERT INTO telefono (telefono_movil, telefono_linea, id_asociado, created, last_modified) VALUES(?, ?, ?, ?, ?)';
    
        Db::query($sql, $data['telefono_movil'], $data['telefono_linea'], $data['id_asociado'], $created, $last_modified);
    
        // commit the transaction
        $db->commit();
    } catch (PDOException $e) {
        // roll back the transaction if something failed
        $db->rollback();
        trigger_error('Error:' . $e->getMessage(), E_USER_ERROR);
        return false; // Deberíamos devolver -1 en caso de error si estuvieramos en JAVA
    }
    return $data['id_asociado']; // Devolvemos el último id insertado ( lastInsertId() devuelve un tipo string ).
}