Consideraciones acerca de los campos de la base de datos

E-mail:
Hay ocasiones en que no todos las personas que vienen a asociarse cuentan con un e-mail,
la razón es que son personas mayores que no les interesa contar con uno.
Por consiguiente, el campo dirección que hace referencia al correo electrónico en la tabla email deberá permitir
campos NULL, esto quiere decir que se podrá insertar la palabra NULL, lo cual significa que la persona no tiene 
un correo electrónico.
Asi mismo el campo mencionado anteriormente (dirección) tendrá o será INDICE ÚNICO, lo cual significa que no existirán
dos direcciones iguales, si así fuese se produciría un error, inclusive si se tratará de ingresar valores vacíos repetidos
ejemplo:

INSERT INTO email (direccion) VALUES('');
INSERT INTO email (direccion) VALUES('');

También se produciría un error.
Entonces si un campo en nuestro formulario no es obligatorio, puede estar o no estar, deberemos setear el campo de la base
de datos para que permita valores NULL, y en la inserción de los datos setear dicho campo a NULL, si es que el asociado no
cuenta con un correo electrónico.

Lo mismo sucede con el campo teléfono de línea.

Respecto al campo teléfono de línea, no es ÍNDICE ÚNICO, lo que quiere decir que podrán haber dos o más registros con el mismo
número de teléfono de línea, ya que podría suceder que dos o más personas que vengan a asociarse vivan en el mismo domicilio en
cuyo caso tendrán probablemente el mismo teléfono de línea.

Despues de analizar la posible estructura de la tabla teléfono cuya relación de cardinalidad con la tabla asociado es de 1 a n,
un asociado puede tener varios números de teléfono (teléfono hogar, teléfono móvil, teléfono trabajo etc.), según la información
disponible, se permitirián dos números de teléfono, el móvil y el de línea, siendo este último no obligatorio (puede estar o no estar).

Se pensó en principio una tabla con la siguiente estructura, que almacenará en el campo número tanto el número movil con el de línea:

teléfono		
id	    numero	          id_asociado
1  2617475748-2614100193       1

Esta tabla almacenaría si existiera el teléfono de línea junto con el teléfono móvil.

-------------------------------

Luego se pensó lo siguiente:

teléfono		
id	   numero       tipo	  id_asociado
1    2617475748     movil          1
2    2614100193     linea          1

En esta estructura al recuperar los números de teléfono del asociado, si tuviera ambos, tendriamos un array multidimensional, lo cual es más
complicado de manejar al unir las tablas.

-------------------------------

Por último se decidio utilizar la siguiente estructura:

teléfono		
id	   telefono_movil     telefono_linea	  id_asociado
1        2617475748         2614200174             1
2        2614100193            NULL                2

En esta estructura tendriamos campos NULL para los asociados que no cuenten con un teléfono de línea, sin embargo es más sencillo de manejar.
Con el teléfono móvil no tendriamos problemas ya que es un campo obligatorio.

-------------------------------------

Consideraciones acerca de la validación de un número entero positivo.
Antes de consultar a la base de datos se validaba que el id fuese un número entero positivo.
por ejemplo:

SELECT * FROM asociado WHERE id_asociado = 3;

Despues de algunas pruebas, casi no es necesario hacer la validación de un entero, ya que en el momento de
consultar a la base de datos si le enviamos un valor por ejemplo:

SELECT * FROM asociado WHERE id_asociado = '/*/*mmm';

Devolverá vacío, pero no generará un error. Con lo cual hasta este momento no veo la necesidad de validar si
el id_asociado contiene un número entero positivo.
A menos que solo quiera conectarme a la base de datos si y solo si existe un número entero positivo.
El conectarse a la base de datos es una operación costosa, así que solo accederemos a ella, sí y solo sí el/los ids son
números enteros positivos, o los campos de consulta son valores válidos.

Planeación de consultas
https://www.sqlite.org/queryplanner.html

DER: Grado y Cardinalidad de las relaciones
https://www.youtube.com/watch?v=DFbCvXNptmY&list=PLMCtO4953x-7S0RhIEoPHifalcGAwwKHt&index=87

