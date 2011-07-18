<?php
// require the client.php file in the base directory
require_once("/path/to/junior/client.php");

// create a new instance of Junior\Client\Client with the URI of te 
$client = new Junior\Client\Client("http://your.json-rpc.url");

// you can use the magic method shortcut to make requests...
$response = $client->foo();
echo $response->result; // --> "bar"

// ...and it supports positional arguments
$response = $client->sum(1, 2, 3);
echo $response->result;  // --> 6

// for named parameters you need to make a request object and send it with the client
$request = new Junior\Client\Request('makeFullName', array('last_name' => 'Fry', 'first_name' => 'Philip J.'));
$response = $client->sendRequest($request);
echo $response->result; // --> "Philip J. Fry"

// notifications should be specified when you create a request object
$request = new Junior\Client\Request('notify', 10, true);
$response = $client->sendNotify($request);
echo $response->result; // --> true (on success)

// batches are sent as an array of requests, and are processed and returned in order (with no notifications)
$requests = array();
$requests[] = new Junior\Client\Request('makeFullName', array('last_name' => 'Fry', 'first_name' => 'Philip'));
$requests[] = new Junior\Client\Request('notify', 10, true);
$requests[] = new Junior\Client\Request('isEven', 11);
$response = $client->sendBatch($requests);
print_r($response->results); // array( "Philip J. Fry", false)

?>
