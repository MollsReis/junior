<?php

namespace Junior\Serverside;

class ErrorResponse {

    public $message, $code;

    public function __construct($message, $code)
    {
        $this->message = $message;
        $this->code = $code;
    }

    public function toJSON()
    {
        //TODO
    }
}