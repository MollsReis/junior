<?php
spl_autoload_register(function($className) {
    if (strpos($className, "Junior\\") === 0) {
        $fileName = str_replace("\\", '/', $className) . ".php";
        require_once __DIR__ . '/' . $fileName;
    }
});
require_once __DIR__ . '/../vendor/autoload.php';