Tutoriales de referencia SQL
https://es.khanacademy.org/computing/computer-programming/sql

Stanford Lagunita
https://online.stanford.edu/lagunita-learning-platform

Buscar datos de personas
https://www.dateas.com/es/consulta_cuit_cuil

Constancia de inscripción en Afip
https://seti.afip.gob.ar/padron-puc-constancia-internet/ConsultaConstanciaAction.do

Datos reales
https://www.mockaroo.com/


Investigar acerca de esto
// session_name('ID');
$path = rtrim(dirname($_SERVER["PHP_SELF"]), "/\\");
session_set_cookie_params([
    'lifetime' => 0,
    'path' => $path,
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => true,
    'httponly' => true,
    'samesite' => 'lax'
]);


Sintaxis heredoc
// $form = <<<HTML
// <form action="" method="post" enctype="multipart/form-data">
//   Select image to upload:
//   <input type="file" name="fileToUpload" id="fileToUpload">
//   <input type="submit" value="Upload Image" name="submit">
// </form>
// HTML;

// https://www.php.net/manual/es/language.constants.predefined.php
echo __DIR__; es equivalente a dirname(__FILE__);

neo.code.edu@gmail.com

Para generación del FAVICON
https://favicon.io/

El codigo ASCII
https://elcodigoascii.com.ar/codigos-ascii-extendidos/signo-ordinal-femenino-genero-codigo-ascii-166.html
codigo ascii 166 = ª ( Ordinal femenino, indicador de genero femenino ) => (ª alt + 166 ) undécima
( entidad HTML = &ordf; )
codigo ascii 167 = º ( Ordinal masculino, indicador de genero masculino ) => (º alt + 167 ) undécimo
codigo ascii 248 = ° ( Signo de grado, anillo ) => (° alt + 248 )

vocales con diéresis
ä alt + 132
ë alt + 137
ï alt + 139
ö alt + 148
ü alt + 129

Ä alt + 142
Ë alt + 211
Ï alt + 216
Ö alt + 153
Ü alt + 154

https://github.com/PHPOffice/PhpSpreadsheet/blob/master/samples/templates/sampleSpreadsheet.php

Buscador de prestador telefónico
https://numeracion.enacom.gob.ar/



<?php
// https://www.youtube.com/watch?v=lUNwKeRygyI
require_once 'TCPDF/tcpdf.php';

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// El ancho total de la página es de 190
$pdf->AddPage();

$data = file_get_contents('MOCK_DATA.json');
$rows = json_decode($data, true);

// Columns title
$headers = array_keys( $rows[0] );
$w = [10, 40, 40, 70, 30];

// Antes de imprimir los encabezados seteamos la fuente a negrita
$pdf->SetFont('', 'B');

// Imprimir los encabezados
$n = count( $headers );
for ($i= 0; $i < $n; $i++) { 
    $pdf->Cell($w[$i], 7, $headers[$i], 1, 0, 'L', 0);
}
// Despues de imprimir los encabezados, damos un salto de línea
$pdf->Ln();

// Devolvemos el estilo de fuente por defecto
$pdf->SetFont('');

// border LR => left and right
foreach($rows as $row) {
    $pdf->Cell($w[0], 6, $row['id'], 'LR', 0, 'L', 0);
    $pdf->Cell($w[1], 6, $row['first_name'], 'LR', 0, 'L', 0);
    $pdf->Cell($w[2], 6, $row['last_name'], 'LR', 0, 'L', 0);
    $pdf->Cell($w[3], 6, $row['email'], 'LR', 0, 'L', 0);
    $pdf->Cell($w[4], 6, $row['gender'], 'LR', 0, 'L', 0);
    $pdf->Ln(); // Salto de línea
}
$pdf->Cell(array_sum($w), 0, '', 'T'); // border T => Top

$pdf->Ln();

$pdf->SetRightMargin(8.5);

$pdf->Cell(0, 0, date('j/n/Y H:i:s'), 0, 0, 'R');

// Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M')

$pdf->Output('test.pdf', 'I');


<?php

