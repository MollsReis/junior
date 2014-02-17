<?php

class StubAdapter implements Junior\Serverside\Adapter\AdapterInterface {

    private $_stubbedJSON;

    public function __construct($json)
    {
        $this->_stubbedJSON = $json;
    }

    public function receive()
    {
        return $this->_stubbedJSON;
    }

    public function respond($json)
    {
        echo $json;
    }
}