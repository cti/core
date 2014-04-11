<?php

$loader = include __DIR__.'/vendor/autoload.php';
$loader->add("Bootstrap", __DIR__.'/tests/src/php/Bootstrap');
$loader->add("Command", __DIR__.'/tests/src/php/Command');
$loader->add("Controller", __DIR__.'/tests/src/php/Controller');
