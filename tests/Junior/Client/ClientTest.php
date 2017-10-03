<?php

use Spray\Spray;


class ClientTest extends PHPUnit_Framework_TestCase
{
    protected $fakeUri;

    public function setUp()
    {
        $this->fakeUri = 'foo://bar';
    }

    public function tearDown()
    {
        Spray::reset();
    }

    private function stubResponse($body)
    {
        Spray::stub($this->fakeUri, array('raw' => $body));
    }

    public function getEmptyRequest()
    {
        $request = $this->getMock('Request', array('getJSON', 'getArray'));
        $request->expects($this->any())
            ->method('getJSON')
            ->will($this->returnValue(null));
        $request->expects($this->any())
            ->method('getArray')
            ->will($this->returnValue(null));

        return $request;
    }

    public function getEmptyResponse()
    {
        return $this->getMock('Object');
    }

    /**
     * @param array $methods
     * @param null $returnsOnce
     * @return PHPUnit_Framework_MockObject_MockObject|\Junior\Client
     */
    public function getMockClient($methods = array(), $returnsOnce = null)
    {
        $client = $this->getMock('Junior\Client',
            $methods,
            array(),
            '',
            false);

        if ($returnsOnce !== null) {
            $client->expects($this->once())
                ->method('send')
                ->will($this->returnValue($returnsOnce));
        }

        return $client;
    }

    public function testSetBasicAuth()
    {
        $client = new Junior\Client($this->fakeUri);
        $client->setBasicAuth('foo', 'bar');
        $expected = "Authorization: Basic Zm9vOmJhcg==\r\n";
        $this->assertEquals($expected, $client->authHeader);
    }

    public function testclearAuth()
    {
        $client = new Junior\Client($this->fakeUri);
        $client->clearAuth();
        $client->setBasicAuth('foo', 'bar');
        $client->clearAuth();
        $this->assertNull($client->authHeader);
    }

    public function testSendRequestGoodId()
    {
        $request     = $this->getEmptyRequest();
        $request->id = 10;

        $response         = $this->getEmptyResponse();
        $response->id     = 10;
        $response->result = 'foo';

        $client = $this->getMockClient(array('send'), $response);

        $this->assertEquals('foo', $client->sendRequest($request));
    }

    public function testSendRequestBadId()
    {
        $request     = $this->getEmptyRequest();
        $request->id = 10;

        $response     = $this->getEmptyResponse();
        $response->id = 11;

        $client = $this->getMockClient(array('send'), $response);

        $this->setExpectedException('Junior\Clientside\Exception');

        $client->sendRequest($request);
    }

    public function testReceiveErrorCodeZero()
    {
        $request     = $this->getEmptyRequest();
        $request->id = 11;

        $response            = $this->getEmptyResponse();
        $response->id        = 11;
        $response->errorCode = 0;

        $client = $this->getMockClient(array('send'), $response);

        $this->setExpectedException('Junior\Clientside\Exception');

        $client->sendRequest($request);
    }

    public function testSendNotifyGood()
    {
        $request     = $this->getEmptyRequest();
        $request->id = null;

        $client = $this->getMockClient(array('send'), true);

        $this->assertTrue($client->sendNotify($request));
    }

    public function testSendNotifyBad()
    {
        $request     = $this->getEmptyRequest();
        $request->id = 10;

        $client = $this->getMockClient(array('send'));
        $client->expects($this->never())->method('send');

        $this->setExpectedException('Junior\Clientside\Exception');

        $client->sendNotify($request);
    }

    public function testSendBatchGood()
    {
        $requests = array();
        for ($i = 10; $i <= 15; $i++) {
            $request     = $this->getEmptyRequest();
            $request->id = $i;
            $requests[]  = $request;
        }

        $responses = array();
        for ($i = 10; $i <= 15; $i++) {
            $response      = $this->getEmptyResponse();
            $response->id  = $i;
            $responses[$i] = $response;
        }

        $client = $this->getMockClient(array('send'), $responses);

        $client->sendBatch($requests);
    }

    public function testSendBatchBadTooFew()
    {
        $requests = array();
        for ($i = 10; $i <= 15; $i++) {
            $request     = $this->getEmptyRequest();
            $request->id = $i;
            $requests[]  = $request;
        }

        $responses = array();
        for ($i = 10; $i <= 13; $i++) {
            $response      = $this->getEmptyResponse();
            $response->id  = $i;
            $responses[$i] = $response;
        }

        $client = $this->getMockClient(array('send'), $responses);

        $this->setExpectedException('Junior\Clientside\Exception');

        $client->sendBatch($requests);
    }

