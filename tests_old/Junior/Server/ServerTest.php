<?php

use Spray\Spray;

class TestClass {

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

class ServerTest extends PHPUnit_Framework_TestCase {

    public function setUp()
    {
        $this->input_url = 'junior://input';
        Spray::stub($this->input_url, array('body' => 'foo'));
    }

    public function tearDown()
    {
        Spray::reset();
    }

    public function getMockServer($mock_request, $handle_request = true, $make_request = true)
    {
        $server = $this->getMock('Junior\Server',
                                 array('handleRequest', 'makeRequest'),
                                 array(new TestClass()));

        $server->input = $this->input_url;

        if ($handle_request) {
            $server->returnValue = 'foo';
            $server->expects($this->once())
                   ->method('handleRequest')
                   ->will($this->returnValue($server->returnValue));
        }

        if ($make_request) {
            $server->expects($this->once())
                   ->method('makeRequest')
                   ->will($this->returnValue($mock_request));
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
        $server = new Junior\Server(new TestClass());
        $param_obj = $this->getMock('Object', null);
        $param_obj->first = 10;
        $param_obj->second = 5;
        $this->assertEquals(5, $server->invokeMethod('testSub', $param_obj));
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
        $mock_request = $this->getMock('Object', null);
        $server = $this->getMockServer($mock_request);

        $this->expectOutputString($server->returnValue);

        $server->process();
    }

    public function testProcessError()
    {
        $error_response = 'Error Response';
        $mock_request = $this->getMock('Object', array('toResponseJSON'));
        $mock_request->expects($this->once())
                     ->method('toResponseJSON')
                     ->will($this->returnValue($error_response));
        $mock_request->error_code = 10;
        $mock_request->error_message = 'ERROR!';

        $server = $this->getMockServer($mock_request, false);

        $this->expectOutputString($error_response);

        $server->process();
    }

    public function testProcessException()
    {
        $mock_request = $this->getMock('Object', null);
        $server = $this->getMockServer($mock_request, false, false);
        $server->input = 'not.there';

        $this->setExpectedException('Junior\Serverside\Exception');

        $server->process();
    }

    public function testHandleRequest()
    {
        $return_value = 'foo';
        $server = $this->getMock('Junior\Server',
                         array('methodExists', 'invokeMethod'),
                         array(),
                         '',
                         false);
        $server->expects($this->once())
               ->method('methodExists')
               ->will($this->returnValue(true));
        $server->expects($this->once())
               ->method('invokeMethod')
               ->will($this->returnValue($return_value));

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
        $this->assertEquals($return_value, $request->result);
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
        $return_value = 'foo';
        $server = $this->getMock('Junior\Server',
                         array('methodExists', 'invokeMethod'),
                         array(),
                         '',
                         false);
        $server->expects($this->once())
               ->method('methodExists')
               ->will($this->returnValue(true));
        $server->expects($this->once())
               ->method('invokeMethod')
               ->will($this->returnValue($return_value));

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
        $this->assertEquals(constant('Junior\ERROR_METHOD_NOT_FOUND'), $request->error_code);
        $this->assertEquals("Method not found.", $request->error_message);
    }

    public function testHandleRequestException()
    {
        $error_message = 'Error!';
        $server = $this->getMock('Junior\Server',
                         array('methodExists', 'invokeMethod'),
                         array(),
                         '',
                         false);
        $server->expects($this->once())
               ->method('methodExists')
               ->will($this->returnValue(true));
        $server->expects($this->once())
               ->method('invokeMethod')
               ->will($this->throwException(new \Exception($error_message)));

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
        $this->assertEquals(constant('Junior\ERROR_EXCEPTION'), $request->error_code);
        $this->assertEquals($error_message, $request->error_message);
    }

    public function testHandleRequestBatch()
    {
        $return_value[] = 'foo';
        $return_value[] = 'bar';
        $server = $this->getMock('Junior\Server',
                         array('methodExists', 'invokeMethod'),
                         array(),
                         '',
                         false);
        $server->expects($this->exactly(3))
               ->method('methodExists')
               ->will($this->returnValue(true));
        $server->expects($this->exactly(3))
               ->method('invokeMethod')
               ->will($this->onConsecutiveCalls($return_value[0], $return_value[1], null));

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
              ->will($this->onConsecutiveCalls($return_value[0], $return_value[1]));
              
        $request->requests = array($child, $child, $child);

        $this->assertEquals("[{$return_value[0]},{$return_value[1]}]", $server->handleRequest($request));
    }

}