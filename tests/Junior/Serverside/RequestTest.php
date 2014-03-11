<?php

use Junior\Serverside\Request,
    Junior\Serverside\NotifyRequest,
    Junior\Serverside\BatchRequest,
    Junior\Serverside\Exception as ServerException;

class RequestTest extends PHPUnit_Framework_TestCase {

    public function testNewRequest()
    {
        $request = new Request(json_decode(FixtureClass::$fooJSON));
        $this->assertInstanceOf('Junior\Serverside\Request', $request);
    }

    public function testGetId()
    {
        $request = new Request(json_decode(FixtureClass::$fooJSON));
        $this->assertEquals(1, $request->getId());
    }

    public function testGetMethod()
    {
        $request = new Request(json_decode(FixtureClass::$fooJSON));
        $this->assertEquals('foo', $request->getMethod());
    }

    public function testGetParams()
    {
        $request = new Request(json_decode(FixtureClass::$barJSON));
        $this->assertEquals([ 1, 2, 3 ], $request->getParams());
    }

    public function testCheckValid()
    {
        $request = new Request(json_decode(FixtureClass::$fooJSON));
        try {
            $request->checkValid();
        } catch (ServerException $exception) {
            $this->fail();
        }
    }

    public function testsCheckValidStringId()
    {
        $request = new Request(json_decode(FixtureClass::$stringIdJSON));
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
        $request = new Request(json_decode(FixtureClass::$invalidJSON));
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
            [ FixtureClass::$missingJSONRPC ],
            [ FixtureClass::$invalidJSONRPC ],
            [ FixtureClass::$invalidId ],
            [ FixtureClass::$missingMethod ],
            [ FixtureClass::$illegalMethod ],
            [ FixtureClass::$invalidParams ]
        ];
    }

    public function testIsNotNotify()
    {
        $request = new Request(json_decode(FixtureClass::$fooJSON));
        $this->assertFalse($request->isNotify());
    }

    public function testIsNotBatch()
    {
        $request = new Request(json_decode(FixtureClass::$fooJSON));
        $this->assertFalse($request->isBatch());
    }

    public function testIsNotify()
    {
        $request = new NotifyRequest(json_decode(FixtureClass::$notifyJSON));
        $this->assertTrue($request->isNotify());
    }

    public function testIsBatch()
    {
        $request = new BatchRequest(json_decode(FixtureClass::$batchJSON));
        $this->assertTrue($request->isBatch());
    }

    public function testGetIds()
    {
        $request = new BatchRequest(json_decode(FixtureClass::$batchJSON));
        $this->assertEquals([ 1, 2 ], $request->getIds());
    }

    public function testGetIdsWithNotify()
    {
        $request = new BatchRequest(json_decode(FixtureClass::$batchJSONWithNotify));
        $this->assertEquals([ 1, null, 2, null ], $request->getIds());
    }
}