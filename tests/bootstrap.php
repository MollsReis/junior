<?php
ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);
define('ENV', 'TEST');
$path = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..'.DIRECTORY_SEPARATOR.'lib' . DIRECTORY_SEPARATOR;
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
require_once('Junior.php');
?>
