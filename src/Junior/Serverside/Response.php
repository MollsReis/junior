<?php

namespace Junior\Serverside;

class Response {

    public $id, $output;

    public function __construct($id, $output)
    {
        $this->id = $id;
        $this->output = $output;
    }

    public function toJSON()
    {
        return json_encode(
            [
                'jsonrpc' => '2.0',
                'result'   => $this->output,
                'id'       => $this->id
            ]
        );
    }

}