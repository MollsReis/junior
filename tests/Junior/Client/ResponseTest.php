<?php


class ResponseTest extends PHPUnit_Framework_TestCase
{
    protected $jsonrpcResult;
    protected $id;
    protected $errorCode;
    protected $errorMessage;


    public function setUp()
    {
        $this->jsonrpcResult = 'foo';
        $this->id            = 1;
        $this->errorCode     = 10;
        $this->errorMessage  = 'ERROR!';
    }

    public function testNewResponse()
    {
        $response = new Junior\Clientside\Response($this->jsonrpcResult,
            $this->id,
            $this->errorCode,
            $this->errorMessage);

        $this->assertEquals($this->jsonrpcResult, $response->result);
        $this->assertEquals($this->id, $response->id);
        $this->assertEquals($this->errorCode, $response->errorCode);
        $this->assertEquals($this->errorMessage, $response->errorMessage);
    }

    public function testNewComplexResponse()
    {
        $complexResult = array(1, 2, 3);
        $response      = new Junior\Clientside\Response($complexResult,
            $this->id,
            $this->errorCode,
            $this->errorMessage);

        $this->assertEquals($complexResult, $response->result);
        $this->assertEquals($this->id, $response->id);
        $this->assertEquals($this->errorCode, $response->errorCode);
        $this->assertEquals($this->errorMessage, $response->errorMessage);
    }

    public function testToStringResult()
    {
        $response = new Junior\Clientside\Response($this->jsonrpcResult,
            $this->id);

        $this->assertEquals($this->jsonrpcResult, $response->__toString());
    }

    public function testToStringError()
    {
        $response = new Junior\Clientside\Response(null,
            null,
            $this->errorCode,
            $this->errorMessage);

        $this->assertEquals("{$response->errorCode}: {$response->errorMessage}",
            $response->__toString());
    }

    public function testRecursiveUTF8Decode()
    {
        $complexResult = array('迎', array('迎', '迎'), '迎');
        $decodedResult = array('?', array('?', '?'), '?');
        $response      = new Junior\Clientside\Response($complexResult, $this->id, $this->errorCode, $this->errorMessage);
        $this->assertEquals($decodedResult, $response->result);
    }

}
