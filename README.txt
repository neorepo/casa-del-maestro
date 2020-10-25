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

Pattrón dao
https://www.ibm.com/developerworks/library/j-dao/index.html