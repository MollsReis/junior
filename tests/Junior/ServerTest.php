<?php

use Junior\Server;
use Junior\Serverside\Request;
use Junior\Serverside\BatchRequest;
use Junior\Serverside\NotifyRequest;
use Junior\Serverside\Adapter\StandardAdapter;

class ServerTest extends PHPUnit_Framework_TestCase {

    public $server;

    public function setUp()
    {
        $instance = new fixtureClass();
        $adapter = new StandardAdapter();
        $this->server = new Server($instance, $adapter);
    }

    public function testNewServer()
    {
        $instance = new fixtureClass();
        $adapter = new StandardAdapter();

        $server = new Server($instance, $adapter);
        $this->assertInstanceOf('Junior\Server', $server);
    }

    public function testNewServerNoAdapter()
    {
        $instance = new fixtureClass();

        $server = new Server($instance);
        $this->assertAttributeInstanceOf('Junior\Serverside\Adapter\StandardAdapter', 'adapter', $server);
    }


    /**
     * @dataProvider createRequestProvider
     */
    public function testCreateRequest($json, $expectedClass)
    {
        $request = $this->server->createRequest(fixtureClass::$fooJSON);
        $this->assertInstanceOf('Junior\Serverside\Request', $request);
    }

    public function createRequestProvider()
    {
        return [
            [fixtureClass::$fooJSON, 'Junior\Serverside\Request'],
            [fixtureClass::$batchJSON, 'Junior\Serverside\BatchRequest'],
            [fixtureClass::$notifyJSON, 'Junior\Serverside\NotifyRequest']
        ];
    }

    /**
     * @dataProvider invokeProvider
     */
    public function testInvoke($json, $expectedOutput)
    {
        if ($json == fixtureClass::$batchJSON) {
            $request = new BatchRequest(json_decode($json));

        } elseif ($json == fixtureClass::$notifyJSON) {
            $request = new NotifyRequest(json_decode($json));

        } else {
            $request = new Request(json_decode($json));
        }

        $output = $this->server->invoke($request);
        $this->assertEquals($expectedOutput, $output);
    }

    public function invokeProvider() {
        return [
            [ fixtureClass::$fooJSON, fixtureClass::$fooReturns ],
            [ fixtureClass::$barJSON, fixtureClass::$barReturns ],
            [ fixtureClass::$batchJSON, fixtureClass::$batchReturns ],
            [ fixtureClass::$notifyJSON, null ]
        ];
    }

    /**
     * @expectedException     Junior\Serverside\Exception
     * @expectedExceptionCode Junior\Serverside\Exception::CODE_METHOD_DOES_NOT_EXIST
     */
    public function testInvokeMethodDoesNotExist()
    {
        $request = new Request(json_decode(fixtureClass::$methodDoesNotExistJSON));
        $this->server->invoke($request);
        $this->fail();
    }

    /**
     * @expectedException     Junior\Serverside\Exception
     * @expectedExceptionCode Junior\Serverside\Exception::CODE_METHOD_NOT_AVAILABLE
     */
    public function testInvokeMethodIsPrivate()
    {
        $request = new Request(json_decode(fixtureClass::$privateMethodJSON));
        $this->server->invoke($request);
        $this->fail();
    }

    /**
     * @expectedException     Junior\Serverside\Exception
     * @expectedExceptionCode Junior\Serverside\Exception::CODE_INVALID_PARAMS
     */
    public function testInvokeWrongNumberOfParams()
    {
        $request = new Request(json_decode(fixtureClass::$wrongNumberOfParams));
        $this->server->invoke($request);
        $this->fail();
    }

    public function testCreateResponse()
    {
        $response = $this->server->createResponse(fixtureClass::$fooReturns);
        $this->assertInstanceOf('Junior\Serverside\Response', $response);
    }
}