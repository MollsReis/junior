<?php

namespace Junior;

use Junior\Serverside\Request;
use Junior\Serverside\NotifyRequest;
use Junior\Serverside\BatchRequest;
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
        $parsedJSON = json_decode($json);

        //TODO check for errors

        if (is_array($parsedJSON)) {
            return new BatchRequest($parsedJSON);
        } elseif (!isset($parsedJSON->id)) {
            return new NotifyRequest($parsedJSON);
        } else {
            return new Request($parsedJSON);
        }
    }

    public function invoke(Request $request)
    {
        // handle batched requests with recursion
        if ($request->isBatch()) {
            $returns = [];
            foreach ($request->batchedRequests as $batchedRequest) {
                $returns[] = $this->invoke($batchedRequest);
            }
            return $returns;
        }

        $method = $request->method;
        $params = $request->params;

        // for named parameters, convert from object to assoc array
        if (is_object($params)) {
            $array = [];
            foreach ($params as $key => $val) {
                $array[$key] = $val;
            }
            $params = [ $array ];
        }

        // for no params, pass in empty array
        if ($params === null) {
            $params = [];
        }

        // method needs to exist on exposed instance
        //TODO check for method existence

        $reflection = new \ReflectionMethod($this->exposedInstance, $method);

        // only allow calls to public functions
        if (!$reflection->isPublic()) {
            throw new Serverside\Exception('Called method is not publicly accessible.');
        }

        // enforce correct number of arguments
        $numRequiredParams = $reflection->getNumberOfRequiredParameters();
        if ($numRequiredParams > count($params)) {
            throw new Serverside\Exception('Too few parameters passed.');
        }

        $output = $reflection->invokeArgs($this->exposedInstance, $params);

        if ($request->isNotify()) {
            return null;
        }

        return $output;
    }

    public function createResponse($output)
    {
        //TODO do other things?
        return new Response($output);
    }
}