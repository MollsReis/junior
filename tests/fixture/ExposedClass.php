<?php

class ExposedClass {

    public function subtract($a, $b = null)
    {
        if (is_array($a)) {
            return $a['minuend'] - $a['subtrahend'];
        } else {
            return $a - $b;
        }
    }
}