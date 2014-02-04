<?php

use Junior\Server;
use Junior\Serverside\Request;
use Junior\Serverside\Adapter\StandardAdapter;

class ServerTest extends PHPUnit_Framework_TestCase {

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
        $instance = new fixtureClass();
        $server = new Server($instance);

        $request = $server->createRequest(fixtureClass::$fooJSON);
        $this->assertInstanceOf('Junior\Serverside\Request', $request);
    }

    public function testInvoke()
    {
        $instance = new fixtureClass();
        $request = new Request(fixtureClass::$fooJSON);
        $server = new Server($instance);

        $output = $server->invoke($request);
        $this->assertEquals(fixtureClass::$fooReturns, $output);
    }

    public function testInvokeWithParams()
    {
        $instance = new fixtureClass();
        $request = new Request(fixtureClass::$barJSON);
        $server = new Server($instance);

        $output = $server->invoke($request);
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