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
Antes de consultar a la base de datos se estaba validando que el id fuese un número entero positivo.
por ejemplo:

SELECT * FROM asociado WHERE id_asociado = 3;

Despues de algunas pruebas, casi no es necesario hacer la validación de un entero, ya que en el momento de
consultar a la base de datos si le enviamos un valor por ejemplo:

SELECT * FROM asociado WHERE id_asociado = '/*/*mmm';

Devolverá vacío, pero no generará un error. Con lo cual hasta este momento no veo la necesidad de validar si
por ejemplo el id_asociado contiene un número entero positivo, a menos que solo me conecte a la base de datos
si y solo si existe un número entero positivo.
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


<a href="/delete.php?id_asociado=<?= $data['id_asociado']; ?>"
                        onclick="return confirm('¿Desea eliminar el registro?');" title="Eliminar registro"
                        class="mx-2"><i class="material-icons" style="color: #dc143b;">delete</i></a>

Sintaxis heredoc
// $form = <<<HTML
// <form action="" method="post" enctype="multipart/form-data">
//   Select image to upload:
//   <input type="file" name="fileToUpload" id="fileToUpload">
//   <input type="submit" value="Upload Image" name="submit">
// </form>
// HTML;

    /**
     * func_get_args ( void ) : array, Obtiene un array de la lista de argumentos de una función.
     * array_slice — Extraer una parte de un array
     */
    public static function query(/* $sql [, ... ] */)
    {
        // SQL statement
        $sql = func_get_arg(0);

        // parameters, if any
        $parameters = array_slice(func_get_args(), 1);

        try {
            $conn = self::getInstance();
            // begin the transaction
            $conn->beginTransaction();
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                // trigger (big, orange) error
                trigger_error($conn->errorInfo()[2], E_USER_ERROR);
                exit;
            }
            $result = $stmt->execute($parameters);
            if ($result === false) return false;
            if ($stmt->columnCount() > 0) {
                // return result set's rows
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            // if query was DELETE, INSERT, or UPDATE
            else {
                // return number of rows affected
                return ($stmt->rowCount() == 1); // true o false
            }
            // commit the transaction
            $conn->commit();
        } catch (PDOException $e) {
            // roll back the transaction if something failed
            $conn->rollback();
            print 'Error: ' . $e->getMessage();
        }

        $conn = null;
    }
}

// https://www.php.net/manual/es/language.constants.predefined.php
echo __DIR__; es equivalente a dirname(__FILE__);