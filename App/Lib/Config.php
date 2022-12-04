<?php

namespace App\Lib;

class Config {
    public static function load() {
        $path = __DIR__ . "/../.env";

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (strpos($line, "#") === 0) {
                continue;
            }

            list($name, $value) = explode("=", $line, 2);
            $name = trim($name);
            $value = trim($value);

            if (!array_key_exists($name, $_ENV)) {
                putenv(sprintf("%s=%s", $name, $value));
                $_ENV[$name] = $value;
            }
        }
    }

    public static function get(string $key) {
        return $_ENV[$key];
    }
}