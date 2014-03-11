<?php

class ExposedClass {

    public function sum($a, $b, $c)
    {
        return $a + $b + $c;
    }

    public function subtract($a, $b = null)
    {
        if (is_array($a)) {
            return $a['minuend'] - $a['subtrahend'];
        } else {
            return $a - $b;
        }
    }

    public function getData()
    {
        return [ "hello", 5 ];
    }

    public function update($a, $b, $c, $d, $e)
    {
        // just a notify
    }

    public function notifyFoobar()
    {
        // just a notify
    }

    public function notifySum($a, $b, $c)
    {
        // just a notify
    }

    public function notifyHello($a)
    {
        // just a notify
    }
}