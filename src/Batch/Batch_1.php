<?php

namespace MyVendor\Plugin\FulfillFlow\Batch;

use SalesRender\Plugin\Components\Form\Form;
use SalesRender\Plugin\Components\Form\FieldGroup;
use SalesRender\Plugin\Components\Form\FieldDefinitions\StringDefinition;

class Batch_1 extends Form
{
    public function __construct()
    {
        parent::__construct(
            'Send to FulfillFlow',
            'Create FulfillFlow shipments for the selected orders',
            [
                'main' => new FieldGroup(
                    'Options',
                    null,
                    [
                        'note' => new StringDefinition(
                            'Note (optional)',
                            'Optional reference note for this batch',
                            null
                        ),
                    ]
                ),
            ],
            'Send to FulfillFlow'
        );
    }
}