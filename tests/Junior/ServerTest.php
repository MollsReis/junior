<?php

use Junior\Server;
use Junior\Serverside\Request;
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
        $this->markTestSkipped();
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
        $this->markTestSkipped();
    }

    public function testInvokeNotify()
    {
        $this->markTestSkipped();
    }

    public function testCreateResponse()
    {
        $this->markTestSkipped();
    }
}