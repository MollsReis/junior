<?php
namespace Junior;
use Junior\Serverside\Request;

foreach(array('Request', 'Exception') as $file) {
    require_once('Junior'. DIRECTORY_SEPARATOR . 'Serverside' . DIRECTORY_SEPARATOR . $file . '.php');
}

const ERROR_INVALID_REQUEST = -32600;
const ERROR_METHOD_NOT_FOUND = -32601;
const ERROR_INVALID_PARAMS = -32602;
const ERROR_EXCEPTION = -32099;

class Server {

    // create new server
    public function __construct($exposed_instance)
    {
        $this->exposed_instance = $exposed_instance;
        $this->input = 'php://input';
    }

    // check for method existence
    public function methodExists($method_name)
    {
        return method_exists($this->exposed_instance, $method_name);
    }

    // attempt to invoke the method with params
    public function invokeMethod($method, $params)
    {
        // for named parameters, convert from object to assoc array
        if (is_object($params)) {
            $array = array();
            foreach ($params as $key => $val) {
                $array[$key] = $val;
            }
            $params = array($array);
        }
        // for no params, pass in empty array
        if ($params === null) {
            $params = array();
        }
        $reflection = new \ReflectionMethod($this->exposed_instance, $method);
        return $reflection->invokeArgs($this->exposed_instance, $params);
    }

    // process json-rpc request
    public function process()
    {
        // try to read input
        try {
            $json = file_get_contents($this->input);
        } catch (\Exception $e) {
            $message = "Server unable to read request body.";
            $message .= PHP_EOL . $e->getMessage();
            throw new Serverside\Exception($message);
        }

        // handle communication errors
        if ($json === false) {
            throw new Serverside\Exception("Server unable to read request body.");
        }

        // create request object
        $request = $this->makeRequest($json);

        // set content type to json if not testing
        if (ENV != 'TEST') {
            header('Content-type: application/json');
        }

        // handle json parse error and empty batch
        if ($request->error_code && $request->error_message) {
            echo $request->toResponseJSON();
            return;
        }

        // respond with json
        echo $this->handleRequest($request);
    }

    // create new request (used for test mocking purposes)
    public function makeRequest($json)
    {
        return new Request($json);
    }

    // handle request object / return response json
    public function handleRequest($request)
    {
        // recursion for batch
        if ($request->isBatch()) {
            $batch = array();
            foreach ($request->requests as $req) {
                $batch[] = $this->handleRequest($req);
            }
            return '[' . implode(',',array_filter($batch, function($a){return $a !== null;})) . ']';
        }

        // check validity of request
        if ($request->checkValid()) {
            // check for method existence
            if (!$this->methodExists($request->method)) {
                $request->error_code = ERROR_METHOD_NOT_FOUND;
                $request->error_message = "Method not found.";
                return $request->toResponseJSON();
            }

            // try to call method with params
            try {
                $response = $this->invokeMethod($request->method, $request->params);
                if (!$request->isNotify()) {
                    $request->result = $response;
                } else {
                    return null;
                }
            // handle exceptions
            } catch (\Exception $e) {
                $request->error_code = ERROR_EXCEPTION;
                $request->error_message = $e->getMessage();
            }
        }

        // return whatever we got
        return $request->toResponseJSON();
    }

}
