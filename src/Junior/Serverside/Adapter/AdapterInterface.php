<?php

namespace Junior\Serverside\Adapter;

interface AdapterInterface {

    // receive raw JSON as a string
    public function receive();

    // respond with raw JSON as a string
    public function respond($json);
}