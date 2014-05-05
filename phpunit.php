<?php

$loader = include __DIR__.'/vendor/autoload.php';
$loader->add("Bootstrap", __DIR__.'/tests/src/php/Bootstrap');
$loader->add("Command", __DIR__.'/tests/src/php/Command');
$loader->add("Controller", __DIR__.'/tests/src/php/Controller');
$loader->add("Build", __DIR__.'/tests/build/php/Build');
