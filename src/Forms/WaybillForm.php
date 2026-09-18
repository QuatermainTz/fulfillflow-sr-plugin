<?php

namespace MyVendor\Plugin\FulfillFlow\Forms;

use SalesRender\Plugin\Components\Form\FieldDefinitions\StringDefinition;
use SalesRender\Plugin\Components\Form\FieldDefinitions\IntegerDefinition;
use SalesRender\Plugin\Components\Form\FieldGroup;
use SalesRender\Plugin\Components\Form\Form;

class WaybillForm extends Form
{
    public function __construct()
    {
        $nonEmpty = function ($value) {
            return empty($value) ? ['Field cannot be empty'] : [];
        };

        parent::__construct(
            'Create FulfillFlow shipment',
            'Confirm the details to send to FulfillFlow',
            [
                'main' => new FieldGroup(
                    'Shipment details',
                    null,
                    [
                        'productSku' => new StringDefinition('Product SKU', 'FulfillFlow product_sku', $nonEmpty),
                        'warehouseName' => new StringDefinition('Warehouse', 'FulfillFlow warehouse_name', $nonEmpty),
                        'quantity' => new IntegerDefinition('Quantity', 'Number of units', $nonEmpty),
                        'shippingCharges' => new NumberDefinition('Shipping charges', 'Delivery fee', function () { return []; }),
                    ]
                ),
            ],
            'Create shipment'
        );
    }
}
