<?php

namespace Junior\Serverside;

class BatchResponse extends Response {

    public $ids, $outputs;

    public function __construct(Array $ids, Array $outputs)
    {
        $this->ids = $ids;
        $this->outputs = $outputs;
    }

    public function toJSON()
    {
        $responses = [];
        foreach ($this->ids as $reqNum => $id) {
            if (!is_null($id)) {
                $response = new Response($id, $this->outputs[$reqNum]);
                $responses[] = json_decode($response->toJSON(), true);
            }
        }
        return json_encode($responses);
    }
}