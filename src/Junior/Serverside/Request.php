<?php

namespace Junior\Serverside;


class Request
{
    const JSON_RPC_VERSION         = "2.0";
    const ERROR_PARSE_ERROR        = -32700;
    const ERROR_INVALID_REQUEST    = -32600;
    const ERROR_MISMATCHED_VERSION = -32000;
    const ERROR_RESERVED_PREFIX    = -32001;
    const VALID_FUNCTION_NAME      = '/^[a-zA-Z_][a-zA-Z0-9_]*$/';

    public $batch;
    public $raw;
    public $result;
    public $jsonRpc;
    public $errorCode;
    public $errorMessage;
    public $method;
    public $params;
    public $id;

    /**
     * create new server request object from raw json
     *
     * @param $json
     */
    public function __construct($json)
    {
        $this->batch = false;
        $this->raw   = $json;

        // handle empty request
        if ($this->raw === "") {
            $this->jsonRpc      = self::JSON_RPC_VERSION;
            $this->errorCode    = self::ERROR_INVALID_REQUEST;
            $this->errorMessage = "Invalid Request.";

            return;
        }

        // parse json into object
        $obj = json_decode($json);

        // handle json parse error
        if ($obj === null) {
            $this->jsonRpc      = self::JSON_RPC_VERSION;
            $this->errorCode    = self::ERROR_PARSE_ERROR;
            $this->errorMessage = "Parse error.";

            return;
        }

        // array of objects for batch
        if (is_array($obj)) {

            // empty batch
            if (count($obj) == 0) {
                $this->jsonRpc      = self::JSON_RPC_VERSION;
                $this->errorCode    = self::ERROR_INVALID_REQUEST;
                $this->errorMessage = "Invalid Request.";

                return;
            }

            // non-empty batch
            $this->batch    = true;
            $this->requests = array();
            foreach ($obj as $req) {
                // recursion for bad requests
                if (!is_object($req)) {
                    $this->requests[] = new Request('');
                    // recursion for good requests
                } else {
                    $this->requests[] = new Request(json_encode($req));
                }
            }

            // single request
        } else {
            $this->jsonRpc = $obj->jsonrpc;
            $this->method  = $obj->method;
            if (property_exists($obj, 'params')) {
                $this->params = $obj->params;
            };
            if (property_exists($obj, 'id')) {
                $this->id = $obj->id;
            };
        }
    }

    /**
     * returns true if request is valid or returns false assigns error
     *
     * @return bool
     */
    public function checkValid()
    {
        // error code/message already set
        if ($this->errorCode && $this->errorMessage) {
            return false;
        }

        // missing jsonrpc or method
        if (!$this->jsonRpc || !$this->method) {
            $this->errorCode    = self::ERROR_INVALID_REQUEST;
            $this->errorMessage = "Invalid Request.";

            return false;
        }

        // reserved method prefix
        if (substr($this->method, 0, 4) == 'rpc.') {
            $this->errorCode    = self::ERROR_RESERVED_PREFIX;
            $this->errorMessage = "Illegal method name; Method cannot start with 'rpc.'";

            return false;
        }

        // illegal method name
        if (!preg_match(self::VALID_FUNCTION_NAME, $this->method)) {
            $this->errorCode    = self::ERROR_INVALID_REQUEST;
            $this->errorMessage = "Invalid Request.";

            return false;
        }

        // mismatched json-rpc version
        if ($this->jsonRpc != "2.0") {
            $this->errorCode    = self::ERROR_MISMATCHED_VERSION;
            $this->errorMessage = "Client/Server JSON-RPC version mismatch; Expected '2.0'";

            return false;
        }

        // valid request
        return true;
    }

    /**
     * returns true if request is a batch
     *
     * @return bool
     */
    public function isBatch()
    {
        return $this->batch;
    }

    /**
     * returns true if request is a notification
     *
     * @return bool
     */
    public function isNotify()
    {
        return !isset($this->id);
    }

    /**
     * return raw JSON response
     *
     * @return string
     */
    public function toResponseJSON()
    {
        // successful response
        $arr = array('jsonrpc' => self::JSON_RPC_VERSION);
        if ($this->result !== null) {
            $arr['result'] = $this->result;
            $arr['id']     = $this->id;

            return json_encode($arr);
            // error response
        } else {
            $arr['error'] = array('code' => $this->errorCode, 'message' => $this->errorMessage);
            $arr['id']    = $this->id;

            return json_encode($arr);
        }
    }

}
