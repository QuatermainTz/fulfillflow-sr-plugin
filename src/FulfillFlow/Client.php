<?php

namespace MyVendor\Plugin\FulfillFlow;

use GuzzleHttp\Client as GuzzleClient;

class Client
{
    private GuzzleClient $http;
    private string $baseUrl;

    public function __construct(string $apiKey, string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->http = new GuzzleClient([
            'base_uri' => $this->baseUrl . '/',
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
            'timeout' => 15,
        ]);
    }

    /**
     * Create an order/shipment with FulfillFlow.
     * @param array $payload see FulfillFlow docs: recipient_name, recipient_phone,
     *   address, product_sku, warehouse_name, country_name, city_name,
     *   reference_id, payment_terms, quantity, order_amount, shipping_charges,
     *   payment_method
     * @return array decoded JSON response (expected to include FulfillFlow's order id)
     */
    public function createOrder(array $payload): array
    {
        $response = $this->http->post('orders', ['json' => $payload]);
        return json_decode((string) $response->getBody(), true) ?? [];
    }

    /**
     * Get a single order's current status from FulfillFlow.
     */
    public function getOrder(string $fulfillflowOrderId): array
    {
        $response = $this->http->get("orders/{$fulfillflowOrderId}");
        return json_decode((string) $response->getBody(), true) ?? [];
    }

    /**
     * List orders - can be used for polling status if FulfillFlow has no webhooks.
     */
    public function listOrders(array $query = []): array
    {
        $response = $this->http->get('orders', ['query' => $query]);
        return json_decode((string) $response->getBody(), true) ?? [];
    }
}
