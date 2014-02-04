<?php

namespace Junior\Serverside;

class Request {

    public $raw, $parsed, $method;

    public function __construct($json)
    {
        $this->raw = $json;
        $this->parsed = json_decode($json);
        $this->method = $this->parsed->method;
    }
}