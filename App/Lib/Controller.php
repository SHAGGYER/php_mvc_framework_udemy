<?php

namespace App\Lib;

class Controller {
    public function __construct()
    {
        Request::parseIncoming();
    }
}