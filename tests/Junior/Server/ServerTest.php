<?php

use Spray\Spray;

class TestClass
{

    public function testAdd($arg1, $arg2)
    {
        return $arg1 + $arg2;
    }

    public function testSub($args)
    {
        return $args['first'] - $args['second'];
    }

    public function testNotify()
    {
        return 'foo';
    }

    private function testPrivate()
    {
        return 'foo';
    }

}

class ServerTest extends PHPUnit_Framework_TestCase
{
    protected $inputUrl;

    public function setUp()
    {
        $this->inputUrl = 'junior://input';
        Spray::stub($this->inputUrl, array('body' => 'foo'));
    }

    public function tearDown()
    {
        Spray::reset();
    }

    public function getMockServer($mockRequest, $handleRequest = true, $makeRequest = true)
    {
        $server = $this->getMock('Junior\Server',
            array('handleRequest', 'makeRequest'),
            array(new TestClass()));

        $server->input = $this->inputUrl;

        if ($handleRequest) {
            $server->returnValue = 'foo';
            $server->expects($this->once())
                ->method('handleRequest')
                ->will($this->returnValue($server->returnValue));
        }

        if ($makeRequest) {
            $server->expects($this->once())
                ->method('makeRequest')
                ->will($this->returnValue($mockRequest));
        }

        return $server;
    }

    public function testObjectPassed()
    {
        $this->setExpectedException('Junior\Serverside\Exception');
        new Junior\Server('foo');
    }

    public function testMethodExists()
    {
        $server = new Junior\Server(new TestClass());
        $this->assertTrue($server->methodExists('testAdd'));
        $this->assertFalse($server->methodExists('notReal'));
    }

    public function testInvokeMethodUnnamedParams()
    {
        $server = new Junior\Server(new TestClass());
        $params = array(2, 3);
        $this->assertEquals(5, $server->invokeMethod('testAdd', $params));
    }

    public function testInvokeMethodNamedParams()
    {
        $server           = new Junior\Server(new TestClass());
        $paramObj         = $this->getMock('Object', null);
        $paramObj->first  = 10;
        $paramObj->second = 5;
        $this->assertEquals(5, $server->invokeMethod('testSub', $paramObj));
    }

    public function testInvokeMethodNoParams()
    {
        $server = new Junior\Server(new TestClass());
        $this->assertEquals('foo', $server->invokeMethod('testNotify', null));
    }

    public function testInvokeMethodPrivate()
    {
        $server = new Junior\Server(new TestClass());
        $this->setExpectedException('Junior\Serverside\Exception');
        $server->invokeMethod('testPrivate', null);
    }

    public function testInvokeMethodTooFewParams()
    {
        $server = new Junior\Server(new TestClass());
        $this->setExpectedException('Junior\Serverside\Exception');
        $server->invokeMethod('testAdd', array(100));
    }

    public function testProcessGood()
    {
        $mockRequest = $this->getMock('Object', null);
        $server      = $this->getMockServer($mockRequest);

        $this->expectOutputString($server->returnValue);

        $server->process();
    }

    public function testProcessError()
    {
        $errorResponse = 'Error Response';
        $mockRequest   = $this->getMock('Object', array('toResponseJSON'));
        $mockRequest->expects($this->once())
            ->method('toResponseJSON')
            ->will($this->returnValue($errorResponse));
        $mockRequest->errorCode    = 10;
        $mockRequest->errorMessage = 'ERROR!';

        $server = $this->getMockServer($mockRequest, false);

        $this->expectOutputString($errorResponse);

        $server->process();
    }

    public function testProcessException()
    {
        $mockRequest   = $this->getMock('Object', null);
        $server        = $this->getMockServer($mockRequest, false, false);
        $server->input = 'not.there';

        $this->setExpectedException('Junior\Serverside\Exception');

        $server->process();
    }

    public function testHandleRequest()
    {
        $returnValue = 'foo';
        $server      = $this->getMock('Junior\Server',
            array('methodExists', 'invokeMethod'),
            array(),
            '',
            false);
        $server->expects($this->once())
            ->method('methodExists')
            ->will($this->returnValue(true));
        $server->expects($this->once())
            ->method('invokeMethod')
            ->will($this->returnValue($returnValue));

        $request = $this->getMock('Object', array('isBatch',
                                                  'checkValid',
                                                  'isNotify',
                                                  'toResponseJSON'));
        $request->expects($this->once())
            ->method('isBatch')
            ->will($this->returnValue(false));
        $request->expects($this->once())
            ->method('checkValid')
            ->will($this->returnValue(true));
        $request->expects($this->once())
            ->method('isNotify')
            ->will($this->returnValue(false));
        $request->expects($this->once())
            ->method('toResponseJSON')
            ->will($this->returnValue(null));

        $server->handleRequest($request);
        $this->assertEquals($returnValue, $request->result);
    }

    public function testHandleRequestInvalid()
    {
        $server = $this->getMock('Junior\Server',
            array('methodExists', 'invokeMethod'),
            array(),
            '',
            false);
        $server->expects($this->never())->method('methodExists');
        $server->expects($this->never())->method('invokeMethod');

        $request = $this->getMock('Object', array('isBatch',
                                                  'checkValid',
                                                  'isNotify',
                                                  'toResponseJSON'));
        $request->expects($this->once())
            ->method('isBatch')
            ->will($this->returnValue(false));
        $request->expects($this->once())
            ->method('checkValid')
            ->will($this->returnValue(false));
        $request->expects($this->never())->method('isNotify');
        $request->expects($this->once())
            ->method('toResponseJSON')
            ->will($this->returnValue(null));

        $server->handleRequest($request);
    }

