<?php

class Token {

    public static function csrf() {
        $_SESSION['_token'] = self::generate();
        return $_SESSION['_token'];
    }

    private static function generate($length = 32) {
        if (!isset($length) || intval($length) <= 8) {
            $length = 32;
        }
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes($length));
        }
        if (function_exists('mcrypt_create_iv')) {
            return bin2hex(mcrypt_create_iv($length, MCRYPT_DEV_URANDOM));
        }
        if (function_exists('openssl_random_pseudo_bytes')) {
            return bin2hex(openssl_random_pseudo_bytes($length));
        }
    }

    private static function salt(){
        return substr(strtr(base64_encode(hex2bin(self::generate(32))), '+', '.'), 0, 44);
    }

    public static function validate($token) {
        if ( !isset($token) || !hash_equals($_SESSION['_token'], $token) ) {
            return false;
        }
        return true;
    }
}