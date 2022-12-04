<?php

namespace App\Lib;

class Container {
    private static array $services = [];

    public static function get(string $key) {
        return self::$services[$key];
    }

    public static function set(string $key, $value) {
        if (is_string($value)) {
            self::$services[$key] = new $value();
        } elseif (is_object($value)) {
            self::$services[$key] = $value;
        }
    }
}