<?php
class ResponseTest extends PHPUnit_Framework_TestCase {

    public function setUp()
    {
        $this->result = 'foo';
        $this->id = 1;
        $this->error_code = 10;
        $this->error_message = 'ERROR!';
    }

    public function testNewResponse()
    {
        $response = new Junior\Client\Response($this->result,
                                               $this->id,
                                               $this->error_code,
                                               $this->error_message);

        $this->assertEquals($this->result, $response->result);
        $this->assertEquals($this->id, $response->id);
        $this->assertEquals($this->error_code, $response->error_code);
        $this->assertEquals($this->error_message, $response->error_message);
    }

    public function testToStringResult()
    {
        $response = new Junior\Client\Response($this->result,
                                               $this->id);
        
        $this->assertEquals($this->result, $response->__toString());
    }

    public function testToStringError()
    {
        $response = new Junior\Client\Response(null,
                                               null,
                                               $this->error_code,
                                               $this->error_message);

        $this->assertEquals("{$response->error_code}: {$response->error_message}",
                            $response->__toString());
    }

}

?>