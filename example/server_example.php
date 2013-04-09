<?php
// require Junior
require_once("../src/autoload.php");

// write or include your own class to expose to communication
class MyClass {

  public function foo()
  {
      return "bar";
  }

  public function isEven($number)
  {
      if ($number % 2 == 0) {
          return true;
      }
      return false;
  }

  public function sum($a, $b, $c)
  {
      return $a + $b + $c;
  }

  // named parameters are accepted in a single associative array
  public function makeFullName(array $named_params)
  {
      return $named_params['first_name'] . ' ' . $named_params['last_name'];
  }

  // notifications don't return anything
  public function notify($number)
  {
      return;
  }

}

// create a new instance of Junior\Server with an instance of your class
$server = new Junior\Server(new MyClass());

// call process()
$server->process();