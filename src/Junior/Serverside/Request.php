<?php

namespace Junior\Serverside;

class Request {

    public $parsed, $method, $params;

    public function __construct($json)
    {
        $this->json = $json;
        $this->method = $this->json->method;
        $this->params = $this->json->params;
    }

    public function isValid()
    {
        //TODO
    }

    public function isNotify()
    {
        return false;
    }

    public function isBatch()
    {
        return false;
    }
}