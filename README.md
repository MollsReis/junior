# Junior - A JSON-RPC Client/Server in PHP [![Build Status](https://secure.travis-ci.org/EvilScott/junior.png)](http://travis-ci.org/EvilScott/junior)

## What is JSON-RPC?
From the [JSON-RPC Spec](http://www.jsonrpc.org/spec.html):

"JSON-RPC is a stateless, light-weight remote procedure call (RPC) protocol. Primarily this specification defines several data structures and the rules around their processing. 
It is transport agnostic in that the concepts can be used within the same process, over sockets, over http, or in many various message passing environments. It uses JSON (RFC 4627) 
as data format.
It is designed to be simple!"

## Why should I use Junior?
Junior is JSON-RPC 2.0 viable and follows the JSON-RPC spec as of March 2012. It supports batching, named parameters, and notifications.

## Is Junior available in a PHAR format?
Junior can be downloaded as a PHAR file from the [downloads section](https://github.com/EvilScott/junior/downloads), or you can make your own PHAR file by running createphar.php from the bin directory.

## How do I use the client?
Include lib/autoload.php load all necessary files. Alternatively, just include junior.phar if taking advantage of the PHAR format. Create a new instance of Junior\Client() and pass it the URI of the server you wish to communicate with. All communication through this instance. See the example folder for more details.

## How do I use the server?
Include lib/autoload.php load all necessary files. Alternatively, just include junior.phar if taking advantage of the PHAR format.  Create a new instance of Junior\Server() and pass it an instance of the class you wish to expose for communication. Then call the process() function on your server instance and you are ready to go! See the example folder for more details.

## What if I don't like PHP (or I need to talk to Ruby)?
Try out [Jimson](https://github.com/chriskite/jimson), written by Chris Kite for Ruby. It was made at the same time as Junior, and supports all the same features.

## What do I need to run Junior?
* PHP >= 5.3

## Does it have tests?
Yes and as of February 2012 Junior has a robust, working test suite! The tests were written using [Spray](https://github.com/jimbojsb/spray) to stub stream wrappers and PHPUnit version 3.6.10. UPDATE: Junior is hooked into [Travis CI](http://travis-ci.org/EvilScott/junior) as of March 2012.
