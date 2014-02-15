<?php

namespace Junior\Serverside;

class ErrorResponse extends Response {

    public $id, $message, $code;

    public function __construct($id, $message, $code)
    {
        $this->id = $id;
        $this->message = $message;
        $this->code = $code;
    }

    public function toJSON()
    {
        //TODO
    }
}