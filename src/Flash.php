<?php

final class Flash
{

    const FLASHES_KEY = '_flashes';

    private static $flashes = null;

    private function __construct()
    {
    }

    public static function hasFlashes()
    {
        self::initFlashes();
        return count(self::$flashes) > 0;
    }

    public static function addFlash($message, $class = 'success', $dismissible = false)
    {
        if (!strlen(trim($message))) {
            throw new Exception('No se puede insertar un mensaje flash vacío.');
        }
        self::initFlashes();

        self::$flashes [] = ['message' => $message, 'class' => $class];
    }

    /**
     * Get flash messages and clear them.
     * @return array flash messages
     */
    public static function getFlashes()
    {
        self::initFlashes();
        $copy = self::$flashes;
        self::$flashes = [];
        return $copy;
    }

    private static function initFlashes()
    {
        if (self::$flashes !== null) {
            return;
        }
        if (!array_key_exists(self::FLASHES_KEY, $_SESSION)) {
            $_SESSION[self::FLASHES_KEY] = [];
        }
        self::$flashes = &$_SESSION[self::FLASHES_KEY];
    }
}
