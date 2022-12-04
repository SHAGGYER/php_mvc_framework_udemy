<?php

namespace App\Lib;

class Request {
    private static array $body = [];
    private static array $query = [];

    public static function body(string $key = null, $default = null) {
        return self::$body[$key] ?? $default;
    }

    public static function query(string $key = null, $default = null) {
        return self::$query[$key] ?? $default;
    }

    public static function parseIncoming() {
        foreach ($_GET as $key => $value) {
            self::$query[$key] = $value;
        }
        
        foreach ($_POST as $key => $value) {
            self::$body[$key] = $value;
        }

        $json = file_get_contents("php://input");
        if ($json) {
            $json = json_decode($json, true);
            foreach ($json as $key => $value) {
                self::$body[$key] = $value;
            }
        }
    }
}