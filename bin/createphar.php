<?php
$phar = new Phar(__DIR__ . '/../junior.phar');
$phar->buildFromDirectory(__DIR__ . '/../lib');

$stub = implode("\n", array_slice(file(__DIR__ . '/../lib/autoload.php'), 1));
$stub = str_replace("__DIR__", "'phar://' . __FILE__ ", $stub);
$stub = implode(
    "\n",
    array(
        '<?php',
        'Phar::mapPhar();',
        $stub,
        "__HALT_COMPILER();"
    )
);
$phar->setStub($stub);
