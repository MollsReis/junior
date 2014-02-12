<?php

use Junior\Serverside\Request,
    Junior\Serverside\NotifyRequest,
    Junior\Serverside\BatchRequest,
    Junior\Serverside\Exception as ServerException;

class RequestTest extends PHPUnit_Framework_TestCase {

    public function testNewRequest()
    {
        $request = new Request(json_decode(fixtureClass::$fooJSON));
        $this->assertInstanceOf('Junior\Serverside\Request', $request);
    }

    public function testGetMethod()
    {
        $request = new Request(json_decode(fixtureClass::$fooJSON));
        $this->assertEquals('foo', $request->getMethod());
    }

    public function testGetParams()
    {
        $request = new Request(json_decode(fixtureClass::$barJSON));
        $this->assertEquals([ 1, 2, 3 ], $request->getParams());
    }

    public function testCheckValid()
    {
        $request = new Request(json_decode(fixtureClass::$fooJSON));
        try {
            $request->checkValid();
        } catch (ServerException $exception) {
            $this->fail();
        }
    }

    /**
     * @expectedException     Junior\Serverside\Exception
     * @expectedExceptionCode Junior\Serverside\Exception::CODE_INVALID_JSON
     */
    public function testCheckValidInvalidJSON()
    {
        $request = new Request(json_decode(fixtureClass::$invalidJSON));
        $request->checkValid();
        $this->fail();
    }

    /**
     * @dataProvider          invalidRequestProvider
     * @expectedException     Junior\Serverside\Exception
     * @expectedExceptionCode Junior\Serverside\Exception::CODE_INVALID_REQUEST
     */
    public function testCheckValidInvalidRequest($invalidRequestJSON)
    {
        $request = new Request(json_decode($invalidRequestJSON));
        $request->checkValid();
        $this->fail();
    }

    public function invalidRequestProvider()
    {
        return [
            [ fixtureClass::$missingJSONRPC ],
            [ fixtureClass::$invalidJSONRPC ],
            [ fixtureClass::$missingMethod ],
            [ fixtureClass::$illegalMethod ],
            [ fixtureClass::$invalidParams ]
        ];
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