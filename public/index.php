<?php

use SalesRender\Plugin\Core\Logistic\Factories\WebAppFactory;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../bootstrap.php';

$factory = new WebAppFactory();
$application = $factory->build();

$application->run();