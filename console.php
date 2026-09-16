#!/usr/bin/env php
<?php

use SalesRender\Plugin\Core\Logistic\Factories\ConsoleAppFactory;

require __DIR__ . '/vendor/autoload.php';

$factory = new ConsoleAppFactory();
$application = $factory->build();
$application->run();
