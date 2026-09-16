<?php

namespace MyVendor\Plugin\FulfillFlow\Actions;

use SalesRender\Plugin\Core\Logistic\Components\Actions\Shipping\ShippingCancelAction;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

class CancelAction extends ShippingCancelAction
{
    protected function handle(array $body, ServerRequest $request, Response $response, array $args): Response
    {
        // TODO: call FulfillFlow's cancel-order endpoint here (check the
        // Endpoints tab in the Vendor Portal for the exact route/method -
        // it wasn't in the Orders/Shipments/Leads/Catalog sections we've
        // seen so far, may be under Shipments or a dedicated Cancel action).
        return $response->withStatus(200);
    }
}
