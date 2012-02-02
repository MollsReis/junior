<?php
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);
define('ENV', 'TEST');
require_once '../../spray/src/Spray.php'; //TODO make a submodule of Spray
require_once 'PHPUnit/Autoload.php';
require_once '../lib/Junior.php';
