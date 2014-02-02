<?php
use Spray\Spray;


class RequestTest extends PHPUnit_Framework_TestCase {

    public function setUp()
    {
        $this->method = 'method';
        $this->params = 'params';
    }

    public function testNewRequestGood()
    {
        $request = new Junior\Clientside\Request($this->method, $this->params);
        $this->assertEquals($this->method, $request->method);
        $this->assertEquals(array($this->params), $request->params);
        $this->assertNotNull($request->id);

        $request = new Junior\Clientside\Request($this->method, array($this->params));
        $this->assertEquals(array($this->params), $request->params);
    }

    public function testNewRequestNotify()
    {
        $request = new Junior\Clientside\Request($this->method, $this->params, true);
        $this->assertEquals($this->method, $request->method);
        $this->assertEquals(array($this->params), $request->params);
        $this->assertNull($request->id);
    }

    public function testNewRequestBad()
    {
        $this->setExpectedException('Junior\Clientside\Exception');
        
        $request = new Junior\Clientside\Request('rpc.' . $this->method, $this->params);
    }

    public function testGetArray()
    {
        $request = new Junior\Clientside\Request($this->method, $this->params);
        $this->assertEquals(array(
                              'jsonrpc' => \Junior\Clientside\Request::JSON_RPC_VERSION,
                              'method' => $this->method,
                              'params' => array($this->params),
                              'id' => $request->id
                            ),
                            $request->getArray());
    }

    public function testGetArrayNoParams()
    {
        $request = new Junior\Clientside\Request($this->method);
        $this->assertEquals(array(
                              'jsonrpc' => \Junior\Clientside\Request::JSON_RPC_VERSION,
                              'method' => $this->method,
                              'id' => $request->id
                            ),
                            $request->getArray());
    }

    public function testGetArrayNotify()
    {
        $request = new Junior\Clientside\Request($this->method, $this->params, true);
        $this->assertEquals(array(
                              'jsonrpc' => Junior\Clientside\Request::JSON_RPC_VERSION,
                              'method' => $this->method,
                              'params' => array($this->params)
                            ),
                            $request->getArray());
    }

    public function testGetJSON()
    {
        $request = new Junior\Clientside\Request($this->method, $this->params);
        $this->assertEquals(json_encode(array(
                              'jsonrpc' => Junior\Clientside\Request::JSON_RPC_VERSION,
                              'method' => $this->method,
                              'params' => array($this->params),
                              'id' => $request->id
                            )),
                            $request->getJSON());
    }

    public function testGetJSONNoParams()
    {
        $request = new Junior\Clientside\Request($this->method);
        $this->assertEquals(json_encode(array(
                              'jsonrpc' => Junior\Clientside\Request::JSON_RPC_VERSION,
                              'method' => $this->method,
                              'id' => $request->id
                            )),
                            $request->getJSON());
    }

    public function testGetJSONNotify()
    {
        $request = new Junior\Clientside\Request($this->method, $this->params, true);
        $this->assertEquals(json_encode(array(
                              'jsonrpc' => Junior\Clientside\Request::JSON_RPC_VERSION,
                              'method' => $this->method,
                              'params' => array($this->params)
                            )),
                            $request->getJSON());
    }

}

?>