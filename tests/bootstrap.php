<?php
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);
define('ENV', 'TEST');
require_once 'Spray' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Spray.php';
require_once 'PHPUnit/' . DIRECTORY_SEPARATOR . 'Autoload.php';
require_once __DIR__ . '/../lib/autoload.php';