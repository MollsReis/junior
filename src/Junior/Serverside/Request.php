<?php

namespace Junior\Serverside;

use Junior\Serverside\Exception as ServerException;

class Request {

    public $json, $error;

    public function __construct($json)
    {
        $this->json = $json;
    }

    public function getId()
    {
        return isset($this->json->id) ? $this->json->id : null;
    }

    public function getMethod()
    {
        return isset($this->json->method) ? $this->json->method : null;
    }

    public function getParams()
    {
        return isset($this->json->params) ? $this->json->params : null;
    }

    public function checkValid()
    {
        if ($this->json === null) {
            $message = ServerException::MESSAGE_INVALID_JSON;
            $code = ServerException::CODE_INVALID_JSON;

        } elseif (!isset($this->json->jsonrpc) || $this->json->jsonrpc !== '2.0') {
            $message = ServerException::MESSAGE_INVALID_REQUEST;
            $code = ServerException::CODE_INVALID_REQUEST;

        } elseif (isset($this->json->id) && !is_string($this->json->id) && !is_numeric($this->json->id) && !is_null($this->json->id)) {
            $message = ServerException::MESSAGE_INVALID_REQUEST;
            $code = ServerException::CODE_INVALID_REQUEST;

        } elseif (!isset($this->json->method)) {
            $message = ServerException::MESSAGE_INVALID_REQUEST;
            $code = ServerException::CODE_INVALID_REQUEST;

        } elseif (strpos(strtolower($this->json->method), 'rpc.') === 0) {
            $message = ServerException::MESSAGE_INVALID_REQUEST;
            $code = ServerException::CODE_INVALID_REQUEST;

        } elseif (isset($this->json->params) && !is_array($this->json->params) && !is_object($this->json->params)) {
            $message = ServerException::MESSAGE_INVALID_REQUEST;
            $code = ServerException::CODE_INVALID_REQUEST;
        }

        if (isset($message) && isset($code)) {
            throw new ServerException($message, $code);
        }
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