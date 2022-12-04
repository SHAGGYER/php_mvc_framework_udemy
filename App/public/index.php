<?php
error_reporting(E_ALL);

session_start();

use App\Lib\Kernel;
require_once "../bootstrap.php";

$kernel = new Kernel();
$kernel->run();