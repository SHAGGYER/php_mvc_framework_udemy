<?php

namespace App\Middleware;

use App\Helpers\Helpers;
use App\Lib\Response;

class AuthenticateMiddleware {
    public function handle() {
        if ($token = Helpers::getBearerToken()) {
            // Login user
        } else {
            Response::json(["message" => "Unauthorized"], 401);
            exit;
        }
    }
}