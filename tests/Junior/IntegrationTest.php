<?php

use Spray\Spray;

class TestJSONRPCClass {

    public function subtract($a, $b = null)
    {
        if (is_array($a)) {
            return $a['minuend'] - $a['subtrahend'];
        } else {
            return $a - $b;
        }
    }

    public function update($a, $b, $c, $d, $e)
    {
        // do anything with params...
        // ...but return nothing
    }

    public function foobar()
    {
        // do anything with params...
        // ...but return nothing
    }

    public function sum($a, $b, $c)
    {
        return $a + $b + $c;
    }

    public function notify_hello($world)
    {
        // do anything with params...
        // ...but return nothing
    }

    public function get_data()
    {
        return array('hello', 5);
    }

    public function notify_sum($a, $b, $c)
    {
        // do anything with params...
        // ...but return nothing
    }

}

class IntegrationTest extends PHPUnit_Framework_TestCase {

    public function setUp()
    {
        $this->client = $this->getMock('Junior\Client',
                                       array('handleResponse','decodeJSON'),
                                       array('foo://bar'));
        $this->client->expects($this->any())
                     ->method('handleResponse')
                     ->will($this->returnArgument(0));
        $this->client->expects($this->any())
                     ->method('decodeJSON')
                     ->will($this->returnCallback(array('IntegrationTest','makeMockResponse')));
        Spray::stub('foo://bar', array('echo_back' => 'content'));
        $this->server = new Junior\Server(new TestJSONRPCClass());
    }

    public function tearDown()
    {
        Spray::reset();
    }

    public static function makeMockResponse($json)
    {
        $response = json_decode($json);
        $response->result = $json;
        return $response;
    }

    private function setRequest($json)
    {
        Spray::stub('php://input', array('raw' => $json));
    }

   /**
    * @dataProvider sendRequestProvider
    */
    public function testSendRequest($request, $expectedRegex, $sentJSON, $returnJSON)
    {
        $this->assertRegExp($expectedRegex, $request->getJSON());
        $this->assertRegExp($expectedRegex, $this->client->sendRequest($request));
        $this->setRequest($sentJSON);
        $this->server->process();
        $this->expectOutputString($returnJSON);
    }

    public function sendRequestProvider()
    {
        return array(
            array(new Junior\Clientside\Request('subtract', array(42,23)),
                '/\{"jsonrpc":"2.0","method":"subtract","params":\[42,23\],"id":"[^"]+"\}/',
                '{"jsonrpc":"2.0","method":"subtract","params":[42,23],"id":"1"}',
                '{"jsonrpc":"2.0","result":19,"id":"1"}'),
            array(new Junior\Clientside\Request('subtract', array(23,42)),
                '/\{"jsonrpc":"2.0","method":"subtract","params":\[23,42\],"id":"[^"]+"\}/',
                '{"jsonrpc":"2.0","method":"subtract","params":[23,42],"id":"1"}',
                '{"jsonrpc":"2.0","result":-19,"id":"1"}'),
            array(new Junior\Clientside\Request('subtract', array("subtrahend"=>23,"minuend"=>42)),
                '/\{"jsonrpc":"2.0","method":"subtract","params":\{"subtrahend":23,"minuend":42\},"id":"[^"]+"\}/',
                '{"jsonrpc":"2.0","method":"subtract","params":{"subtrahend":23,"minuend":42},"id":"1"}',
                '{"jsonrpc":"2.0","result":19,"id":"1"}'),
            array(new Junior\Clientside\Request('subtract', array("minuend"=>42,"subtrahend"=>23)),
                '/\{"jsonrpc":"2.0","method":"subtract","params":\{"minuend":42,"subtrahend":23\},"id":"[^"]+"\}/',
                '{"jsonrpc":"2.0","method":"subtract","params":{"minuend":42,"subtrahend":23},"id":"1"}',
                '{"jsonrpc":"2.0","result":19,"id":"1"}')
        );
    }

    /**
     * @dataProvider sendNotificationProvider
     */
    public function testSendNotification($request, $expectedRegex, $sentJSON, $returnJSON)
    {
        $this->assertRegExp($expectedRegex, $request->getJSON());
        $this->assertRegExp($expectedRegex, $this->client->sendRequest($request));
        $this->setRequest($sentJSON);
        $this->server->process();
        $this->expectOutputString($returnJSON);
    }

    public function sendNotificationProvider()
    {
        return array(
            array(new Junior\Clientside\Request('update', array(1,2,3,4,5), true),
                '/\{"jsonrpc":"2.0","method":"update","params":\[1,2,3,4,5\]\}/',
                '{"jsonrpc":"2.0","method":"update","params":[1,2,3,4,5]}',
                null),
            array(new Junior\Clientside\Request('foobar', null, true),
                '/\{"jsonrpc":"2.0","method":"foobar"\}/',
                '{"jsonrpc":"2.0","method":"foobar"}',
                null)
        );
    }

