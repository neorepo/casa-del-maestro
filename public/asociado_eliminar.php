<?php

// configuration
require '../includes/bootstrap.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Seteamos un mensaje por defecto
    $message = 'El registro no pudo ser eliminado, intentelo más tarde.';
    $class = 'danger';

    if (!empty($_POST['token']) && Token::validate($_POST['token'])) {

        if (!empty($_POST['aid']) && isPositiveInt($_POST['aid'])) {

            // Podríamos haber llamado al metódo getAsociadoPorId pero esta consulta es más eficiente
            $rows = Db::query('SELECT id_asociado FROM asociado WHERE deleted = 0 AND id_asociado = ? LIMIT 1; ', (int) $_POST['aid']);
            
            // Si existe el asociado
            if( count($rows) == 1 ) {
                $asociado = $rows[0];
                // Eliminamos el asociado
                if( eliminarAsociado( $asociado['id_asociado'] ) ) {
                    unset($_SESSION['_token']);
                    $message = 'El registro fue eliminado correctamente.';
                    $class = 'info';
                }
            }
        }
    }
    Flash::addFlash($message, $class);
}

redirect('/');