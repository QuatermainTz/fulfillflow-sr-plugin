<?php

namespace MyVendor\Plugin\FulfillFlow\Forms;

use SalesRender\Plugin\Components\Form\FieldDefinitions\StringDefinition;
use SalesRender\Plugin\Components\Form\FieldGroup;
use SalesRender\Plugin\Components\Form\Form;

class SettingsForm extends Form
{
    public function __construct()
    {
        $nonEmpty = function ($value) {
            $errors = [];
            if (empty($value)) {
                $errors[] = 'Field cannot be empty';
            }
            return $errors;
        };

        parent::__construct(
            'FulfillFlow settings',
            'Enter your FulfillFlow vendor portal API key',
            [
                'main' => new FieldGroup(
                    'Main settings',
                    null,
                    [
                        'apiKey' => new StringDefinition(
                            'FulfillFlow API key',
                            'Found in FulfillFlow Vendor Portal -> API -> Your API Key',
                            $nonEmpty
                        ),
                        'apiBaseUrl' => new StringDefinition(
                            'FulfillFlow API base URL',
                            'e.g. https://<your-instance>.supabase.co/functions/v1/api-gateway/api/v1',
                            $nonEmpty
                        ),
                    ]
                ),
            ],
            'Save'
        );
    }
}
