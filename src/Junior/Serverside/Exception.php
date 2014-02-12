<?php

namespace Junior\Serverside;

class Exception extends \Exception {
    const MESSAGE_INVALID_JSON = 'Parse error: Invalid JSON was received by the server';
    const CODE_INVALID_JSON = -32700;

    const MESSAGE_ERROR_PARSING_JSON = 'Parse error: An error occurred on the server while parsing the JSON text';
    const CODE_ERROR_PARSING_JSON = -32700;

    const MESSAGE_INVALID_REQUEST = 'Invalid Request: The JSON sent is not a valid Request object.';
    const CODE_INVALID_REQUEST = -32600;

    const MESSAGE_METHOD_DOES_NOT_EXIST = 'Method not found: The method does not exist.';
    const CODE_METHOD_DOES_NOT_EXIST = -32601;

    const MESSAGE_METHOD_NOT_AVAILABLE = 'Method not found: The method is not available.';
    const CODE_METHOD_NOT_AVAILABLE = -32601;

    const MESSAGE_INVALID_PARAMS = 'Invalid params: Invalid method parameter(s).';
    const CODE_INVALID_PARAMS = -32602;

    const MESSAGE_INTERNAL_ERROR = 'Internal error: Internal JSON-RPC error.';
    const CODE_INTERNAL_ERROR = -32603;
}