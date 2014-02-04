<?php

use Junior\Serverside\Request;

class RequestTest extends PHPUnit_Framework_TestCase {

    public function testNewRequest()
    {
        $request = new Request(fixtureClass::$fooJSON);
        $this->assertInstanceOf('Junior\Serverside\Request', $request);
    }

    public function testGetMethod()
    {
        $request = new Request(fixtureClass::$fooJSON);
        $request->method = 'foo';
    }

    public function testGetParams()
    {
        $request = new Request(fixtureClass::$barJSON);
        $request->method = [ 1, 2, 3 ];
    }

    public function testIsValid()
    {
        $this->markTestSkipped();
    }

    public function testIsNotValid()
    {
        $this->markTestSkipped();
    }

    public function testIsNotNotify()
    {
        $request = new Request(fixtureClass::$fooJSON);
        $this->assertFalse($request->isNotify());
    }

    public function testIsNotBatch()
    {
        $request = new Request(fixtureClass::$fooJSON);
        $this->assertFalse($request->isBatch());
    }

    public function testIsNotify()
    {
        $this->markTestSkipped();
    }

    public function testIsBatch()
    {
        $this->markTestSkipped();
    }
}