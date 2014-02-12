<?php

namespace Junior;

use Junior\Serverside\Exception as ServerException,
    Junior\Serverside\Request,
    Junior\Serverside\NotifyRequest,
    Junior\Serverside\BatchRequest,
    Junior\Serverside\Response,
    Junior\Serverside\ErrorResponse,
    Junior\Serverside\Adapter\AdapterInterface,
    Junior\Serverside\Adapter\StandardAdapter;

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
        try {
            $request->checkValid();
            $output = $this->invoke($request);
            $response = $this->createResponse($output);
        } catch (ServerException $exception) {
            $response = $this->createErrorResponse($exception->getCode(), $exception->getMessage());
        }
        $this->adapter->respond($response);
    }

    public function createRequest($json)
    {
        $parsedJSON = json_decode($json);

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

        $method = $request->getMethod();
        $params = $request->getParams();

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
        if (!method_exists($this->exposedInstance, $method)) {
            throw new ServerException(
                ServerException::MESSAGE_METHOD_DOES_NOT_EXIST,
                ServerException::CODE_METHOD_DOES_NOT_EXIST
            );
        }

        $reflection = new \ReflectionMethod($this->exposedInstance, $method);

        // only allow calls to public functions
        if (!$reflection->isPublic()) {
            throw new ServerException(
                ServerException::MESSAGE_METHOD_NOT_AVAILABLE,
                ServerException::CODE_METHOD_NOT_AVAILABLE
            );
        }

        // enforce correct number of arguments
        $numRequiredParams = $reflection->getNumberOfRequiredParameters();
        if ($numRequiredParams > count($params)) {
            throw new ServerException(
                ServerException::MESSAGE_INVALID_PARAMS,
                ServerException::CODE_INVALID_PARAMS
            );
        }

        $output = $reflection->invokeArgs($this->exposedInstance, $params);

        if ($request->isNotify()) {
            return null;
        }

        return $output;
    }

    public function createResponse($output)
    {
        //TODO other things?
        return new Response($output);
    }

    public function createErrorResponse($message, $code)
    {
        //TODO other things?
        return new ErrorResponse($message, $code);
    }
}