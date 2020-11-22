<?php

// configuration
require '../includes/bootstrap.php';

// Include the main TCPDF library (search for installation path).
require_once('../reports/tcpdf/tcpdf.php');

// Extend the TCPDF class to create custom Header and Footer
class PDF extends TCPDF {

    //Page header
    public function Header() {
        // Logo
        $image_file = K_PATH_IMAGES.'cdm_logo.png';
        //Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false, $alt=false, $altimgs=array())
        $this->Image($image_file, 40, 30, '', 30, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $this->Ln(3);
        // Set font
        $this->SetFont('helvetica', 'B', 11);
        // Title
        // Cell(ancho, alto, texto, borde, ln(salto de línea), alineación, indica si el fondo es pintado o no)

        $this->Cell(55, 5, "", 0, 0, 'C', 0);
        $this->Cell(105, 5, "Casa del Maestro y Previsión Social", 0, 1, 'C', 0);

        $this->Cell(55, 5, "", 0, 0, 'C', 0);
        $this->Cell(105, 5, "Dirección: Alem 184, Mendoza -5500", 0, 1, 'C', 0);

        $this->Cell(55, 5, "", 0, 0, 'C', 0);
        $this->Cell(105, 5, "Teléfono: 261-4200192", 0, 1, 'C', 0);

        $this->Cell(55, 5, "", 0, 0, 'C', 0);
        $this->Cell(105, 5, "Cuit: 33-53512127-9", 0, 1, 'C', 0);

        $this->Cell(55, 5, "", 0, 0, 'C', 0);
        $this->Cell(105, 5, "E-mail: mutualcasadelmaestro@gmail.com", 0, 1, 'C', 0);
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-25);
        // Set font
        $this->SetFont('helvetica', '', 11);
        // Page number
        // $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
        $this->Cell(0, 5, date('j/n/Y'), 0, false, 'R', 0);
    }
}

// create new PDF document
$pdf = new PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Julio Cesar');
$pdf->SetTitle('Información del asociado');
// $pdf->SetSubject('TCPDF Tutorial');
// $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// remove default header/footer
// $pdf->setPrintHeader(false);
// $pdf->setPrintFooter(false);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

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
$pdf->SetFont('helvetica', 'B', 11);

// add a page
$pdf->AddPage();

// Cell(ancho, alto, texto, borde, , alineación, indica si el fondo es pintado o no)
$pdf->Ln(47);

$pdf->Cell(0, 7, "Información del asociado", 0, 1, 'C', 0);
$pdf->Ln();

// $pdf->Cell(50, 7, "Fecha de alta:", 'B', 0, 'L', 0);
// $pdf->Cell(110, 7, "01/12/2017 10:33", 'B', 1, 'L', 0);
// $pdf->Cell(50, 7, "Apellidos:", 'B', 0, 'L', 0);
// $pdf->Cell(110, 7, "Ypanaque Silva", 'B', 1, 'L', 0);
// $pdf->Cell(50, 7, "Nombres:", 'B', 0, 'L', 0);
// $pdf->Cell(110, 7, "Julio Cesar", 'B', 1, 'L', 0);
// $pdf->Cell(50, 7, "Sexo:", 'B', 0, 'L', 0);
// $pdf->Cell(110, 7, "Masculino", 'B', 1, 'L', 0);
// $pdf->Cell(50, 7, "Fecha de nacimiento:", 'B', 0, 'L', 0);
// $pdf->Cell(110, 7, "03/03/1981", 'B', 1, 'L', 0);
// $pdf->Cell(50, 7, "Documento:", 'B', 0, 'L', 0);
// $pdf->Cell(110, 7, "DNI - 94269698", 'B', 1, 'L', 0);
// $pdf->Cell(50, 7, "Número de cuil:", 'B', 0, 'L', 0);
// $pdf->Cell(110, 7, "20-94269698-2", 'B', 1, 'L', 0);
// $pdf->Cell(50, 7, "Cargo:", 'B', 0, 'L', 0);
// $pdf->Cell(110, 7, "Maestranza y servicio", 'B', 1, 'L', 0);
// $pdf->Cell(50, 7, "Correo electrónico:", 'B', 0, 'L', 0);
// $pdf->Cell(110, 7, "jys9102@gmail.com", 'B', 1, 'L', 0);
// $pdf->Cell(50, 7, "Teléfono móvil:", 'B', 0, 'L', 0);
// $pdf->Cell(110, 7, "2615161142", 'B', 1, 'L', 0);
// $pdf->Cell(50, 7, "Teléfono de línea:", 'B', 0, 'L', 0);
// $pdf->Cell(110, 7, "2614200192", 'B', 1, 'L', 0);
// $pdf->Cell(50, 7, "Domicilio:", 'B', 0, 'L', 0);
// $pdf->Cell(110, 7, "B° El Progreso Ma. B - Ca. 7 - 11ª sección San Agustín", 'B', 1, 'L', 0);
// $pdf->Cell(50, 7, "Localidad:", 'B', 0, 'L', 0);
// $pdf->Cell(110, 7, "MENDOZA", 'B', 1, 'L', 0);
// $pdf->Cell(50, 7, "Código postal:", 'B', 0, 'L', 0);
// $pdf->Cell(110, 7, "5500", 'B', 1, 'L', 0);
// $pdf->Cell(50, 7, "Provincia:", 'B', 0, 'L', 0);
// $pdf->Cell(110, 7, "MENDOZA", 'B', 1, 'L', 0);

