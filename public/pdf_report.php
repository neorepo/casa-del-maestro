<?php

// configuration
require '../includes/bootstrap.php';

// Include the main TCPDF library (search for installation path).
require_once('../TCPDF/tcpdf.php');

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING,
    // array(0,64,255), array(220,20,60) // Texto del header/color línea divisoria
);
$pdf->setFooterData(
	// array(220,20,60), array(0,64,128) // Color número de página/color línea divisoria
); 

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set font
$pdf->SetFont('helvetica', 'B', 12);

// add a page
$pdf->AddPage();

$pdf->Write(16, 'Información de Asociado', '', 0, 'L', true, 0, false, false, 0);

$pdf->SetFont('helvetica', '', 10);

// -----------------------------------------------------------------------------

$data = getAsociadoPorId();

$data['sexo'] = ($data['sexo'] == 'F') ? 'Femenino' : 'Masculino';

$tbl = <<<EOD
<table style="padding-top: 8px;">
    <tbody>
        <tr>
            <th>Fecha de Alta</th>
            <td>$data[created]</td>
        </tr>
        <tr>
            <th>Apellidos</th>
            <td>$data[apellido]</td>
        </tr>
        <tr>
            <th>Nombres</th>
            <td>$data[nombre]</td>
        </tr>
        <tr>
            <th>Sexo</th>
            <td>$data[sexo]</td>
        </tr>
        <tr>
            <th>Fecha de Nacimiento</th>
            <td>$data[fecha_nacimiento]</td>
        </tr>
        <tr>
            <th>Documento</th>
            <td>$data[tipo_documento] - $data[num_documento]</td>
        </tr>
        <tr>
            <th>Número de Cuil</th>
            <td>$data[num_cuil]</td>
        </tr>
        <tr>
            <th>Tipo de Asociado</th>
            <td>$data[condicion_ingreso]</td>
        </tr>
        <tr>
            <th>Correo Electrónico</th>
            <td>$data[email]</td>
        </tr>
        <tr>
            <th>Teléfono Móvil</th>
            <td>$data[telefono_movil]</td>
        </tr>
        <tr>
            <th>Teléfono de Línea</th>
            <td>$data[telefono_linea]</td>
        </tr>
        <tr>
            <th>Domicilio</th>
            <td>$data[domicilio]</td>
        </tr>
        <tr>
            <th>Localidad</th>
            <td>$data[localidad]</td>
        </tr>
        <tr>
            <th>Código Postal</th>
            <td>$data[cp]</td>
        </tr>
        <tr>
            <th>Provincia</th>
            <td>$data[provincia]</td>
        </tr>
    </tbody>
</table>
EOD;

$pdf->writeHTML($tbl, true, false, false, false, '');

//Close and output PDF document
$pdf->Output('informacion-de-asociado.pdf', 'I');
