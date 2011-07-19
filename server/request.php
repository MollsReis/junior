<?php
namespace Junior\Server;

const JSON_RPC_VERSION = "2.0";
const ERROR_PARSE_ERROR = -32700;
const ERROR_INVALID_REQUEST = -32600;
const ERROR_MISMATCHED_VERSION = -32000;
const ERROR_RESERVED_PREFIX = -32001;

class Request {

    // create new server request object from raw json
    public function __construct($json)
    {
        $this->batch = false;
        $this->raw = $json;

        // handle empty request
        if ($this->raw === "") {
            $this->json_rpc = $obj->jsonrpc;
            $this->error_code = ERROR_INVALID_REQUEST;
            $this->error_message = "Invalid request.";
            return;
        }

        // parse json into object
        $obj = json_decode($json);

        // handle json parse error
        if ($obj === null) {
            $this->json_rpc = $obj->jsonrpc;
            $this->error_code = ERROR_PARSE_ERROR;
            $this->error_message = "Parse error.";
            return;
        }

        // array of objects for batch
        if (is_array($obj)) {

            // empty batch
            if (count($obj) == 0) {
                $this->json_rpc = $obj->jsonrpc;
                $this->error_code = ERROR_INVALID_REQUEST;
                $this->error_message = "Invalid request.";
                return;
            }

            // non-empty batch
            $this->batch = true;
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
            $this->json_rpc = $obj->jsonrpc;
            $this->method = $obj->method;
            $this->params = $obj->params;
            $this->id = $obj->id;
        }
    }

    // returns true if request is valid or returns false assigns error
    public function checkValid()
    {
        // error code/message already set
        if ($this->error_code && $this->error_message) {
            return false;
        }

        // missing jsonrpc or method
        if (!$this->json_rpc || !$this->method) {
            $this->error_code = ERROR_INVALID_REQUEST;
            $this->error_message = "Invalid request.";
            return false;
        }

        // illegal method name
        if (!is_string($this->method)) {
            $this->error_code = ERROR_INVALID_REQUEST;
            $this->error_message = "Invalid request.";
            return false;
        }

        // reserved method prefix
        if (substr($this->method,0,4) == 'rpc.') {
            $this->error_code = ERROR_RESERVED_PREFIX;
            $this->error_message = "Illegal method name; Method cannot start with 'rpc.'";
            return false;
        }

        // mismatched json-rpc version
        if ($this->json_rpc != "2.0") {
            $this->error_code = ERROR_MISMATCHED_VERSION;
            $this->error_version = "Client/Server JSON-RPC version mismatch; Expected '2.0'";
            return false;
        }

        // valid request
        return true;
    }

    // returns true if request is a batch
    public function isBatch()
    {
        return $this->batch;
    }

    // returns true if request is a notification
    public function isNotify()
    {
        if ($this->id) {
            return false;
        }
        return true;
    }

    // return raw JSON response
    public function toResponseJSON()
    {
        // successful response
        $arr = array('jsonrpc' => JSON_RPC_VERSION);
        if ($this->result) {
            $arr['result'] = $this->result;
            $arr['id'] = $this->id;
            return json_encode($arr);
        // error response
        } else {
            $arr['error'] = array('code' => $this->error_code, 'message' => $this->error_message);
            $arr['id'] = $this->id;
            return json_encode($arr);
        }
    }

}

?>

