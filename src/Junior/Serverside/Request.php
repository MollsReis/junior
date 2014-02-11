<?php

namespace Junior\Serverside;

class Request {

    public $json, $method, $params;

    public function __construct($json)
    {
        $this->json = $json;
        $this->method = $this->json->method;
        $this->params = $this->json->params;
    }

    public function isValid()
    {
        if (!isset($this->json->jsonrpc) || $this->json->jsonrpc !== '2.0') {
            //TODO exception?
            return false;
        } elseif (!isset($this->json->method)) {
            //TODO exception?
            return false;
        } elseif (strpos(strtolower($this->json->method), 'rpc.') === 0) {
            //TODO exception?
            return false;
        } elseif (isset($this->json->params) && !is_array($this->json->params) && !is_object($this->json->params)) {
            //TODO exception?
            return false;
        }
        return true;
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