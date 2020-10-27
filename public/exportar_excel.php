<?php

// https://github.com/PHPOffice/PhpSpreadsheet antes PHPExcel

/**
 * Ejemplos
 * https://github.com/PHPOffice/PhpSpreadsheet/blob/master/samples/templates/sampleSpreadsheet.php
 * https://github.com/PHPOffice/PhpSpreadsheet/blob/master/samples/templates/largeSpreadsheet.php
 */

require '../includes/bootstrap.php';
$filename = 'Reporte_asociados_' . date('d-m-Y') . '.xls';

$q = 'SELECT a.apellido, a.nombre, a.sexo, a.fecha_nacimiento, a.tipo_documento, a.num_documento, a.num_cuil, 
a.condicion_ingreso, a.email, t.telefono_movil, t.telefono_linea, a.domicilio, l.nombre AS localidad, l.cp, p.nombre AS provincia
FROM asociado a INNER JOIN telefono t ON a.id_asociado = t.id_asociado INNER JOIN localidad l ON a.id_localidad = l.id_localidad 
INNER JOIN provincia p ON l.id_provincia = p.id_provincia WHERE a.deleted = 0 ORDER BY a.apellido, a.nombre;';

$rows = Db::query($q);

if( count($rows) ) {
    header('Content-type: application/vnd.ms-excel');
    header('Content-disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    // $headers = ['apellido' => 'APELLIDO','nombre' => 'NOMBRE','sexo' => 'SEXO','fecha_nacimiento' => 'FEC. NAC.',
    // 'tipo_documento' => 'TIPO DOC.','num_documento' => 'DOC.','num_cuil' => 'CUIL','condicion_ingreso' => 'CONDICION',
    // 'email' => 'E-MAIL','telefono_movil' => 'TEL. MOVIL','telefono_linea' => 'TEL. LINEA','domicilio' => 'DOMICILIO',
    // 'localidad' => 'LOCALIDAD','cp' => 'CP','provincia' => 'PROVINCIA'];
    
    // array_unshift($rows, $headers);

    $isPrintHeader = false;
    
    foreach ($rows as $key => $row) {
        if (!$isPrintHeader) {
            print implode("\t", array_keys( $row) ) . "\n";
            $isPrintHeader = true;
        }
        // print implode("\t", array_values($row)) . "\n";
        print utf8_decode($row['apellido']). "\t";
        print utf8_decode($row['nombre']). "\t";
        print $row['sexo']. "\t";
        print $row['fecha_nacimiento']. "\t";
        print $row['tipo_documento']. "\t";
        print $row['num_documento']. "\t";
        print $row['num_cuil']. "\t";
        print $row['condicion_ingreso']. "\t";
        print $row['email']. "\t";
        print $row['telefono_movil']. "\t";
        print $row['telefono_linea']. "\t";
        print utf8_decode($row['domicilio']). "\t";
        print utf8_decode($row['localidad']). "\t";
        print $row['cp']. "\t";
        print $row['provincia']. "\n";
    }
} else {
    Flash::addFlash('No hay registros disponibles para exportar!', 'info');
    redirect('/');
}