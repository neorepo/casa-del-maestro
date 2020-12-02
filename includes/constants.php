<?php

define('DOCUMENT_ROOT', 'C:\Users\neo\Desktop\cdm');
define('BASE_URL', 'http://localhost:8000/cdm');

// $whitespace = ' \t\n\r\v\f';
// $ascii_lowercase = 'abcdefghijklmnopqrstuvwxyz';
// $ascii_uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
// $spanish_letter = 'áéíóúÁÉÍÓÚÑñÜü';
// $digits = '0123456789';

// $password = "{4_8$\U,mK(zdsg$\RL-lXfDl)ky&Se2(.N)";

define('PEPPER', 'r8UN#uHVX5');// x~&4+ZaG&y
// Constantes
define('LONGITUD_MAXIMA', 40);
define('LONGITUD_MINIMA', 3);

$messages = [
    'required' => 'Este campo es requerido.',
    'onlyLetters' => 'Solo se permiten letras (a-zA-Z), y espacios en blanco.',
    'minLength' => 'Aumenta la longitud a ' . LONGITUD_MINIMA . ' caracteres como mínimo.',
    'maxLength' => 'Reduce la longitud a ' . LONGITUD_MAXIMA . ' caracteres o menos.',
    'valid_date' => 'El formato o la fecha ingresada no es válida.',
    'valid_email' => 'El correo electrónico no es válido.',
    'valid_document_type' => 'El tipo de documento seleccionado no es válido.',
    'valid_entry_condition' => 'La condición de ingreso seleccionada no es válida.',
    'valid_sex' => 'El sexo seleccionado no es válido.',
    'valid_legal_age' => 'Debes tener 18 años o más para poder asociarte. Asegúrate de usar tu fecha de nacimiento real.', /* Age of majority (also known as the "age of maturity") */
    'valid_document' => 'El formato o el número de documento ingresado no es válido.',
    'valid_cuil' => 'El formato o el número de cuil ingresado no es válido.',
    'valid_mobile_phone' => 'El formato o el número de teléfono móvil ingresado no es válido.',
    'valid_phone' => 'El formato o el número de teléfono de línea ingresado no es válido.',
    'unique' => 'Este :f ya se encuentra registrado.'
];