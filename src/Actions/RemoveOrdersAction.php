<?php

namespace MyVendor\Plugin\FulfillFlow\Actions;

use SalesRender\Plugin\Core\Logistic\Components\Actions\Shipping\RemoveOrdersAction as BaseRemoveOrdersAction;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

class RemoveOrdersAction extends BaseRemoveOrdersAction
{
    protected function handle(array $body, ServerRequest $request, Response $response, array $args): Response
    {
        // TODO: call FulfillFlow to remove/void the order if they support it.
        return $response->withStatus(200);
    }
}
