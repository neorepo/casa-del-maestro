<?php

class ErrorHandler {

    private static $errors = [];

    public static function hasErrors() {
        return count(self::$errors) > 0;
    }

    public static function getErrors() {
        return self::$errors;
    }

    private static function addError($key, $value) {
        self::$errors[$key][] = $value;
    }
}