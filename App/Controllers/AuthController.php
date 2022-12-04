<?php

namespace App\Controllers;

use App\Lib\Authentication;
use App\Lib\Controller;
use App\Lib\Request;
use App\Lib\Response;
use App\Models\User;

class AuthController extends Controller {
    public function init() {
        $users = User::with(["roles"])->select()->get();

        return [
            "message" => "Hello World",
            "users" => $users
        ];
    }

    public function login() {
        if (Authentication::attempt(Request::body("email"), Request::body("password"))) {
            $token = Authentication::getUser()->createToken();
            return ["content" => ["token" => $token]];
        }

        Response::json(["message" => "Invalid credentials"], 401);
    }

    public function register() {
        $user = new User();
        $user->email = Request::body("email");
        $user->password = password_hash(Request::body("password"), PASSWORD_BCRYPT);
        $user->save();

        return ["content" => $user];
    }

    public function test() {
        Response::json(["message" => "Success"]);
    }
}