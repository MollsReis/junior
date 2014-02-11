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

    public function testCreateRequest()
    {
        $request = $this->server->createRequest(fixtureClass::$fooJSON);
        $this->assertInstanceOf('Junior\Serverside\Request', $request);
    }

    public function testCreateNotifyRequest()
    {
        $request = $this->server->createRequest(fixtureClass::$notifyJSON);
        $this->assertInstanceOf('Junior\Serverside\Request', $request);
    }

    public function testCreateBatchRequest()
    {
        $request = $this->server->createRequest(fixtureClass::$batchJSON);
        $this->assertInstanceOf('Junior\Serverside\BatchRequest', $request);
    }

    public function testInvoke()
    {
        $request = new Request(json_decode(fixtureClass::$fooJSON));

        $output = $this->server->invoke($request);
        $this->assertEquals(fixtureClass::$fooReturns, $output);
    }

    public function testInvokeWithParams()
    {
        $request = new Request(json_decode(fixtureClass::$barJSON));

        $output = $this->server->invoke($request);
        $this->assertEquals(fixtureClass::$barReturns, $output);
    }

    public function testInvokeBatch()
    {
        $request = new BatchRequest(json_decode(fixtureClass::$batchJSON));

        $output = $this->server->invoke($request);
        $this->assertEquals(fixtureClass::$batchReturns, $output);
    }

    public function testInvokeNotify()
    {
        $request = new NotifyRequest(json_decode(fixtureClass::$notifyJSON));

        $output = $this->server->invoke($request);
        $this->assertNull($output);
    }

    public function testCreateResponse()
    {
        $response = $this->server->createResponse(fixtureClass::$fooReturns);
        $this->assertInstanceOf('Junior\Serverside\Response', $response);
    }
}