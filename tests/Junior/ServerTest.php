<?php

class ServerTest extends PHPUnit_Framework_TestCase {

    public function testNewServer()
    {
        $instance = new StdClass();
        $adapter = new Junior\Serverside\Adapter\StandardAdapter();
        $server = new Junior\Server($instance, $adapter);
        $this->assertInstanceOf('Junior\Server', $server);
    }

    public function testNewServerNoAdapter()
    {
        $instance = new StdClass();
        $server = new Junior\Server($instance);
        $adapter = new Junior\Serverside\Adapter\StandardAdapter();
        $this->assertAttributeEquals($adapter, 'adapter', $server);
    }

    public function testCreateRequest()
    {
        $rawJSON = '{ "foo": "bar" }';
        $server = new Junior\Server(new StdClass());
        $request = $server->createRequest($rawJSON);
        $this->assertInstanceOf('Junior\Serverside\Request', $request);
    }

    public function testInvoke()
    {
        $this->markTestSkipped();
    }

    public function testCreateResponse()
    {
        $this->markTestSkipped();
    }
}