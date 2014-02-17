<?php
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);
define('ENV', 'TEST');
require_once __DIR__ . '/../src/autoload.php';

require_once __DIR__ . '/fixture/FixtureClass.php';
require_once __DIR__ . '/fixture/ExposedClass.php';
require_once __DIR__ . '/fixture/StubAdapter.php';