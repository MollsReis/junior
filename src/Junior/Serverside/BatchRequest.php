<?php

namespace Junior\Serverside;

class BatchRequest extends Request {

    public function isBatch()
    {
        return true;
    }
}