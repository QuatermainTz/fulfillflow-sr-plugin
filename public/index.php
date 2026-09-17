<?php

use SalesRender\Plugin\Core\Logistic\Factories\WebAppFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../vendor/autoload.php';

$factory = new WebAppFactory();
$application = $factory->build();

// Debug logging middleware - logs every request's method, path, headers,
// and raw body to stdout so it shows up in Render's logs.
$application->add(function (Request $request, $handler) {
    $body = (string) $request->getBody();
    $request->getBody()->rewind();

    error_log(sprintf(
        "[PLUGIN-DEBUG] %s %s | headers=%s | body=%s",
        $request->getMethod(),
        (string) $request->getUri()->getPath(),
        json_encode($request->getHeaders()),
        $body
    ));

    try {
        $response = $handler->handle($request);
        error_log(sprintf(
            "[PLUGIN-DEBUG] response status=%d body=%s",
            $response->getStatusCode(),
            (string) $response->getBody()
        ));
        $response->getBody()->rewind();
        return $response;
    } catch (\Throwable $e) {
        error_log(sprintf(
            "[PLUGIN-DEBUG] EXCEPTION: %s: %s in %s:%d\n%s",
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        ));
        throw $e;
    }
});

$application->run();