$data = getAsociadoPorId();

$data['sexo'] = ($data['sexo'] == 'F') ? 'Femenino' : 'Masculino';
$data['num_cuil'] = cuilFormat($data['num_cuil']);

$pdf->SetFont('helvetica', '', 11);

$tbl = <<<EOD
<style>
table {table-layout: fixed;width: 100%;}
table, th, td {border-bottom: 1px solid #000;padding: 4px}
table th {font-weight: bold;width: 30%}
table td {width: 70%}
</style>
<table>
    <tbody>
        <tr>
            <th>Fecha de alta:</th>
            <td>$data[created]</td>
        </tr>
        <tr>
            <th>Apellidos:</th>
            <td>$data[apellido]</td>
        </tr>
        <tr>
            <th>Nombres:</th>
            <td>$data[nombre]</td>
        </tr>
        <tr>
            <th>Sexo:</th>
            <td>$data[sexo]</td>
        </tr>
        <tr>
            <th>Fecha de nacimiento:</th>
            <td>$data[fecha_nacimiento]</td>
        </tr>
        <tr>
            <th>Documento:</th>
            <td>$data[tipo_documento] - $data[num_documento]</td>
        </tr>
        <tr>
            <th>Número de cuil:</th>
            <td>$data[num_cuil]</td>
        </tr>
        <tr>
            <th>Tipo de asociado:</th>
            <td>$data[condicion_ingreso]</td>
        </tr>
        <tr>
            <th>Correo electrónico:</th>
            <td>$data[email]</td>
        </tr>
        <tr>
            <th>Teléfono móvil:</th>
            <td>$data[telefono_movil]</td>
        </tr>
        <tr>
            <th>Teléfono de línea:</th>
            <td>$data[telefono_linea]</td>
        </tr>
        <tr>
            <th>Domicilio:</th>
            <td>$data[domicilio]</td>
        </tr>
        <tr>
            <th>Localidad:</th>
            <td>$data[localidad]</td>
        </tr>
        <tr>
            <th>Código postal:</th>
            <td>$data[cp]</td>
        </tr>
        <tr>
            <th>Provincia:</th>
            <td>$data[provincia]</td>
        </tr>
    </tbody>
</table>
EOD;

$pdf->writeHTML($tbl, true, false, false, false, '');

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('informacion del asociado.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
// function writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=false, $reseth=true, $align='', $autopadding=true)

// function writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='')

// function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M')

// function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='T', $fitcell=false)

// function Text($x, $y, $txt, $fstroke=false, $fclip=false, $ffill=true, $border=0, $ln=0, $align='', $fill=false, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M', $rtloff=false)

// function Write($h, $txt, $link='', $fill=false, $align='', $ln=false, $stretch=0, $firstline=false, $firstblock=false, $maxh=0, $wadj=0, $margin='')