<?php
namespace Junior\Server;

const JSON_RPC_VERSION = "2.0";
const ERROR_PARSE_ERROR = -32700;
const ERROR_INVALID_REQUEST = -32600;
const ERROR_MISMATCHED_VERSION = -32000;

class Request {

    // create new server request object from raw json
    public function __construct($json)
    {
        $this->raw = $json;

        // handle empty request
        if ($this->raw === "") {
            $this->error_code = ERROR_INVALID_REQUEST;
            $this->error_message = "Invalid request.";
            return;
        }

        $obj = json_decode($json);

        // handle json parse error
        if ($obj === null) {
            $this->error_code = ERROR_PARSE_ERROR;
            $this->error_message = "Parse error.";
            return;
        }

        // array of objects for batch
        if (is_array($obj)) {

            // empty batch
            if (count($obj) == 0) {
                $this->error_code = ERROR_INVALID_REQUEST;
                $this->error_message = "Invalid request.";
                return;
            }

            // good batch
            $this->batch = true;
            $this->requests = array();
            foreach ($obj as $req) {
                $this->requests[] = new Request(json_encode($req));
            }

        // single request
        } else {
            $this->batch = false;
            $this->json_rpc = $obj->jsonrpc;
            $this->method = $obj->method;
            $this->params = $obj->params;
            $this->id = $obj->id;
        }
    }

    // returns true if request is valid or returns false assigns error
    public function checkValid()
    {
        // mismatched json-rpc version
        if ($this->json_rpc != "2.0") {
            $this->error_code = ERROR_MISMATCHED_VERSION;
            $this->error_version = "Client/Server JSON-RPC version mismatch.";
            return false;
        }

        // illegal method name
        if (!is_string($this->method)) {
            $this->error_code = ERROR_INVALID_REQUEST;
            $this->error_message = "Invalid request.";
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
        return json_encode($this->toArray());
    }

    // return assoc array for JSON response
    public function toArray()
    {
        // successful response
        $arr = array('jsonrpc' => JSON_RPC_VERSION);
        if ($this->result) {
            $arr['result'] = $this->result;
            $arr['id'] = $this->id;
            return $arr;
        // error response
        } else {
            $arr['error'] = array('code' => $this->error_code, 'message' => $this->error_message);
            if ($this->id) {
                $arr['id'] = $this->id;
            }
            return $arr;
        }
    }

}

?>

