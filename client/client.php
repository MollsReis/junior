<?php
namespace Junior\Client;

class Client {

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

    // send a single request object
    public function sendRequest($req)
    {
        $response = $this->send($req->getJSON());

        if ($response->id != $req->id) {
            throw new Exception("Mismatched request id");
        }

        return $response;
    }

    // send a single notify request object
    public function sendNotify($req)
    {
        if ($req->id) {
            throw new Exception("Notify requests must not have ID set");
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
                throw new Exception("Missing id in response");
            }
        }

        // check for extra ids in response
        if (count($response) > 0) {
            throw new Exception("Extra id(s) in response");
        }

        return $ordered_response;
    }

    // send raw json to the server
    public function send($json, $notify = false)
    {
        // prepare data to be sent
        $opts = array(
            'http' => array(
                'method'  => 'POST',
                'header'  => 'Content-Type: application/json\r\n',
                'content' => $json));
        $context = stream_context_create($opts);

        // try to physically send data to destination 
        try {
            $response = file_get_contents($this->uri, false, $context);
        } catch (\Exception $e) {
            throw new Exception("Unable to connect to {$this->uri}");
        }

        // handle communication errors
        if ($response === false) {
            throw new Exception("Unable to connect to {$this->uri}");
        }

        // notify has no response
        if ($notify) {
            return true;
        }

        // try to decode json
        $response = json_decode($response);
        if ($response === null) {
            throw new Exception("Unable to decode JSON response");
        }

        // handle response, create response object and return it
        return $this->handleResponse($response);
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
        if ($response->error) {
            return new Response(null, $response->id, $response->error->code, $response->error->message);
        }

        // return successful response
        return new Response($response->result, $response->id);
    }

}

?>