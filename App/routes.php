<?php
use App\Lib\Container;
use App\Middleware\AuthenticateMiddleware;

$router = Container::get("router");

$router->get("/api/auth/init", "AuthController@init");
$router->get("/api/auth/protected", "AuthController@test")->middleware(AuthenticateMiddleware::class);
$router->post("/api/auth/register", "AuthController@register");
$router->post("/api/auth/login", "AuthController@login");

return $router;