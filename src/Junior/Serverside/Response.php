<?php

namespace Junior\Serverside;

class Response {

    public $output;

    public function __construct($output)
    {
        $this->output = $output;
    }

    public function toJSON()
    {
        //TODO
    }

}