    public function testSendBatchBadTooMany()
    {
        $requests = array();
        for ($i = 10; $i <= 15; $i++) {
            $request     = $this->getEmptyRequest();
            $request->id = $i;
            $requests[]  = $request;
        }

        $responses = array();
        for ($i = 10; $i <= 17; $i++) {
            $response      = $this->getEmptyResponse();
            $response->id  = $i;
            $responses[$i] = $response;
        }

        $client = $this->getMockClient(array('send'), $responses);

        $this->setExpectedException('Junior\Clientside\Exception');

        $client->sendBatch($requests);
    }


    public function testSendBatchNotify()
    {
        $requests = array();
        for ($i = 10; $i <= 15; $i++) {
            $request     = $this->getEmptyRequest();
            $request->id = null;
            $requests[]  = $request;
        }

        $client = $this->getMockClient(array('send'), true);

        $this->assertTrue($client->sendBatch($requests));
    }

    public function testSendGood()
    {
        $this->stubResponse('{"good":"json"}');
        $client = $this->getMock('Junior\Client',
            array('handleResponse'),
            array($this->fakeUri));
        $client->expects($this->once())
            ->method('handleResponse')
            ->will($this->returnArgument(0));

        $client->send('foo');
    }

    public function testSendGoodNotify()
    {
        $this->stubResponse('foo');
        $client = $this->getMock('Junior\Client',
            array('handleResponse'),
            array($this->fakeUri));
        $client->expects($this->never())->method('handleResponse');

        $this->assertTrue($client->send('foo', true));
    }

    public function testSendBadConnect()
    {
        $client = $this->getMock('Junior\Client',
            array('handleResponse'),
            array('not.there'));
        $client->expects($this->never())->method('handleResponse');

        $this->setExpectedException('Junior\Clientside\Exception');

        $client->send('foo');
    }

    public function testSendBadResponseJSON()
    {
        $this->stubResponse('{bad:json,}');
        $client = $this->getMock('Junior\Client',
            array('handleResponse'),
            array($this->fakeUri));
        $client->expects($this->never())->method('handleResponse');

        $this->setExpectedException('Junior\Clientside\Exception');

        $client->send('foo');
    }

    public function testHandleResponseGood()
    {
        $testResponse         = $this->getEmptyResponse();
        $testResponse->result = 'foo';
        $testResponse->id     = 1;

        $client          = $this->getMockClient(null);
        $client_response = $client->handleResponse($testResponse);

        $this->assertEquals($testResponse->result, $client_response->result);
        $this->assertEquals($testResponse->id, $client_response->id);
    }

    public function testHandleResponseError()
    {
        $testResponse        = $this->getEmptyResponse();
        $error               = new stdClass();
        $error->code         = 1;
        $error->message      = 'foo';
        $testResponse->error = $error;
        $testResponse->id    = 1;

        $client         = $this->getMockClient(null);
        $clientResponse = $client->handleResponse($testResponse);

        $this->assertEquals($testResponse->error->code, $clientResponse->errorCode);
        $this->assertEquals($testResponse->error->message, $clientResponse->errorMessage);
        $this->assertEquals($testResponse->id, $clientResponse->id);
    }

    public function testHandleResponseBatch()
    {
        $goodResponse         = $this->getEmptyResponse();
        $goodResponse->result = 'foo';
        $goodResponse->id     = 1;

        $errorResponse        = $this->getEmptyResponse();
        $error                = new stdClass();
        $error->code          = 1;
        $error->message       = 'foo';
        $errorResponse->error = $error;
        $errorResponse->id    = 2;

        $responses = array($goodResponse, $errorResponse);

        $client         = $this->getMockClient(null);
        $clientResponse = $client->handleResponse($responses);

        $this->assertEquals($goodResponse->result, $clientResponse[1]->result);
        $this->assertEquals($goodResponse->id, $clientResponse[1]->id);

        $this->assertEquals($errorResponse->error->code, $clientResponse[2]->errorCode);
        $this->assertEquals($errorResponse->error->message, $clientResponse[2]->errorMessage);
        $this->assertEquals($errorResponse->id, $clientResponse[2]->id);
    }

}
