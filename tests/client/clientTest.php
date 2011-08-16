<?php
class ClientTest extends \PHPUnit_Framework_TestCase {

    public function getEmptyRequest()
    {
        return $this->getMock('\Junior\Client\Request',
                              array(),
                              array(),
                              '',
                              false,
                              false,
                              false);
    }

    public function getEmptyResponse()
    {
        return $this->getMock('\Junior\Client\Response',
                              array(),
                              array(),
                              '',
                              false,
                              false,
                              false);
    }

    public function getMockClient(Array $methods = array())
    {
        return $this->getMock('\Junior\Client\Client',
                                 $methods,
                                 array('foo'));
    }

    public function testSendRequestGoodId()
    {
        $request = $this->getEmptyRequest();
        $request->id = 10;

        $response = $this->getEmptyResponse();
        $response->id = 10;

        $client = $this->getMockClient(array('send'));
        $client->expects($this->once())
               ->method('send')
               ->will($this->returnValue($response));

        $this->assertEquals($response, $client->sendRequest($request));
    }

    public function testSendRequestBadId()
    {
        $request = $this->getEmptyRequest();
        $request->id = 10;

        $response = $this->getEmptyResponse();
        $response->id = 11;

        $client = $this->getMockClient(array('send'));
        $client->expects($this->once())
               ->method('send')
               ->will($this->returnValue($response));

        $this->setExpectedException('Junior\Client\Exception');

        $client->sendRequest($request);
    }

    public function testSendNotifyGood()
    {
        $request = $this->getEmptyRequest();
        $request->id = null;

        $client = $this->getMockClient(array('send'));
        $client->expects($this->once())
               ->method('send')
               ->will($this->returnValue(true));

        $this->assertTrue($client->sendNotify($request));
    }

    public function testSendNotifyBad()
    {
        $request = $this->getEmptyRequest();
        $request->id = 10;

        $client = $this->getMockClient(array('send'));
        $client->expects($this->never())
               ->method('send');

        $this->setExpectedException('Junior\Client\Exception');

        $client->sendNotify($request);
    }

}

?>