<?php
// configuration
require '../includes/bootstrap.php';

require '../reports/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $format = null;
    $errors = [];

    $data = ['format' => isset($_POST['format']) ? $_POST['format'] : null];

    if (array_key_exists('format', $data)) {
        $format = $data['format'];
    }

    if (!$format) {
        $errors['format'] = 'Seleccione un formato para exportar.';
    }
    elseif (!in_array($format, ['xls', 'xlsx'])) {
        $errors['format'] = 'El formato seleccionado no es válido.';
    }

    // Si no hay errores
    if (empty($errors)) {

        $filename = 'lista_de_asociados-' . date('d-m-Y');
        $class = null;
        $content_type = 'Content-Type: application/';

        switch ($format) {
            case 'xls':
                $filename .= '.xls';
                $class = 'Xls';
                $content_type .= 'vnd.ms-excel';
            break;
            case 'xlsx':
                $filename .= '.xlsx';
                $class = 'Xlsx';
                $content_type .= 'vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            break;
        }

        $usuarioLogueado = $_SESSION['nombre'];

        // strftime("%d/%m/%Y",a.fecha_nacimiento) AS fecha_nacimiento
        $sql = 'SELECT a.apellido, a.nombre, a.sexo, a.fecha_nacimiento, a.tipo_documento, a.num_documento, a.num_cuil,
    a.condicion_ingreso, a.email, t.telefono_movil, t.telefono_linea, a.domicilio, l.nombre AS localidad, l.cp, p.nombre AS provincia
    FROM asociado a INNER JOIN telefono t ON a.id_asociado = t.id_asociado INNER JOIN localidad l ON a.id_localidad = l.id_localidad
    INNER JOIN provincia p ON l.id_provincia = p.id_provincia WHERE a.deleted = 0 ORDER BY a.apellido, a.nombre;';

        $rows = Db::query($sql);

        $numberOfrows = count($rows);

        // Si tenemos registros exportamos, de lo contrario devolvemos al index
        if ($numberOfrows > 0) {
            $spreadsheet = new Spreadsheet();

            // Seteamos las propiedades del documento
            $spreadsheet->getProperties()
                ->setCreator('Casa del Maestro y Previsión Social')
                ->setLastModifiedBy($usuarioLogueado)->setTitle('Lista de Asociados')
                ->setSubject('')
                ->setDescription('')
                ->setKeywords('')
                ->setCategory('');

            // Encabezados
            $spreadsheet->setActiveSheetIndex(0);
            $spreadsheet->getActiveSheet()->setCellValue('A1', 'APELLIDO');
            $spreadsheet->getActiveSheet()->setCellValue('B1', 'NOMBRE');
            $spreadsheet->getActiveSheet()->setCellValue('C1', 'SEXO');
            $spreadsheet->getActiveSheet()->setCellValue('D1', 'FEC. NAC.');
            $spreadsheet->getActiveSheet()->setCellValue('E1', 'TIPO DOC.');
            $spreadsheet->getActiveSheet()->setCellValue('F1', 'NÚM. DOC.');
            $spreadsheet->getActiveSheet()->setCellValue('G1', 'NÚM. CUIL');
            $spreadsheet->getActiveSheet()->setCellValue('H1', 'CONDICIÓN');
            $spreadsheet->getActiveSheet()->setCellValue('I1', 'E-MAIL');
            $spreadsheet->getActiveSheet()->setCellValue('J1', 'TEL. MÓVIL');
            $spreadsheet->getActiveSheet()->setCellValue('K1', 'TEL. LÍNEA');
            $spreadsheet->getActiveSheet()->setCellValue('L1', 'DOMICILIO');
            $spreadsheet->getActiveSheet()->setCellValue('M1', 'LOCALIDAD');
            $spreadsheet->getActiveSheet()->setCellValue('N1', 'CÓD. POSTAL');
            $spreadsheet->getActiveSheet()->setCellValue('O1', 'PROVINCIA');

            // Set font bold
            // $spreadsheet->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
            // Set column widths
            // $spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
            // $row = 2;
            // foreach($rows as $country) {
            //     $spreadsheet->getActiveSheet()->setCellValue('A' . $row, $country['localidad']);
            //     $spreadsheet->getActiveSheet()->setCellValue('B' . $row, $country['codigo_postal']);
            //     $spreadsheet->getActiveSheet()->setCellValue('C' . $row, $country['provincia']);
            //     $row += 1;
            // }
            // Agregamos datos a las celdas
            $j = 0;
            for ($i = 2;$i <= $numberOfrows + 1;++$i) {
                $spreadsheet->getActiveSheet()
                ->setCellValue('A' . $i, $rows[$j]['apellido'])->setCellValue('B' . $i, $rows[$j]['nombre'])
                ->setCellValue('C' . $i, $rows[$j]['sexo'])->setCellValue('D' . $i, $rows[$j]['fecha_nacimiento'])
                ->setCellValue('E' . $i, $rows[$j]['tipo_documento'])->setCellValue('F' . $i, $rows[$j]['num_documento'])
                ->setCellValue('G' . $i, $rows[$j]['num_cuil'])->setCellValue('H' . $i, $rows[$j]['condicion_ingreso'])
                ->setCellValue('I' . $i, $rows[$j]['email'])->setCellValue('J' . $i, $rows[$j]['telefono_movil'])
                ->setCellValue('K' . $i, $rows[$j]['telefono_linea'])->setCellValue('L' . $i, $rows[$j]['domicilio'])
                ->setCellValue('M' . $i, $rows[$j]['localidad'])->setCellValue('N' . $i, $rows[$j]['cp'])
                ->setCellValue('O' . $i, $rows[$j]['provincia']);
                $j++;
            }

            // Renombramos la hoja de cálculo
            $spreadsheet->getActiveSheet()->setTitle('Lista de asociados');
            // $spreadsheet->getActiveSheet()->setShowGridLines(false);
            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $spreadsheet->setActiveSheetIndex(0);

            header($content_type);
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            // If you're serving to IE 9, then the following may be needed
            header('Cache-Control: max-age=1');

            // If you're serving to IE over SSL, then the following may be needed
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
            header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header('Pragma: public'); // HTTP/1.0
            $writer = IOFactory::createWriter($spreadsheet, $class);
            $writer->save('php://output');

            exit();
        }
        else {
            Flash::addFlash('No hay registros disponibles para exportar!', 'warning');
            redirect('/');
        }
    }
    Flash::addFlash($errors['format'], 'danger');
}
redirect('/');