<?php

namespace App\Lib;

class Response {
    public static function json(array $data, int $status = 200): void {
        header("Content-Type: application/json");
        http_response_code($status);
        echo json_encode($data);
        exit;
    }
}