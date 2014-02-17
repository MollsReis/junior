<?php

namespace Junior\Serverside\Adapter;

use Junior\Serverside\Exception as ServerException;

class StandardAdapter implements AdapterInterface {

    public function receive() {
        try {
            return file_get_contents('php://input');
        } catch (\Exception $exception) {
            throw new ServerException(
                ServerException::MESSAGE_UNABLE_TO_READ_REQUEST,
                ServerException::CODE_UNABLE_TO_READ_REQUEST,
                $exception
            );
        }
    }

    public function respond($json) {
        echo $json;
    }
}