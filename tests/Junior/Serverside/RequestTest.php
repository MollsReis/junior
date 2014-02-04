<?php

use Junior\Serverside\Request;

class RequestTest extends PHPUnit_Framework_TestCase {

    public function testNewRequest()
    {
        $request = new Request(fixtureClass::$fooJSON);
        $this->assertInstanceOf('Junior\Serverside\Request', $request);
    }

    public function testMethod()
    {
        $request = new Request(fixtureClass::$fooJSON);
        $request->method = 'foo';
    }

    public function testIsValid()
    {
        $this->markTestSkipped();
    }

    public function testIsNotValid()
    {
        $this->markTestSkipped();
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