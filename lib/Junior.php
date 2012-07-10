<?php
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);
require_once('Junior' . DIRECTORY_SEPARATOR . 'Client.php');
require_once('Junior' . DIRECTORY_SEPARATOR . 'Server.php');
if (function_exists('curl_init')) {
    require_once('Junior' . DIRECTORY_SEPARATOR . 'ClientBasicAuth.php');
    require_once('Junior' . DIRECTORY_SEPARATOR . 'ClientDigestAuth.php');
}
