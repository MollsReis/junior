<?php
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);
define('ENV', 'TEST');
require_once __DIR__ . '/../src/autoload.php';

class fixtureClass {

    public static $fooJSON = '{ "jsonrpc" : "2.0", "method" : "foo", "id": 1 }';
    public static $fooReturns = 'foo';

    public function foo()
    {
        return self::$fooReturns;
    }
}