    public function testHandleRequestNotify()
    {
        $returnValue = 'foo';
        $server      = $this->getMock('Junior\Server',
            array('methodExists', 'invokeMethod'),
            array(),
            '',
            false);
        $server->expects($this->once())
            ->method('methodExists')
            ->will($this->returnValue(true));
        $server->expects($this->once())
            ->method('invokeMethod')
            ->will($this->returnValue($returnValue));

        $request = $this->getMock('Object', array('isBatch',
                                                  'checkValid',
                                                  'isNotify',
                                                  'toResponseJSON'));
        $request->expects($this->once())
            ->method('isBatch')
            ->will($this->returnValue(false));
        $request->expects($this->once())
            ->method('checkValid')
            ->will($this->returnValue(true));
        $request->expects($this->once())
            ->method('isNotify')
            ->will($this->returnValue(true));
        $request->expects($this->never())->method('toResponseJSON');

        $this->assertNull($server->handleRequest($request));
    }

    public function testHandleRequestMethodNotFound()
    {
        $server = $this->getMock('Junior\Server',
            array('methodExists', 'invokeMethod'),
            array(),
            '',
            false);
        $server->expects($this->once())
            ->method('methodExists')
            ->will($this->returnValue(false));
        $server->expects($this->never())->method('invokeMethod');

        $request = $this->getMock('Object', array('isBatch',
                                                  'checkValid',
                                                  'isNotify',
                                                  'toResponseJSON'));
        $request->expects($this->once())
            ->method('isBatch')
            ->will($this->returnValue(false));
        $request->expects($this->once())
            ->method('checkValid')
            ->will($this->returnValue(true));
        $request->expects($this->never())->method('isNotify');
        $request->expects($this->once())
            ->method('toResponseJSON')
            ->will($this->returnValue(null));

        $server->handleRequest($request);
        $this->assertEquals(constant('Junior\ERROR_METHOD_NOT_FOUND'), $request->errorCode);
        $this->assertEquals("Method not found.", $request->errorMessage);
    }

    public function testHandleRequestException()
    {
        $errorMessage = 'Error!';
        $server       = $this->getMock('Junior\Server',
            array('methodExists', 'invokeMethod'),
            array(),
            '',
            false);
        $server->expects($this->once())
            ->method('methodExists')
            ->will($this->returnValue(true));
        $server->expects($this->once())
            ->method('invokeMethod')
            ->will($this->throwException(new \Exception($errorMessage)));

        $request = $this->getMock('Object', array('isBatch',
                                                  'checkValid',
                                                  'isNotify',
                                                  'toResponseJSON'));
        $request->expects($this->once())
            ->method('isBatch')
            ->will($this->returnValue(false));
        $request->expects($this->once())
            ->method('checkValid')
            ->will($this->returnValue(true));
        $request->expects($this->never())->method('isNotify');
        $request->expects($this->once())
            ->method('toResponseJSON')
            ->will($this->returnValue(null));

        $server->handleRequest($request);
        $this->assertEquals(constant('Junior\ERROR_EXCEPTION'), $request->errorCode);
        $this->assertEquals($errorMessage, $request->errorMessage);
    }

    public function testHandleRequestBatch()
    {
        $returnValue[] = 'foo';
        $returnValue[] = 'bar';
        $server        = $this->getMock('Junior\Server',
            array('methodExists', 'invokeMethod'),
            array(),
            '',
            false);
        $server->expects($this->exactly(3))
            ->method('methodExists')
            ->will($this->returnValue(true));
        $server->expects($this->exactly(3))
            ->method('invokeMethod')
            ->will($this->onConsecutiveCalls($returnValue[0], $returnValue[1], null));

        $request = $this->getMock('Object', array('isBatch',
                                                  'checkValid',
                                                  'isNotify',
                                                  'toResponseJSON'));
        $request->expects($this->once())
            ->method('isBatch')
            ->will($this->returnValue(true));
        $request->expects($this->never())->method('checkValid');
        $request->expects($this->never())->method('isNotify');
        $request->expects($this->never())->method('toResponseJSON');

        $child = $this->getMock('Object', array('isBatch',
                                                'checkValid',
                                                'isNotify',
                                                'toResponseJSON'));
        $child->expects($this->exactly(3))
            ->method('isBatch')
            ->will($this->returnValue(false));
        $child->expects($this->exactly(3))
            ->method('checkValid')
            ->will($this->returnValue(true));
        $child->expects($this->exactly(3))
            ->method('isNotify')
            ->will($this->onConsecutiveCalls(false, false, true));
        $child->expects($this->exactly(2))
            ->method('toResponseJSON')
            ->will($this->onConsecutiveCalls($returnValue[0], $returnValue[1]));

        $request->requests = array($child, $child, $child);

        $this->assertEquals("[{$returnValue[0]},{$returnValue[1]}]", $server->handleRequest($request));
    }

}