<?php

/**
 * Application configuration.
 */
final class Config {

    /** @var array config data */
    private static $DATA = null;

    /**
     * @return array
     * @throws Exception
     */
    public static function getConfig($section = null) {
        // === Tiene en cuenta la igualdad de contenido y tipo
        if ($section === null) {
            return self::getData();
        }
        $data = self::getData();
        if (!array_key_exists($section, $data)) {
            throw new Exception('Unknown config section: ' . $section);
        }
        return $data[$section];
    }

    /**
     * @return array
     */
    private static function getData() {
        if (self::$DATA !== null) {
            return self::$DATA;
        }
        self::$DATA = parse_ini_file(__DIR__ . '/config.ini', true);
        return self::$DATA;
    }
}
