<?php

class Validator {

    private static $data;
    
    private static $errors = [];

    private static $errorMessages = [
        'required' => 'Completa este campo.',
        'onlyletters' => 'Solo se permiten letras (a-z) y espacios en blanco.',
        'onlynumbers' => 'Solo se permiten números (0-9).',
        'minlength' => 'Aumenta la longitud a :c caracteres como mínimo.',
        'maxlength' => 'Reduce la longitud a :c caracteres o menos.',
        'matches' => 'El campo confirmar contraseña no coincide con el campo contraseña.',
        'valid_documento' => 'Formato o número de documento inválido.',
        'valid_email' => 'Dirección de correo electrónico no válida.',
        'valid_date' => 'El formato o la fecha ingresada no es válida.',
        'valid_age' => 'Asegúrate de usar tu fecha de nacimiento real.',
        'unique' => 'Este :f ya se encuentra registrado.'
    ];

    public static function validateForm($data, $rules) {

        self::$data = $data;

        $ruleKeys = array_keys($rules);

        foreach ($data as $field => $value) {
            if (in_array($field, $ruleKeys)) {
                self::validateField([
                    'field' => $field,
                    'value' => $value,
                    'rules' => $rules[$field]
                ]);
            }
        }
    }

    private static function validateField($item) {

        $field = $item['field'];

        foreach ($item['rules'] as $rule => $condition) {
            if (!call_user_func_array([self::class, $rule], [$field, $item['value'], $condition])) {
                self::addError(
                        $field,
                        str_replace([':f', ':c'], [$field, $condition], self::$errorMessages[$rule])
                );
            }
        }
    }

    public static function hasErrors() {
        return count(self::$errors) > 0;
    }

    public static function getErrors() {
        return self::$errors;
    }

    private static function addError($key, $value) {
        self::$errors[$key][] = $value;
    }

    /**
     * Funciones de validación
     */
    private function onlyletters($field, $value, $condition) {
        return preg_match('/^[A-Za-záéíóúÁÉÍÓÚÑñÜü\' ]+$/', $value);
    }

    private function onlynumbers($field, $value, $condition) {
        return preg_match('/^[\d]+$/', $value);
    }

    private function minlength($field, $value, $minlength) {
        $txtlen = mb_strlen(trim($value));
        return ($txtlen >= $minlength);
    }

    private function maxlength($field, $value, $maxlength) {
        $txtlen = mb_strlen(trim($value));
        return ($txtlen <= $maxlength);
    }

    private function required($field, $value, $condition) {
        return !empty(trim($value));
    }

    private function valid_email($field, $email, $condition) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        return preg_match('/^[\w.-]+@[\w.-]+\.[A-Za-z]{2,6}$/', $email);
    }

    private function valid_documento($field, $num_documento, $condition) {
        return preg_match('/^[\d]{8}$/', $num_documento);
    }

    private function validate_date($field, $date, $condition) {
        $matches = [];
        $pattern = '/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})$/';
        if (!preg_match($pattern, $date, $matches)) return false;
        //checkdate ( int $month , int $day , int $year ) : bool checkdate(12, 31, 2000)
        if (!checkdate($matches[2], $matches[1], $matches[3])) return false;
        // return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        return true;
    }

    private function valid_age($field, $date, $condition) {
        if ( !array_key_exists($field, self::$errors) ) {
            $age = (int) self::get_age($date);
            if ($age >= $condition) {
                return true;
            }
        }
        return false;
    }

    private function get_age($dateOfBirth) {
        $today = date_create( date("Y-m-d") );
        $dateOfBirth = date_create( str_replace('/', '-', $dateOfBirth) );
        
        if($dateOfBirth > $today) return -1;
        
        $diff = date_diff( $dateOfBirth, $today );
        return $diff->format('%y');
    }

    private function matches($field, $value, $condition) {
        return $value === self::$data[$condition];
    }

    /**
     * Funciones de validación base de datos
     */
    private function unique($field, $value, $condition) {
        $q = 'SELECT ' . $field . ' FROM ' . $condition . ' WHERE ' . $field . ' = ? LIMIT 1 ;';
        $rows = Db::query($q, $value);
        return count($rows) != 1;
    }
}