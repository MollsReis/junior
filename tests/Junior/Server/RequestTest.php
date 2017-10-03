<?php


class ServerRequestTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return PHPUnit_Framework_MockObject_MockObject|\Junior\Serverside\Request
     */
    public function getEmptyRequest()
    {
        return $this->getMock('Junior\Serverside\Request',
            null,
            array(),
            '',
            false);
    }

    public function testNewRequest()
    {
        $jsonRpc = '2.0';
        $method  = 'testmethod';
        $params  = array('foo', 'bar');
        $id      = 10;
        $json    = "{\"jsonrpc\":\"{$jsonRpc}\", \"method\":\"{$method}\", \"params\":[\"{$params[0]}\", \"{$params[1]}\"], \"id\": {$id}}";
        $request = new Junior\Serverside\Request($json);
        $this->assertEquals($jsonRpc, $request->jsonRpc);
        $this->assertEquals($method, $request->method);
        $this->assertEquals($params, $request->params);
        $this->assertEquals($id, $request->id);
    }

    public function testNewRequestInvalidRequest()
    {
        $request = new Junior\Serverside\Request('');
        $this->assertEquals(Junior\Serverside\Request::JSON_RPC_VERSION, $request->jsonRpc);
        $this->assertEquals(Junior\Serverside\Request::ERROR_INVALID_REQUEST, $request->errorCode);
        $this->assertEquals("Invalid Request.", $request->errorMessage);

        $request = new Junior\Serverside\Request('[]');
        $this->assertEquals(Junior\Serverside\Request::JSON_RPC_VERSION, $request->jsonRpc);
        $this->assertEquals(Junior\Serverside\Request::ERROR_INVALID_REQUEST, $request->errorCode);
        $this->assertEquals("Invalid Request.", $request->errorMessage);
    }

    public function testNewRequestParseError()
    {
        $request = new Junior\Serverside\Request('[bad:json::]');
        $this->assertEquals(Junior\Serverside\Request::JSON_RPC_VERSION, $request->jsonRpc);
        $this->assertEquals(Junior\Serverside\Request::ERROR_PARSE_ERROR, $request->errorCode);
        $this->assertEquals("Parse error.", $request->errorMessage);
    }

    public function testNewRequestBatch()
    {
        $jsonRpc   = '2.0';
        $method    = 'testmethod';
        $params    = array('foo', 'bar');
        $id        = 10;
        $json      = "{\"jsonrpc\":\"{$jsonRpc}\", \"method\":\"{$method}\", \"params\":[\"{$params[0]}\", \"{$params[1]}\"], \"id\": {$id}}";
        $batchJson = "[$json,$json,$json]";
        $requests  = new Junior\Serverside\Request($batchJson);
        foreach ($requests->requests as $request) {
            $this->assertEquals($jsonRpc, $request->jsonRpc);
            $this->assertEquals($method, $request->method);
            $this->assertEquals($params, $request->params);
            $this->assertEquals($id, $request->id);
        }
    }

    public function testCheckValidGood()
    {
        $request          = $this->getEmptyRequest();
        $request->jsonRpc = '2.0';
        $request->method  = 'testMethod';
        $request->id      = 10;
        $this->assertTrue($request->checkValid());
    }

    public function testCheckValidErrorAlreadySet()
    {
        $request               = $this->getEmptyRequest();
        $request->jsonRpc      = '2.0';
        $request->method       = 'testMethod';
        $request->errorCode    = 10;
        $request->errorMessage = 'Error!';
        $request->id           = 10;
        $this->assertFalse($request->checkValid());
    }

    public function testCheckValidInvalidRequest()
    {
        $errorCode    = Junior\Serverside\Request::ERROR_INVALID_REQUEST;
        $errorMessage = 'Invalid Request.';

        $request          = $this->getEmptyRequest();
        $request->jsonRpc = null;
        $request->method  = 'testMethod';
        $request->id      = 10;
        $this->assertFalse($request->checkValid());
        $this->assertEquals($errorCode, $request->errorCode);
        $this->assertEquals($errorMessage, $request->errorMessage);

        $request          = $this->getEmptyRequest();
        $request->jsonRpc = '2.0';
        $request->method  = null;
        $request->id      = 10;
        $this->assertFalse($request->checkValid());
        $this->assertEquals($errorCode, $request->errorCode);
        $this->assertEquals($errorMessage, $request->errorMessage);

        $request          = $this->getEmptyRequest();
        $request->jsonRpc = '2.0';
        $request->method  = '!!!function';
        $request->id      = 10;
        $this->assertFalse($request->checkValid());
        $this->assertEquals($errorCode, $request->errorCode);
        $this->assertEquals($errorMessage, $request->errorMessage);
    }

    public function testCheckValidReservedPrefix()
    {
        $errorCode    = Junior\Serverside\Request::ERROR_RESERVED_PREFIX;
        $errorMessage = 'Illegal method name; Method cannot start with \'rpc.\'';

        $request          = $this->getEmptyRequest();
        $request->jsonRpc = '2.0';
        $request->method  = 'rpc.notvalid';
        $request->id      = 10;
        $this->assertFalse($request->checkValid());
        $this->assertEquals($errorCode, $request->errorCode);
        $this->assertEquals($errorMessage, $request->errorMessage);
    }

    public function testCheckValidMismatchedVersion()
    {
        $errorCode    = Junior\Serverside\Request::ERROR_MISMATCHED_VERSION;
        $errorMessage = 'Client/Server JSON-RPC version mismatch; Expected \'2.0\'';

        $request          = $this->getEmptyRequest();
        $request->jsonRpc = '1.0';
        $request->method  = 'method';
        $request->id      = 10;
        $this->assertFalse($request->checkValid());
        $this->assertEquals($errorCode, $request->errorCode);
        $this->assertEquals($errorMessage, $request->errorMessage);
    }

    public function testIsBatch()
    {
        $request        = $this->getEmptyRequest();
        $request->batch = true;

        $this->assertTrue($request->isBatch());
    }

    public function testIsNotify()
    {
        $request     = $this->getEmptyRequest();
        $request->id = null;

        $this->assertTrue($request->isNotify());

        $request->id = 10;

        $this->assertFalse($request->isNotify());
    }

    public function testIsNotifyWithZero()
    {
        $request     = $this->getEmptyRequest();
        $request->id = 0;

        $this->assertFalse($request->isNotify());
    }

    public function testResponseJSON()
    {
        $request               = $this->getEmptyRequest();
        $jsonVersion           = Junior\Serverside\Request::JSON_RPC_VERSION;
        $request->errorCode    = 10;
        $request->errorMessage = 'Error!';
        $request->id           = 1;

        $json = "{\"jsonrpc\":\"{$jsonVersion}\",\"error\":{\"code\":{$request->errorCode},\"message\":\"{$request->errorMessage}\"},\"id\":{$request->id}}";
        $this->assertEquals($json, $request->toResponseJSON());

        $request->result = 'foo';

        $json = "{\"jsonrpc\":\"{$jsonVersion}\",\"result\":\"{$request->result}\",\"id\":{$request->id}}";
        $this->assertEquals($json, $request->toResponseJSON());
    }

}