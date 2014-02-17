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
        return json_encode(
            [
                "jsonrpc" => "2.0",
                "id"      => $this->id,
                "error"   => [
                    "message" => $this->message,
                    "code"    => $this->code
                ]
            ]
        );
    }
}