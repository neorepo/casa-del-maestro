<?php

// configuration
require '../includes/bootstrap.php';

// Include the main TCPDF library (search for installation path).
require_once('../reports/tcpdf/tcpdf.php');

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING,
    // array(0,64,255), array(220,20,60) // Texto del header/color línea divisoria
);
/*$pdf->setFooterData(
	array(220,20,60), array(0,64,128) // Color número de página/color línea divisoria
);*/

// set header and footer fonts
                         // Helvetica              font-size: 10
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                         // Helvetica              font-size: 8
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
                               // courier
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
                    //15                27           15
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                      // 5
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                      // 10
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER); // Esta línea genera la línea divisoria del footer con el núm. de pág.

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

$pdf->Write(16, 'Información del asociado', '', 0, 'L', true, 0, false, false, 0);

$pdf->SetFont('helvetica', '', 10);

// -----------------------------------------------------------------------------

$data = getAsociadoPorId();

$data['sexo'] = ($data['sexo'] == 'F') ? 'Femenino' : 'Masculino';
$data['num_cuil'] = cuilFormat($data['num_cuil']);

$tbl = <<<EOD
<style>
table {padding-top: 3px;}
table, th, td {border-bottom: 1px solid #000;padding: 4px}
th{font-weight: bold;}
</style>
<table>
    <tbody>
        <tr>
            <th>Fecha de alta</th>
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
            <th>Fecha de nacimiento</th>
            <td>$data[fecha_nacimiento]</td>
        </tr>
        <tr>
            <th>Documento</th>
            <td>$data[tipo_documento] - $data[num_documento]</td>
        </tr>
        <tr>
            <th>Número de cuil</th>
            <td>$data[num_cuil]</td>
        </tr>
        <tr>
            <th>Tipo de asociado</th>
            <td>$data[condicion_ingreso]</td>
        </tr>
        <tr>
            <th>Correo electrónico</th>
            <td>$data[email]</td>
        </tr>
        <tr>
            <th>Teléfono móvil</th>
            <td>$data[telefono_movil]</td>
        </tr>
        <tr>
            <th>Teléfono de línea</th>
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
            <th>Código postal</th>
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

// -----------------------------------------------------------------------------
$currentDate = date('j/n/Y H:i:s');
$pdf->Ln();

// $html = <<<EOD
// <p style="">$currentDate</p>
// EOD;

// Print text using writeHTMLCell()
$pdf->writeHTMLCell(0, 33, '', '', $currentDate, 0, 1, 0, true, 'R', true);

// $pdf->writeHTML($html, true, false, false, false, 'R');

//Close and output PDF document
$pdf->Output('informacion-del-asociado.pdf', 'I');


// function writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=false, $reseth=true, $align='', $autopadding=true)

// function writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='')

// function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M')

// function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='T', $fitcell=false)

// function Text($x, $y, $txt, $fstroke=false, $fclip=false, $ffill=true, $border=0, $ln=0, $align='', $fill=false, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M', $rtloff=false)

// function Write($h, $txt, $link='', $fill=false, $align='', $ln=false, $stretch=0, $firstline=false, $firstblock=false, $maxh=0, $wadj=0, $margin='')