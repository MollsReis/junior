<?php

namespace Junior;

use Junior\Serverside\Request;
use Junior\Serverside\Response;
use Junior\Serverside\Adapter\AdapterInterface;
use Junior\Serverside\Adapter\StandardAdapter;

const ERROR_INVALID_REQUEST = -32600;
const ERROR_METHOD_NOT_FOUND = -32601;
const ERROR_INVALID_PARAMS = -32602;
const ERROR_EXCEPTION = -32099;

class Server {

    public $exposedInstance, $adapter;

    public function __construct($exposedInstance, AdapterInterface $adapter = null)
    {
        $this->exposedInstance = $exposedInstance;
        $this->adapter = $adapter ?: new StandardAdapter();
    }

    public function process()
    {
        $json = $this->adapter->receive();
        $request = $this->createRequest($json);
        $output = $this->invoke($request);
        $response = $this->createResponse($output);
        $this->adapter->respond($response);
    }

    public function createRequest($json)
    {
        return new Request($json);
    }

    public function invoke(Request $request)
    {
        $method = $request->method;
        $params = $request->params;

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

        $reflection = new \ReflectionMethod($this->exposedInstance, $method);

        // only allow calls to public functions
        if (!$reflection->isPublic()) {
            throw new Serverside\Exception('Called method is not publicly accessible.');
        }

        // enforce correct number of arguments
        $num_required_params = $reflection->getNumberOfRequiredParameters();
        if ($num_required_params > count($params)) {
            throw new Serverside\Exception('Too few parameters passed.');
        }

        return $reflection->invokeArgs($this->exposedInstance, $params);
    }

    public function createResponse()
    {
        //TODO
        return new Response();
    }
}