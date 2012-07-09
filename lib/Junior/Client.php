<?php
namespace Junior;
use Junior\Clientside\Request,
    Junior\Clientside\Response,
    Junior\Clientside\Exception;

foreach(array('Request', 'Response', 'Exception') as $file) {
    require_once('Junior'. DIRECTORY_SEPARATOR . 'Clientside' . DIRECTORY_SEPARATOR . $file . '.php');
}

class Client {

    public $uri;
    private $curlAuthType = null;
    private $curlUsername = "";
    private $curlPassword = "";
    
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
    
    /**
     * Enforces HTTP Digest Authentication to be used when querying the JSON-RPC
     * service.
     * 
     * @param string $username
     * @param string $password
     */
     public function useHttpDigestAuthentication($username, $password) {
        $this->curlUsername = $username;
        $this->curlPassword = $password;
        $this->curlAuthType = 'digest';
     }
    
    /**
     * Enforces HTTP Basic Authentication to be used when querying the JSON-RPC
     * service.
     * 
     * @param string $username
     * @param string $password
     */
     public function useHttpBasicAuthentication($username, $password) {
         $this->curlUsername = $username;
         $this->curlPassword = $password;
         $this->curlAuthType = 'basic';
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
        $response = false;
        
        // try to physically send data to destination
         
        $ch = curl_init($this->uri);
        // Return result as string instead of printing it
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // HTTP Post
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        // Follow any "Location: " header
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        // Keep sending the username and password when following locations
        curl_setopt($ch, CURLOPT_UNRESTRICTED_AUTH, true);
        // Apply the curl authentication options
        switch ($this->curlAuthType) {
            case 'digest':
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
                curl_setopt($ch, CURLOPT_USERPWD, "{$this->curlUsername}:{$this->curlPassword}");
                break;
            case 'basic':
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, "{$this->curlUsername}:{$this->curlPassword}");
                break;
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        $response = curl_exec($ch);
        curl_close($ch);

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
