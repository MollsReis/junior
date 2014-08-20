<?php

namespace Junior\Serverside;

use Junior\Serverside\Exception as ServerException;

class BatchRequest extends Request {

    public $batchedRequests = [],
           $validityMap = [];

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

    public function getIds()
    {
        return array_map(function(Request $request) {
            return $request->getId();
        }, $this->batchedRequests);
    }

    public function checkValid()
    {
        foreach ($this->batchedRequests as $key => $request) {
            try {
                $request->checkValid();
                $this->validityMap[$key] = true;
            } catch (ServerException $e) {
                $this->validityMap[$key] = $e;
            }
        }
    }
}