require_once 'db/Db.php';
require_once 'config/Config.php';

require_once('TCPDF/tcpdf.php');

// extend TCPF with custom functions
class MYPDF extends TCPDF {

	// Load table data from file
	public function LoadData() {
        $sql = 'SELECT 
          l.nombre AS localidad,
          l.codigo_postal,
          p.nombre AS provincia
        FROM
          localidad AS l 
        INNER JOIN provincia AS p 
          ON l.id_provincia = p.id_provincia LIMIT 36;';

        return Db::query( $sql );
    }

	// Colored table
	public function ColoredTable($header,$data) {
		// Colors, line width and bold font
		$this->SetFillColor(17, 61, 118); // crimson color 220, 20, 60
		$this->SetTextColor(255);
		$this->SetDrawColor(221, 221, 221); // color línea de borde
		$this->SetLineWidth(.2); // ancho de borde default 0.3
		$this->SetFont('', 'B'); // BI => bold italic
		// Header
		$ancho_celda = array(85, 35, 60); // 180
		$num_headers = count($header);
		for($i = 0; $i < $num_headers; ++$i) {
			$this->Cell($ancho_celda[$i], 7, $header[$i], 1, 0, 'L', 1);
		}
		$this->Ln();
		// Color and font restoration
		$this->SetFillColor(241, 241, 241);
		$this->SetTextColor(0);
		$this->SetFont('');
		// Data
        $fill = 0;
        // Cell(ancho, alto, texto, borde, , alineación, indica si el fondo es pintado o no)
		foreach($data as $row) {
			$this->Cell($ancho_celda[0], 6, $row['localidad'], 'LR', 0, 'L', $fill);
			$this->Cell($ancho_celda[1], 6, $row['codigo_postal'], 'LR', 0, 'L', $fill);
			$this->Cell($ancho_celda[2], 6, $row['provincia'], 'LR', 0, 'L', $fill);
            $this->Ln(); // Salto de línea
            $fill=!$fill; // Para generar el striped de las filas
        }
        $this->Cell(array_sum($ancho_celda), 0, '', 'T');
        // $this->Ln();
        // $this->Cell(array_sum($ancho_celda), 6, date('j/n/Y H:i:s'), '', 0, 'R');
	}
}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Nicola Asuni');
$pdf->SetTitle('TCPDF Example 011');
$pdf->SetSubject('TCPDF Tutorial');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// set default header data
// size logo image 354 * 118
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
// $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
// $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

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
$pdf->SetFont('helvetica', '', 12); // 12pts = 16px

// add a page
$pdf->AddPage();

// column titles
$header = array('LOCALIDAD', 'CÓD. POSTAL', 'PROVINCIA');

// data loading
$data = $pdf->LoadData();

// column titles
// $header = array_keys( $data[0] );

// print colored table
$pdf->ColoredTable($header, $data);

$pdf->Ln();

$pdf->SetRightMargin(13.5);

$pdf->Cell(0, 0, date('j/n/Y H:i:s'), 0, 0, 'R');

// ---------------------------------------------------------

// close and output PDF document
$pdf->Output('test.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+


// Dao Pattern
https://www.oracle.com/java/technologies/dataaccessobject.html

public int insertCustomer(...) {
    // Implement insert customer here.
    // Return newly created customer number
    // or a -1 on error
  }
  
  public boolean deleteCustomer(...) {
    // Implement delete customer here
    // Return true on success, false on failure
  }

  public Customer findCustomer(...) {
    // Implement find a customer here using supplied
    // argument values as search criteria
    // Return a Transfer Object if found,
    // return null on error or if not found
  }

  public boolean updateCustomer(...) {
    // implement update record here using data
    // from the customerData Transfer Object
    // Return true on success, false on failure or
    // error
  }

  public RowSet selectCustomersRS(...) {
    // implement search customers here using the
    // supplied criteria.
    // Return a RowSet. 
  }

  public Collection selectCustomersTO(...) {
    // implement search customers here using the
    // supplied criteria.
    // Alternatively, implement to return a Collection 
    // of Transfer Objects.
  }