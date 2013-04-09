<?php
use Spray\Spray;


class ResponseTest extends PHPUnit_Framework_TestCase {

    public function setUp()
    {
        $this->jsonrpc_result = 'foo';
        $this->id = 1;
        $this->error_code = 10;
        $this->error_message = 'ERROR!';
    }

    public function testNewResponse()
    {
        $response = new Junior\Clientside\Response($this->jsonrpc_result,
                                               $this->id,
                                               $this->error_code,
                                               $this->error_message);

        $this->assertEquals($this->jsonrpc_result, $response->result);
        $this->assertEquals($this->id, $response->id);
        $this->assertEquals($this->error_code, $response->error_code);
        $this->assertEquals($this->error_message, $response->error_message);
    }

    public function testToStringResult()
    {
        $response = new Junior\Clientside\Response($this->jsonrpc_result,
                                               $this->id);
        
        $this->assertEquals($this->jsonrpc_result, $response->__toString());
    }

    public function testToStringError()
    {
        $response = new Junior\Clientside\Response(null,
                                               null,
                                               $this->error_code,
                                               $this->error_message);

        $this->assertEquals("{$response->error_code}: {$response->error_message}",
                            $response->__toString());
    }

}

?>