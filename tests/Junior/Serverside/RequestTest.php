<?php

use Junior\Serverside\Request;
use Junior\Serverside\NotifyRequest;
use Junior\Serverside\BatchRequest;

class RequestTest extends PHPUnit_Framework_TestCase {

    public function testNewRequest()
    {
        $request = new Request(json_decode(fixtureClass::$fooJSON));
        $this->assertInstanceOf('Junior\Serverside\Request', $request);
    }

    public function testGetMethod()
    {
        $request = new Request(json_decode(fixtureClass::$fooJSON));
        $this->assertEquals('foo', $request->method);
    }

    public function testGetParams()
    {
        $request = new Request(json_decode(fixtureClass::$barJSON));
        $this->assertEquals([ 1, 2, 3 ], $request->params);
    }

    public function testIsValid()
    {
        $request = new Request(json_decode(fixtureClass::$fooJSON));
        $this->assertTrue($request->isValid());
    }

    public function testIsNotValidMissingJSONRPC()
    {
        $request = new Request(json_decode(fixtureClass::$missingJSONRPC));
        $this->assertFalse($request->isValid());
    }

    public function testIsNotValidInvalidJSONRPC()
    {
        $request = new Request(json_decode(fixtureClass::$invalidJSONRPC));
        $this->assertFalse($request->isValid());
    }

    public function testIsNotValidMissingMethod()
    {
        $request = new Request(json_decode(fixtureClass::$missingMethod));
        $this->assertFalse($request->isValid());
    }

    public function testIsNotValidIllegalMethod()
    {
        $request = new Request(json_decode(fixtureClass::$illegalMethod));
        $this->assertFalse($request->isValid());
    }

    public function testIsNotValidInvalidParams()
    {
        $request = new Request(json_decode(fixtureClass::$invalidParams));
        $this->assertFalse($request->isValid());
    }

    public function testIsNotNotify()
    {
        $request = new Request(json_decode(fixtureClass::$fooJSON));
        $this->assertFalse($request->isNotify());
    }

    public function testIsNotBatch()
    {
        $request = new Request(json_decode(fixtureClass::$fooJSON));
        $this->assertFalse($request->isBatch());
    }

    public function testIsNotify()
    {
        $request = new NotifyRequest(json_decode(fixtureClass::$notifyJSON));
        $this->assertTrue($request->isNotify());
    }

    public function testIsBatch()
    {
        $request = new BatchRequest(json_decode(fixtureClass::$batchJSON));
        $this->assertTrue($request->isBatch());
    }
}