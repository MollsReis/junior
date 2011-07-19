<?php
namespace Junior\Client;

const JSON_RPC_VERSION = "2.0";

class Request {

    // create a new json rpc request object
    public function __construct($method, $params = null, $notify = false)
    {
        // ensure params are array
        if ($params !== null && !is_array($params)) {
            $params = array($params);
        }

        // check for illegal method prefix
        if (substr($method,0,4) == 'rpc.') {
            throw new \Exception("Illegal method name; Method cannot start with 'rpc.'");
        }

        // ensure params are utf8
        if ($params !== null) {
            foreach ($params as &$param) {
                $param = utf8_encode($param);
            }
        }

        $this->method = $method;
        $this->params = $params;

        // do not create id for notify
        if ($notify == false) {
            $this->id = uniqid();
        }
    }

    // return an associated array for this object 
    public function getArray()
    {
        $arr = array('jsonrpc' => JSON_RPC_VERSION, 'method' => $this->method);
        if ($this->params) {
            $arr['params'] = $this->params;
        }
        if ($this->id) {
            $arr['id'] = $this->id;
        }
        return $arr;
    }

    // return the json for this object
    public function getJSON()
    {
        return json_encode($this->getArray());
    }

}

?>
