<?php

namespace MyVendor\Plugin\FulfillFlow\Waybill;

use SalesRender\Plugin\Components\Form\Form;
use SalesRender\Plugin\Components\Form\FormData;
use SalesRender\Plugin\Components\Settings\Settings;
use SalesRender\Plugin\Core\Logistic\Components\Waybill\WaybillHandlerInterface;
use SalesRender\Plugin\Core\Logistic\Components\Waybill\Response\WaybillResponse;
use SalesRender\Plugin\Components\Logistic\Waybill\Waybill;
use SalesRender\Plugin\Components\Logistic\Waybill\Track;
use SalesRender\Plugin\Components\Logistic\LogisticStatus;
use SalesRender\Plugin\Components\Logistic\Logistic;
use MyVendor\Plugin\FulfillFlow\Client;

class WaybillHandler implements WaybillHandlerInterface
{
    public function __invoke(Form $form, FormData $data): WaybillResponse
    {
        $settings = Settings::find()->getData();

        $client = new Client(
            (string) $settings->get('main.apiKey'),
            (string) $settings->get('main.apiBaseUrl')
        );

        // Call FulfillFlow to actually create the shipment.
        $result = $client->createOrder([
            'recipient_name'    => (string) $data->get('main.recipientName', ''),
            'recipient_phone'   => (string) $data->get('main.recipientPhone', ''),
            'address'           => (string) $data->get('main.address', ''),
            'product_sku'       => (string) $data->get('main.productSku'),
            'warehouse_name'    => (string) $data->get('main.warehouseName'),
            'country_name'      => (string) $data->get('main.countryName', ''),
            'city_name'         => (string) $data->get('main.cityName', ''),
            'reference_id'      => (string) $data->get('main.referenceId', ''),
            'payment_terms'     => 'cod',
            'quantity'          => (int) $data->get('main.quantity', 1),
            'order_amount'      => (float) $data->get('main.orderAmount', 0),
            'shipping_charges'  => (float) $data->get('main.shippingCharges', 0),
            'payment_method'    => 'cod',
        ]);

        // FulfillFlow's order id becomes our tracking number, if it fits
        // Track's allowed pattern (6-36 chars, A-Z 0-9 - _). If not, we
        // fall back to no track for now and log it for follow-up.
        $rawId = (string) ($result['id'] ?? $result['order_id'] ?? '');
        $track = null;
        if ($rawId !== '' && preg_match('/^[A-Za-z0-9\-_]{6,36}$/', $rawId)) {
            $track = new Track($rawId);
        }

        $shippingCharges = isset($result['shipping_charges'])
            ? (float) $result['shipping_charges']
            : null;

        $waybill = new Waybill(
            $track,
            $shippingCharges,
            null, // delivery terms - unknown from FulfillFlow at creation time
            null, // delivery type - unknown from FulfillFlow at creation time
            true  // cash on delivery, matches our 'cod' payment_method above
        );

        $logistic = new Logistic(
            $waybill,
            new LogisticStatus(LogisticStatus::CREATED, 'Order created with FulfillFlow')
        );

        return new WaybillResponse($logistic, null);
    }
}