<?php

use Junior\Serverside\Response,
    Junior\Serverside\BatchResponse,
    Junior\Serverside\ErrorResponse,
    Junior\Serverside\Exception as ServerException;

class ResponseTest extends PHPUnit_Framework_TestCase {

    public function testToJSON()
    {
        $response = new Response(1, 'foo');
        $actualResponse = json_decode($response->toJSON());
        $expectedResponse = json_decode(fixtureClass::$fooResponse);

        $this->assertEquals($expectedResponse->{'jsonrpc'}, $actualResponse->{'jsonrpc'});
        $this->assertEquals($expectedResponse->result, $actualResponse->result);
        $this->assertEquals($expectedResponse->id, $actualResponse->id);
    }

    public function testToJSONBatch()
    {
        $response = new BatchResponse([ 1, null, 2, null ], [ 'bar', null, 'foo', null ]);
        $actualResponse = json_decode($response->toJSON());
        $expectedResponse = json_decode(fixtureClass::$batchWithNotifyResponse);

        $this->assertEquals($expectedResponse[0]->{'jsonrpc'}, $actualResponse[0]->{'jsonrpc'});
        $this->assertEquals($expectedResponse[0]->result, $actualResponse[0]->result);
        $this->assertEquals($expectedResponse[0]->id, $actualResponse[0]->id);

        $this->assertEquals($expectedResponse[1]->{'jsonrpc'}, $actualResponse[1]->{'jsonrpc'});
        $this->assertEquals($expectedResponse[1]->result, $actualResponse[1]->result);
        $this->assertEquals($expectedResponse[1]->id, $actualResponse[1]->id);
    }

    public function testToJSONError()
    {
        $response = new ErrorResponse(
            1,
            ServerException::MESSAGE_ERROR_PARSING_JSON,
            ServerException::CODE_ERROR_PARSING_JSON
        );
        $actualResponse = json_decode($response->toJSON());
        $expectedResponse = json_decode(fixtureClass::$errorParsingJSONResponse);

        $this->assertEquals($expectedResponse->{'jsonrpc'}, $actualResponse->{'jsonrpc'});
        $this->assertEquals($expectedResponse->error->message, $actualResponse->error->message);
        $this->assertEquals($expectedResponse->error->code, $actualResponse->error->code);
        $this->assertEquals($expectedResponse->id, $actualResponse->id);
    }

}