<?php

spl_autoload_register(function ($class) {
    $path = __DIR__ . "/../" . ucfirst(str_replace("\\", "/", $class)) . ".php";
    require_once $path;
});