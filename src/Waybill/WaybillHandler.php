<?php

namespace MyVendor\Plugin\FulfillFlow\Waybill;

use SalesRender\Plugin\Components\Form\Form;
use SalesRender\Plugin\Components\Form\FormData;
use SalesRender\Plugin\Components\Settings\Settings;
use SalesRender\Plugin\Core\Logistic\Components\Waybill\WaybillHandlerInterface;
use SalesRender\Plugin\Core\Logistic\Components\Waybill\Response\WaybillResponse;
use SalesRender\Plugin\Components\Logistic\Waybill;
use SalesRender\Plugin\Components\Logistic\LogisticStatus;
use SalesRender\Plugin\Components\Logistic\Logistic;
use MyVendor\Plugin\FulfillFlow\Client;

class WaybillHandler implements WaybillHandlerInterface
{
    public function __invoke(Form $form, FormData $data): WaybillResponse
    {
        $settings = Settings::find()->getData();

        $client = new Client(
            $settings->get('main.apiKey'),
            $settings->get('main.apiBaseUrl')
        );

        // NOTE: field names below (recipient_name, recipient_phone, etc.)
        // match FulfillFlow's documented "Create Order" example. Map real
        // order data (customer name/phone/address) in here - currently
        // pulling only what the waybill form collects plus placeholders.
        $result = $client->createOrder([
            'recipient_name'    => $data->get('main.recipientName', ''),
            'recipient_phone'   => $data->get('main.recipientPhone', ''),
            'address'           => $data->get('main.address', ''),
            'product_sku'       => $data->get('main.productSku'),
            'warehouse_name'    => $data->get('main.warehouseName'),
            'country_name'      => $data->get('main.countryName', ''),
            'city_name'         => $data->get('main.cityName', ''),
            'reference_id'      => $data->get('main.referenceId', ''),
            'payment_terms'     => 'cod',
            'quantity'          => $data->get('main.quantity'),
            'order_amount'      => $data->get('main.orderAmount', 0),
            'shipping_charges'  => $data->get('main.shippingCharges', 0),
            'payment_method'    => 'cod',
        ]);

        // TODO: verify Waybill's exact constructor against
        // salesrender/plugin-component-logistic source - this is a
        // best-effort mapping based on the documented Logistic/Waybill
        // model (track number + carrier + optional metadata).
        $waybill = new Waybill(
            (string) ($result['id'] ?? $result['order_id'] ?? ''),  // FulfillFlow's order/tracking id
            'FulfillFlow'
        );

        $status = new LogisticStatus(LogisticStatus::CREATED ?? 'CREATED');

        $logistic = new Logistic($waybill, $status, $result);

        return new WaybillResponse($logistic, null);
    }
}
