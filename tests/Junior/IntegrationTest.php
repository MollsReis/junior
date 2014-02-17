<?php

use Junior\Server;

class IntegrationTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider serversideProvider
     */
    public function testServerside($inputJSON, $expected)
    {
        $server = new Server(new ExposedClass(), new StubAdapter($inputJSON));

        $this->setOutputCallback(function($json) use ($expected) {
            //TODO handle batch and notify
            $actual = json_decode($json);
            foreach ($expected as $key => $expectedValue) {
                if ($key == 'error') {
                    $this->assertEquals($expectedValue['code'], $actual->error->code);
                    $this->assertEquals($expectedValue['message'], $actual->error->message);
                } else {
                    $this->assertEquals($expectedValue, $actual->{$key});
                }
            }
        });

        $server->process();
    }

    // test cases taken directly from JSON-RPC 2.0 spec (http://www.jsonrpc.org/specification)
    public function serversideProvider()
    {
        return [
            [
                '{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}',
                [ 'jsonrpc' => '2.0', 'result' => 19, 'id' => 1 ]
            ],
            [
                '{"jsonrpc": "2.0", "method": "subtract", "params": [23, 42], "id": 2}',
                [ 'jsonrpc' => '2.0', 'result' => -19, 'id' => 2 ]
            ],
            [
                '{"jsonrpc": "2.0", "method": "subtract", "params": {"subtrahend": 23, "minuend": 42}, "id": 3}',
                [ 'jsonrpc' => '2.0', 'result' => 19, 'id' => 3 ]
            ],
            [
                '{"jsonrpc": "2.0", "method": "subtract", "params": {"minuend": 42, "subtrahend": 23}, "id": 4}',
                [ 'jsonrpc' => '2.0', 'result' => 19, 'id' => 4 ]
            ],
            /*
             * TODO
            [
                '{"jsonrpc": "2.0", "method": "update", "params": [1,2,3,4,5]}',
                [ null ]
            ],
            [
                '{"jsonrpc": "2.0", "method": "foobar"}',
                [ null ]
            ],
            */
            [
                '{"jsonrpc": "2.0", "method": "foobar", "id": "1"}',
                [ 'jsonrpc' => '2.0', 'error' => [ 'code' => -32601, 'message' => 'Method not found: The method does not exist.' ], 'id' => 1 ]
            ],
            [
                '{"jsonrpc": "2.0", "method": "foobar, "params": "bar", "baz]',
                [ 'jsonrpc' => '2.0', 'error' => [ 'code' => -32700, 'message' => 'Parse error: Invalid JSON was received by the server.' ], 'id' => null ]
            ],
            [
                '{"jsonrpc": "2.0", "method": 1, "params": "bar"}',
                [ 'jsonrpc' => '2.0', 'error' => [ 'code' => -32600, 'message' => 'Invalid Request: The JSON sent is not a valid Request object.' ], 'id' => null ]
            ],
            [
                '[
                  {"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"},
                  {"jsonrpc": "2.0", "method"
                ]',
                [ 'jsonrpc' => '2.0', 'error' => [ 'code' => -32700, 'message' => 'Parse error: Invalid JSON was received by the server.' ], 'id' => null ]
            ],
            [
                '[]',
                [ 'jsonrpc' => '2.0', 'error' => [ 'code' => -32600, 'message' => 'Invalid Request: The JSON sent is not a valid Request object.' ], 'id' => null ]
            ],
            [
                '[1]',
                [ 'jsonrpc' => '2.0', 'error' => [ 'code' => -32600, 'message' => 'Invalid Request: The JSON sent is not a valid Request object.' ], 'id' => null ]
            ],
            /*
             * TODO
             [
                '[
                    {"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"},
                    {"jsonrpc": "2.0", "method": "notify_hello", "params": [7]},
                    {"jsonrpc": "2.0", "method": "subtract", "params": [42,23], "id": "2"},
                    {"foo": "boo"},
                    {"jsonrpc": "2.0", "method": "foo.get", "params": {"name": "myself"}, "id": "5"},
                    {"jsonrpc": "2.0", "method": "get_data", "id": "9"}
                ]',
                [ null ]
             ],
             [
                '[
                    {"jsonrpc": "2.0", "method": "notify_sum", "params": [1,2,4]},
                    {"jsonrpc": "2.0", "method": "notify_hello", "params": [7]}
                ]',
                [ null ]
             ],
            */
        ];
    }
}