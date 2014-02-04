<?php

namespace Junior\Serverside\Adapter;

use Junior\Serverside\Response;

interface AdapterInterface {
    public function receive();
    public function respond(Response $response);
}