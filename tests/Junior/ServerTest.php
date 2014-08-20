<?php

use Junior\Server,
    Junior\Serverside\Request,
    Junior\Serverside\BatchRequest,
    Junior\Serverside\NotifyRequest,
    Junior\Serverside\Adapter\StandardAdapter;

class ServerTest extends PHPUnit_Framework_TestCase {

    public $server;

    public function setUp()
    {
        $instance = new FixtureClass();
        $adapter = new StandardAdapter();
        $this->server = new Server($instance, $adapter);
    }

    public function testNewServer()
    {
        $instance = new FixtureClass();
        $adapter = new StandardAdapter();

        $server = new Server($instance, $adapter);
        $this->assertInstanceOf('Junior\Server', $server);
    }

    public function testNewServerNoAdapter()
    {
        $instance = new FixtureClass();

        $server = new Server($instance);
        $this->assertAttributeInstanceOf('Junior\Serverside\Adapter\StandardAdapter', 'adapter', $server);
    }

    /**
     * @dataProvider createRequestProvider
     */
    public function testCreateRequest($json, $expectedClass)
    {
        $request = $this->server->createRequest($json);
        $this->assertInstanceOf($expectedClass, $request);
    }

    public function createRequestProvider()
    {
        return [
            [FixtureClass::$fooJSON, 'Junior\Serverside\Request'],
            [FixtureClass::$batchJSON, 'Junior\Serverside\BatchRequest'],
            [FixtureClass::$notifyJSON, 'Junior\Serverside\NotifyRequest']
        ];
    }

    /**
     * @dataProvider invokeProvider
     */
    public function testInvoke($json, $expectedOutput)
    {
        if ($json == FixtureClass::$batchJSON) {
            $request = new BatchRequest(json_decode($json));

        } elseif ($json == FixtureClass::$notifyJSON) {
            $request = new NotifyRequest(json_decode($json));

        } else {
            $request = new Request(json_decode($json));
        }

        $output = $this->server->invoke($request);
        $this->assertEquals($expectedOutput, $output);
    }

    public function invokeProvider() {
        return [
            [ FixtureClass::$fooJSON, FixtureClass::$fooReturns ],
            [ FixtureClass::$barJSON, FixtureClass::$barReturns ],
            [ FixtureClass::$batchJSON, FixtureClass::$batchReturns ],
            [ FixtureClass::$notifyJSON, null ]
        ];
    }

    /**
     * @expectedException     Junior\Serverside\Exception
     * @expectedExceptionCode Junior\Serverside\Exception::CODE_METHOD_DOES_NOT_EXIST
     */
    public function testInvokeMethodDoesNotExist()
    {
        $request = new Request(json_decode(FixtureClass::$methodDoesNotExistJSON));
        $this->server->invoke($request);
        $this->fail();
    }

    /**
     * @expectedException     Junior\Serverside\Exception
     * @expectedExceptionCode Junior\Serverside\Exception::CODE_METHOD_NOT_AVAILABLE
     */
    public function testInvokeMethodIsPrivate()
    {
        $request = new Request(json_decode(FixtureClass::$privateMethodJSON));
        $this->server->invoke($request);
        $this->fail();
    }

    /**
     * @expectedException     Junior\Serverside\Exception
     * @expectedExceptionCode Junior\Serverside\Exception::CODE_INVALID_PARAMS
     */
    public function testInvokeWrongNumberOfParams()
    {
        $request = new Request(json_decode(FixtureClass::$wrongNumberOfParams));
        $this->server->invoke($request);
        $this->fail();
    }

    public function testCreateResponse()
    {
        $response = $this->server->createResponse(0, FixtureClass::$fooReturns);
        $this->assertInstanceOf('Junior\Serverside\Response', $response);
    }

    public function testCreateErrorResponse()
    {
        $response = $this->server->createErrorResponse(0, FixtureClass::$fooReturns, 0);
        $this->assertInstanceOf('Junior\Serverside\Response', $response);
    }
}