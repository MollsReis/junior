<?php
namespace Junior;
use Junior\Clientside\Request,
    Junior\Clientside\Response,
    Junior\Clientside\Exception;

class Client {

    public $uri, $authHeader;

    // create new client connection
    public function __construct($uri)
    {
        $this->uri = $uri;
    }

    // shortcut to call a single, non-notification request
    public function __call($method, $params)
    {
        $req = new Request($method, $params);
        return $this->sendRequest($req);
    }

    // set basic http authentication
    public function setBasicAuth($username, $password)
    {
        $this->authHeader = "Authorization: Basic " . base64_encode("$username:$password") . "\r\n";
    }

    // clear any existing http authentication
    public function clearAuth()
    {
        $this->authHeader = null;
    }

    // send a single request object
    public function sendRequest($req)
    {
        $response = $this->send($req->getJSON());

        if ($response->id != $req->id) {
            throw new Clientside\Exception("Mismatched request id");
        }

        if(isset($response->error_code)) {
            throw new Clientside\Exception("{$response->error_code} {$response->error_message}", $response->error_code);
        }

        return $response->result;
    }

    // send a single notify request object
    public function sendNotify($req)
    {
        if (property_exists($req, 'id') && $req->id != null) {
            throw new Clientside\Exception("Notify requests must not have ID set");
        }

        $this->send($req->getJSON(), true);
        return true;
    }

    // send an array of request objects as a batch
    public function sendBatch($reqs)
    {
        $arr = array();
        $ids = array();
        $all_notify = true;
        foreach ($reqs as $req) {
            if ($req->id) {
                $all_notify = false;
                $ids[] = $req->id;
            }
            $arr[] = $req->getArray();
        }
        $response = $this->send(json_encode($arr), $all_notify);

        // no response if batch is all notifications
        if ($all_notify) {
            return true;
        }

        // check for missing ids and return responses in order of requests
        $ordered_response = array();
        foreach ($ids as $id) {
            if (array_key_exists($id, $response)) {
                $ordered_response[] = $response[$id];
                unset($response[$id]);
            } else {
                throw new Clientside\Exception("Missing id in response");
            }
        }

        // check for extra ids in response
        if (count($response) > 0) {
            throw new Clientside\Exception("Extra id(s) in response");
        }

        return $ordered_response;
    }

    // send raw json to the server
    public function send($json, $notify = false)
    {
        // use http authentication header if set
        $header = "Content-Type: application/json\r\n";
        if ($this->authHeader) {
            $header .= $this->authHeader;
        }

        // prepare data to be sent
        $opts = array(
            'http' => array(
                'method'  => 'POST',
                'header'  => $header,
                'content' => $json));
        $context = stream_context_create($opts);

        // try to physically send data to destination 
        try {
            $response = file_get_contents($this->uri, false, $context);
        } catch (\Exception $e) {
            $message = "Unable to connect to {$this->uri}";
            $message .= PHP_EOL . $e->getMessage();
            throw new Clientside\Exception($message);
        }

        // handle communication errors
        if ($response === false) {
            throw new Clientside\Exception("Unable to connect to {$this->uri}");
        }

        // notify has no response
        if ($notify) {
            return true;
        }

        // try to decode json
        $json_response = $this->decodeJSON($response);

        // handle response, create response object and return it
        return $this->handleResponse($json_response);
    }

    // decode json throwing exception if unable
    function decodeJSON($json)
    {
        $json_response = json_decode($json);
        if ($json_response === null) {
            throw new Clientside\Exception("Unable to decode JSON response from: {$json}");
        }
        return $json_response;
    }

    // handle the response and return a result or an error
    public function handleResponse($response)
    {
        // recursion for batch
        if (is_array($response)) {
            $response_arr = array();
            foreach ($response as $res) {
                $response_arr[$res->id] = $this->handleResponse($res);
            }
            return $response_arr;
        }

        // return error response
        if (property_exists($response, 'error')) {
            return new Response(null, $response->id, $response->error->code, $response->error->message);
        }

        // return successful response
        return new Response($response->result, $response->id);
    }

}
