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
            throw new \Exception("Mismatched request id");
        }

        return $response;
    }

    // send a single notify request object
    public function sendNotify($req)
    {
        if ($req->id) {
            throw new \Exception("Notify requests must not have ID set");
        }

        $this->send($req->getJSON(), true);
        return true;
    }

    // send an array of request objects as a batch
    public function sendBatch($reqs)
    {
        $arr = array();
        $all_notify = true;
        foreach ($reqs as $req) {
            if ($req->id) {
                $all_notify = false;
            }
            $arr[] = $req->getArray();
        }
        $response = $this->send(json_encode($arr), $all_notify);

        if ($all_notify) {
            return true;
        }

        if (count(array_udiff($reqs, $response, array('self', '_checkId'))) > 0) {
            throw new \Exception("Mismatched request id(s)");
        }

        return $response;
    }

    // helper function to check sent vs. received ids in a batch
    public static function _checkId($a, $b)
    {
        if ($a->id === $b->id) {
            return 1;
        }
        return 0;
    }

    // send raw json to the server
    public function send($json, $notify = false)
    {
        $opts = array(
            'http' => array(
                'method'  => 'POST',
                'header'  => 'Content-Type: application/json\r\n',
                'content' => $json));
        $context = stream_context_create($opts);
        $response = file_get_contents($this->uri, false, $context);

        if ($response === false) {
            throw new \Exception("Unable to connect to {$this->uri}");
        }

        if ($notify) {
            return true;
        }

        $response = json_decode($response);
        if ($response === null) {
            throw new \Exception("Unable to decode JSON response");
        }

        return $this->handleResponse($response);
    }

    // handle the response and return a result or an error
    private function handleResponse($response)
    {
        if (is_array($response)) {
            $response_arr = array();
            foreach ($response as $res) {
                $response_arr[] = $this->handleResponse($res);
            }
            return $response_arr;
        }

        if ($response->error) {
            return new Response(null, $response->id, $response->error->code, $response->error->message);
        }

        return new Response($response->result, $response->id);
    }

}

?>
