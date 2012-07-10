<?php
namespace Junior;
use Junior\Clientside\Exception,
    Junior\Client;

require_once('Junior'. DIRECTORY_SEPARATOR . 'Client.php');

/**
 * This class basically does the same thing as the \c Junior\Client class except
 * that it can handle HTTP Basic Authentication.
 *
 * @FIXME Remove dependency on curl to support PHP installation that don't have it.
 * @TODO Add tests for this class
 *
 * @author Konrad Kleine (kwk)
 */
class ClientBasicAuth extends Client {

    private $username = "";
    private $password = "";
    
    /**
     * Sets the \a $username and \a $password to be used for the HTTP Basic
     * authentication and passes the \a $uri to the parent constructor.
     */
    public function __construct($uri, $username, $password)
    {
        parent::__construct($uri);
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @override
     */
    public function send($json, $notify = false)
    {
        $response = false;
        
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
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->curlUsername}:{$this->curlPassword}");
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
}
