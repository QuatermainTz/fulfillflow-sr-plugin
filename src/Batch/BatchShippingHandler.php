<?php

namespace MyVendor\Plugin\FulfillFlow\Batch;

use SalesRender\Plugin\Components\Batch\Batch;
use SalesRender\Plugin\Components\Batch\BatchHandlerInterface;
use SalesRender\Plugin\Components\Batch\Process\Error;
use SalesRender\Plugin\Components\Batch\Process\Process;
use SalesRender\Plugin\Components\ApiClient\ApiClient;
use SalesRender\Plugin\Components\ApiClient\ApiFetcherIterator;
use SalesRender\Plugin\Components\Access\Token\GraphqlInputToken;
use SalesRender\Plugin\Components\Settings\Settings;
use XAKEPEHOK\ArrayGraphQL\ArrayGraphQL;
use MyVendor\Plugin\FulfillFlow\Client;

class BatchShippingHandler implements BatchHandlerInterface
{
    public function __invoke(Process $process, Batch $batch)
    {
        $settings = Settings::find()->getData();
        $fulfillFlowClient = new Client(
            (string) $settings->get('main.apiKey'),
            (string) $settings->get('main.apiBaseUrl')
        );

        $orderFields = [
            'orders' => [
                'id',
                'status' => ['id'],
                'customer' => [
                    'name' => ['fullName'],
                    'phones' => ['raw'],
                ],
                'shipping' => [
                    'address' => [
                        'country',
                        'city',
                        'line1',
                    ],
                ],
            ],
        ];

        $ordersIterator = new OrdersFetcherIterator(
            $orderFields,
            $batch->getApiClient(),
            $batch->getFsp()
        );

        $process->initialize(count($ordersIterator));

        foreach ($ordersIterator as $orderId => $order) {
            try {
                $customerName = $order['customer']['name']['fullName'] ?? '';
                $customerPhone = $order['customer']['phones'][0]['raw'] ?? '';
                $address = $order['shipping']['address'] ?? [];
                $addressLine = trim(implode(', ', array_filter([
                    $address['line1'] ?? '',
                    $address['city'] ?? '',
                    $address['country'] ?? '',
                ])));

                $result = $fulfillFlowClient->createOrder([
                    'recipient_name'    => $customerName,
                    'recipient_phone'   => $customerPhone,
                    'address'           => $addressLine,
                    'product_sku'       => 'SKU-001',
                    'warehouse_name'    => 'Fulfillflow',
                    'country_name'      => $address['country'] ?? '',
                    'city_name'         => $address['city'] ?? '',
                    'reference_id'      => (string) $orderId,
                    'payment_terms'     => 'cod',
                    'quantity'          => 1,
                    'order_amount'      => 0,
                    'shipping_charges'  => 0,
                    'payment_method'    => 'cod',
                ]);

                $process->handle();
            } catch (\Exception $exception) {
                $process->addError(new Error(
                    $exception->getMessage(),
                    (string) $orderId
                ));
            }
            $process->save();
        }

        $process->finish(true);
        $process->save();
    }
}

class OrdersFetcherIterator extends ApiFetcherIterator
{
    protected function getQuery(array $fields): string
    {
        return '
            query($pagination: Pagination!, $filters: OrderSearchFilter, $sort: OrderSort) {
                ordersFetcher(pagination: $pagination, filters: $filters, sort: $sort) '
            . ArrayGraphQL::convert($fields) .
            '}
        ';
    }

    protected function getQueryPath(): string
    {
        return 'ordersFetcher';
    }

    protected function getIdentity(array $array): string
    {
        return $array['id'];
    }
}