<?php

namespace MyVendor\Plugin\FulfillFlow\Forms;

use SalesRender\Plugin\Components\Form\FieldDefinitions\StringDefinition;
use SalesRender\Plugin\Components\Form\FieldDefinitions\IntegerDefinition;
use SalesRender\Plugin\Components\Form\FieldGroup;
use SalesRender\Plugin\Components\Form\Form;

class WaybillForm extends Form
{
    public function __construct(array $context = [])
    {
        $nonEmpty = function ($value) {
            return empty($value) ? ['Field cannot be empty'] : [];
        };

        $optional = function ($value) {
            return [];
        };

        $orderData = $context['data'] ?? [];

        $recipientName = '';
        foreach ($orderData['humanNameFields'] ?? [] as $entry) {
            $first = $entry['value']['firstName'] ?? '';
            $last  = $entry['value']['lastName'] ?? '';
            $recipientName = trim($first . ' ' . $last);
            if ($recipientName !== '') break;
        }

        $addressLine = '';
        $cityName = '';
        foreach ($orderData['addressFields'] ?? [] as $entry) {
            $value = $entry['value'] ?? [];
            $cityName = $value['city'] ?? $value['region'] ?? '';
            $parts = array_filter([$value['address_1'] ?? null, $value['apartment'] ?? null]);
            $addressLine = implode(', ', $parts) ?: ($value['region'] ?? '');
            if ($addressLine !== '' || $cityName !== '') break;
        }

        parent::__construct(
            'Create FulfillFlow shipment',
            'Confirm the details to send to FulfillFlow',
            [
                'main' => new FieldGroup(
                    'Shipment details',
                    null,
                    [
                        'recipientName'   => new StringDefinition('Recipient name', 'Customer full name', $nonEmpty, $recipientName ?: null),
                        'recipientPhone'  => new StringDefinition('Recipient phone', 'Customer phone number', $nonEmpty),
                        'address'         => new StringDefinition('Address', 'Delivery address', $nonEmpty, $addressLine ?: null),
                        'cityName'        => new StringDefinition('City', 'Delivery city', $nonEmpty, $cityName ?: null),
                        'countryName'     => new StringDefinition('Country', 'Delivery country', $nonEmpty, 'Tanzania'),
                        'productSku'      => new StringDefinition('Product SKU', 'FulfillFlow product_sku', $nonEmpty),
                        'warehouseName'   => new StringDefinition('Warehouse', 'FulfillFlow warehouse_name', $nonEmpty),
                        'quantity'        => new IntegerDefinition('Quantity', 'Number of units', $nonEmpty),
                        'orderAmount'     => new IntegerDefinition('Order amount', 'Total order value (TZS)', $nonEmpty),
                        'shippingCharges' => new IntegerDefinition('Shipping charges', 'Delivery fee', $optional),
                        'referenceId'     => new StringDefinition('Reference ID', 'Your internal order reference', $optional),
                    ]
                ),
            ],
            'Create shipment'
        );
    }
}