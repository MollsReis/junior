<?php

namespace Junior\Serverside;

class BatchRequest extends Request {

    public $batchedRequests = [];

    public function __construct($json)
    {
        foreach ($json as $requestJSON) {
            $this->batchedRequests[] = new Request($requestJSON);
        }
        parent::__construct($json);
    }

    public function isBatch()
    {
        return true;
    }
}