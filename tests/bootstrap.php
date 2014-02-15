<?php
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);
define('ENV', 'TEST');
require_once __DIR__ . '/../src/autoload.php';

class fixtureClass {

    public static $fooJSON = '{ "jsonrpc" : "2.0", "method" : "foo", "id": 1 }',
                  $fooReturns = 'foo',
                  $fooResponse = '{ "jsonrpc" : "2.0", "result" : "foo", "id": 1 }';

    public function foo()
    {
        return self::$fooReturns;
    }

    public static $barJSON = '{ "jsonrpc" : "2.0", "method" : "bar", "params" : [ 1, 2, 3 ], "id": 1 }',
                  $barReturns = 'bar';

    public function bar($a, $b, $c)
    {
        return self::$barReturns;
    }

    public static $batchJSON = '[
        { "jsonrpc" : "2.0", "method" : "foo", "id": 1 },
        { "jsonrpc" : "2.0", "method" : "bar", "params" : [ 1, 2, 3 ], "id": 2 }
    ]';
    public static $batchReturns = [ 'foo', 'bar' ];

    public static $batchJSONWithNotify = '[
        { "jsonrpc" : "2.0", "method" : "bar", "id": 1 },
        { "jsonrpc" : "2.0", "method" : "notifyMethod" },
        { "jsonrpc" : "2.0", "method" : "foo", "params" : [ 1, 2, 3 ], "id": 2 },
        { "jsonrpc" : "2.0", "method" : "notifyMethod" }
    ]',
                  $batchWithNotifyReturns = [ 'bar', 'foo' ],
                  $batchWithNotifyResponse = '[
        { "jsonrpc" : "2.0", "result" : "bar", "id": 1 },
        { "jsonrpc" : "2.0", "result" : "foo", "id": 2 }
    ]';

    public static $notifyJSON = '{ "jsonrpc" : "2.0", "method" : "notifyMethod" }';

    public function notifyMethod()
    {
        // return is ignored
    }

    public static $invalidJSON = '[{}}}',
                  $missingJSONRPC = '{ "method" : "foo", "id": 1 }',
                  $invalidJSONRPC = '{ "jsonrpc" : "foo", "method" : "foo", "id": 1 }',
                  $invalidId = '{ "jsonrpc" : "2.0", "method" : "foo", "id": { "foo" : "bar" } }',
                  $missingMethod = '{ "jsonrpc" : "2.0", "id": 1 }',
                  $illegalMethod = '{ "jsonrpc" : "2.0", "method" : "rpc.foo", "id": 1 }',
                  $invalidParams = '{ "jsonrpc" : "2.0", "method" : "foo", "params" : "bar", "id": 1 }',
                  $methodDoesNotExistJSON = '{ "jsonrpc" : "2.0", "method" : "notHere", "id": 1 }',
                  $wrongNumberOfParams = '{ "jsonrpc" : "2.0", "method" : "bar", "params" : [ 1 ], "id": 1 }';

    public static $privateMethodJSON = '{ "jsonrpc" : "2.0", "method" : "_imShy", "id": 1 }';

    private function _imShy() {
        // will never be called
    }
}

