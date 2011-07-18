<?php
namespace Junior\Server;

const ERROR_METHOD_NOT_FOUND = -32601;
const ERROR_INVALID_PARAMS = -32602;

class Server {

    // create new server
    public function __construct($exposed_instance)
    {
        $this->exposed_instance = $exposed_instance;
    }

    // check for method existence
    public function methodExists($method_name)
    {
        $reflection = new \ReflectionClass($this->exposed_instance);
        return $reflection->hasMethod($method_name);
    }

    // attempt to invoke the method with params
    public function invokeMethod($method, $params)
    {
        $reflection = new \ReflectionMethod($this->exposed_instance, $method);
        return $reflection->invokeArgs($this->exposed_instance, $params);
    }

    // process json-rpc request
    public function process()
    {
        // read input and create request object
        $json = file_get_contents('php://input');
        if ($json === false) {
            throw new \Exception("Server unable to read request body.");
        }
        $request = new Request($json);

        // set content type to json
        header('Content-type: application/json');

        // handle json parse error
        if ($request->error_code && $request->error_message) {
            echo $request->toResponseJSON();
            return;
        }

        // respond with json
        echo $this->handleRequest($request);
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
            return json_encode(array_filter($batch, function($a){return $a === null;}));
        }

        // check validity of request
        if ($request->checkValid()) {
            // check for method existence
            if (!$this->methodExists($request->method)) {
                return $request->toErrorResponse(ERROR_METHOD_NOT_FOUND, "Method not found.");
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
            } catch (Exception $e) {
                $request->error_code = $e->getCode();
                $request->error_message = $e->getMessage();
            }
        }

        // return whatever we got
        return $request->toResponseJSON();
    }

}

?>