    public function testNonExistentMethod()
    {
        $request = new Junior\Clientside\Request('doesNotExist', null);
        $expectedRegex = '/{"jsonrpc":"2.0","method":"doesNotExist","id":"[^"]+"}/';
        $this->assertRegExp($expectedRegex, $request->getJSON());
        $this->assertRegExp($expectedRegex, $this->client->sendRequest($request));

        $sentJSON = '{"jsonrpc":"2.0","method":"doesNotExist","id":"1"}';
        $returnJSON = '{"jsonrpc":"2.0","error":{"code":-32601,"message":"Method not found."},"id":"1"}';

        $this->setRequest($sentJSON);
        $this->server->process();
        $this->expectOutputString($returnJSON);
    }

    /**
     * @dataProvider badInputProvider
     */
    public function testBadInput($sentJSON, $returnJSON)
    {
        $this->setRequest($sentJSON);
        $this->server->process();
        $this->expectOutputString($returnJSON);
    }

    public function badInputProvider()
    {
        $invalidRequest = '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request."},"id":null}';
        $parseError = '{"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error."},"id":null}';

        return array(
            array('{"jsonrpc":"2.0","method":"foobar,"params":"bar","baz]',
                  $parseError),
            array('{"jsonrpc":"2.0","method":1,"params":"bar"}',
                  $invalidRequest),
            array('[{"jsonrpc":"2.0","method":"sum","params":[1,2,4],"id":"1"},{"jsonrpc":"2.0","method"]',
                  $parseError),
            array('[{"jsonrpc":"2.0","method":"sum","params":[1,2,4],"id":"1"},{"jsonrpc":"2.0","method"]',
                  $parseError),
            array('[]',
                  $invalidRequest),
            array('[1]',
                  "[{$invalidRequest}]"),
            array('[1,2,3]',
                  "[{$invalidRequest},{$invalidRequest},{$invalidRequest}]")
       );
    }

    public function testSendBatch()
    {
        $requests = array(
            new Junior\Clientside\Request('sum', array(1,2,4)),
            new Junior\Clientside\Request('notify_hello', array(7), true),
            new Junior\Clientside\Request('subtract', array(42,23)),
            new Junior\Clientside\Request('foo_get', array('name'=>'myself')),
            new Junior\Clientside\Request('get_data', null),
        );

        $expectedJSON = '/\[\{"jsonrpc":"2.0","method":"sum","params":\[1,2,4\],"id":"[^"]+"\},';
        $expectedJSON .= '\{"jsonrpc":"2.0","method":"notify_hello","params":\[7\]\},';
        $expectedJSON .= '\{"jsonrpc":"2.0","method":"subtract","params":\[42,23\],"id":"[^"]+"\},';
        $expectedJSON .= '\{"jsonrpc":"2.0","method":"foo_get","params":\{"name":"myself"\},"id":"[^"]+"\},';
        $expectedJSON .= '\{"jsonrpc":"2.0","method":"get_data","id":"[^"]+"\}\]/';

        $requestJSON = "[".implode(',',array_map(function($x){return $x->getJSON();},$requests))."]";
        $this->assertRegExp($expectedJSON, $requestJSON);

        $sentJSON = '[{"jsonrpc":"2.0","method":"sum","params":[1,2,4],"id":"1"},';
        $sentJSON .= '{"jsonrpc":"2.0","method":"notify_hello","params":[7]},';
        $sentJSON .= '{"jsonrpc":"2.0","method":"subtract","params":[42,23],"id":"2"},';
        $sentJSON .= '{"foo":"boo"},';
        $sentJSON .= '{"jsonrpc":"2.0","method":"foo_get","params":{"name":"myself"},"id":"5"},';
        $sentJSON .= '{"jsonrpc":"2.0","method":"get_data","id":"9"}]';

        $returnJSON = '[{"jsonrpc":"2.0","result":7,"id":"1"},';
        $returnJSON .= '{"jsonrpc":"2.0","result":19,"id":"2"},';
        $returnJSON .= '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request."},"id":null},';
        $returnJSON .= '{"jsonrpc":"2.0","error":{"code":-32601,"message":"Method not found."},"id":"5"},';
        $returnJSON .= '{"jsonrpc":"2.0","result":["hello",5],"id":"9"}]';

        $this->setRequest($sentJSON);
        $this->server->process();
        $this->expectOutputString($returnJSON);
    }

    public function testSendNotificationBatch()
    {
        $requests = array(
            new Junior\Clientside\Request('notify_sum', array(1,2,4), true),
            new Junior\Clientside\Request('notify_hello', array(7), true),
        );

        $expectedJSON = '/\[\{"jsonrpc":"2.0","method":"notify_sum","params":\[1,2,4\]\},';
        $expectedJSON .= '\{"jsonrpc":"2.0","method":"notify_hello","params":\[7]\}\]/';

        $requestJSON = "[".implode(',',array_map(function($x){return $x->getJSON();},$requests))."]";
        $this->assertRegExp($expectedJSON, $requestJSON);

        $sentJSON = '[{"jsonrpc":"2.0","method":"notify_sum","params":[1,2,4]},';
        $sentJSON .= '{"jsonrpc":"2.0","method":"notify_hello","params":[7]}]';

        $this->setRequest($sentJSON);
        $this->server->process();
        $this->expectOutputString('');
    }

}

?>