<?php

use Junior\Server;
use Junior\Serverside\Exception as ServerException;

class IntegrationTest extends PHPUnit_Framework_TestCase {

    public function assertCorrectResponse($expected, $actual)
    {
        foreach ($expected as $key => $expectedValue) {
            if ($key == 'error') {
                $this->assertEquals($expectedValue['code'], $actual->error->code);
                $this->assertEquals($expectedValue['message'], $actual->error->message);
            } else {
                $this->assertEquals($expectedValue, $actual->{$key});
            }
        }
    }

    /**
     * @dataProvider serversideProvider
     */
    public function testServerside($inputJSON, $expected)
    {
        $server = new Server(new ExposedClass(), new StubAdapter($inputJSON));

        $this->setOutputCallback(function($outputJSON) use ($expected) {
            if (is_null($expected)) {
                $this->assertEmpty($outputJSON);
            } else {
                $this->assertCorrectResponse($expected, json_decode($outputJSON));
            }
        });

        $server->process();
    }

    public function testServersideBatch()
    {
        $inputJSON = '[
            {"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"},
            {"jsonrpc": "2.0", "method": "notify_hello", "params": [7]},
            {"jsonrpc": "2.0", "method": "subtract", "params": [42,23], "id": "2"},
            {"foo": "boo"},
            {"jsonrpc": "2.0", "method": "foo.get", "params": {"name": "myself"}, "id": "5"},
            {"jsonrpc": "2.0", "method": "getData", "id": "9"}
        ]';

        $expected = [
            [ 'jsonrpc' => '2.0', 'result' => 7, 'id' => 1 ],
            [ 'jsonrpc' => '2.0', 'result' => 19, 'id' => 2 ],
            [ 'jsonrpc' => '2.0', 'error' => [ 'code' => -32600, 'message' => ServerException::MESSAGE_INVALID_REQUEST ], 'id' => null ],
            [ 'jsonrpc' => '2.0', 'error' => [ 'code' => -32601, 'message' => ServerException::MESSAGE_METHOD_DOES_NOT_EXIST ], 'id' => null ],
            [ 'jsonrpc' => '2.0', 'result' => [ "hello", 5 ], 'id' => 2 ],
        ];

        $server = new Server(new ExposedClass(), new StubAdapter($inputJSON));

        $this->setOutputCallback(function($outputJSON) use ($expected) {
            $actualOutput = json_decode($outputJSON);
            foreach ($expected as $key => $expectedResponse) {
                //TODO fix this
                //$this->assertCorrectResponse($expected, $actualOutput[$key]);
            }
        });

        $server->process();
    }

    public function testServersideBatchNotify()
    {
        $inputJSON = '[
            {"jsonrpc": "2.0", "method": "notifySum", "params": [1,2,4]},
            {"jsonrpc": "2.0", "method": "notifyHello", "params": [7]}
        ]';

        $server = new Server(new ExposedClass(), new StubAdapter($inputJSON));

        $this->setOutputCallback(function($outputJSON) { $this->assertEmpty($outputJSON); });

        $server->process();
    }

    // All test cases taken directly from JSON-RPC 2.0 spec (http://www.jsonrpc.org/specification)
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
            [
                '{"jsonrpc": "2.0", "method": "update", "params": [1,2,3,4,5]}',
                null
            ],
            [
                '{"jsonrpc": "2.0", "method": "notifyFoobar"}',
                null
            ],
            [
                '{"jsonrpc": "2.0", "method": "foobar", "id": "1"}',
                [ 'jsonrpc' => '2.0', 'error' => [ 'code' => -32601, 'message' => ServerException::MESSAGE_METHOD_DOES_NOT_EXIST ], 'id' => 1 ]
            ],
            [
                '{"jsonrpc": "2.0", "method": "foobar, "params": "bar", "baz]',
                [ 'jsonrpc' => '2.0', 'error' => [ 'code' => -32700, 'message' => ServerException::MESSAGE_INVALID_JSON ], 'id' => null ]
            ],
            [
                '{"jsonrpc": "2.0", "method": 1, "params": "bar"}',
                [ 'jsonrpc' => '2.0', 'error' => [ 'code' => -32600, 'message' => ServerException::MESSAGE_INVALID_REQUEST ], 'id' => null ]
            ],
            [
                '[
                  {"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"},
                  {"jsonrpc": "2.0", "method"
                ]',
                [ 'jsonrpc' => '2.0', 'error' => [ 'code' => -32700, 'message' => ServerException::MESSAGE_INVALID_JSON ], 'id' => null ]
            ],
            [
                '[]',
                [ 'jsonrpc' => '2.0', 'error' => [ 'code' => -32600, 'message' => ServerException::MESSAGE_INVALID_REQUEST ], 'id' => null ]
            ],
            [
                '[1]',
                [ 'jsonrpc' => '2.0', 'error' => [ 'code' => -32600, 'message' => ServerException::MESSAGE_INVALID_REQUEST ], 'id' => null ]
            ],
        ];
    }
}