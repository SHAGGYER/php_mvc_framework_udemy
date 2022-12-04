<?php

namespace App\Lib;

class Kernel {
    public Database $db;

    private Router $router;
    
    public function run() {
        Config::load();

        Database::connect();

        Container::set("router", Router::class);
        $this->loadRoutes();
    }

    private function loadRoutes() {
        $router = require_once __DIR__ . "/../routes.php";
        $this->router = $router;
        $this->router->run();
    }
}