<?php
// require Junior
require_once("../src/autoload.php");

/**
 * write or include your own class to expose to communication
 *
 * Class MyClass
 */
class MyClass
{
    /**
     * @return string
     */
    public function foo()
    {
        return "bar";
    }

    /**
     * @param $number
     * @return bool
     */
    public function isEven($number)
    {
        if ($number % 2 == 0) {
            return true;
        }

        return false;
    }

    /**
     * @param $a
     * @param $b
     * @param $c
     * @return mixed
     */
    public function sum($a, $b, $c)
    {
        return $a + $b + $c;
    }

    /**
     * named parameters are accepted in a single associative array
     *
     * @param array $named_params
     * @return string
     */
    public function makeFullName(array $named_params)
    {
        return $named_params['first_name'] . ' ' . $named_params['last_name'];
    }

    /**
     * notifications don't return anything
     *
     * @param $number
     */
    public function notify($number)
    {
        return;
    }

}

// create a new instance of Junior\Server with an instance of your class
$server = new Junior\Server(new MyClass());

// call process()
$server->process();