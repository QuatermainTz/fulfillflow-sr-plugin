<?php

use SalesRender\Plugin\Components\Db\Components\Connector;
use SalesRender\Plugin\Components\Info\Developer;
use SalesRender\Plugin\Components\Info\Info;
use SalesRender\Plugin\Components\Info\PluginType;
use SalesRender\Plugin\Components\Purpose\LogisticPluginClass;
use SalesRender\Plugin\Components\Purpose\PluginEntity;
use SalesRender\Plugin\Components\Settings\Settings;
use SalesRender\Plugin\Components\Translations\Translator;
use SalesRender\Plugin\Core\Logistic\Components\Waybill\WaybillContainer;
use SalesRender\Plugin\Core\Logistic\Components\Actions\Shipping\ShippingContainer;
use SalesRender\Plugin\Components\Batch\BatchContainer;
use Medoo\Medoo;
use MyVendor\Plugin\FulfillFlow\Forms\SettingsForm;
use MyVendor\Plugin\FulfillFlow\Forms\WaybillForm;
use MyVendor\Plugin\FulfillFlow\Waybill\WaybillHandler;
use MyVendor\Plugin\FulfillFlow\Actions\CancelAction;
use MyVendor\Plugin\FulfillFlow\Actions\RemoveOrdersAction;
use MyVendor\Plugin\FulfillFlow\Batch\Batch_1;
use MyVendor\Plugin\FulfillFlow\Batch\BatchShippingHandler;
use XAKEPEHOK\Path\Path;

require_once __DIR__ . '/vendor/autoload.php';

error_log('[FF-DEBUG] Client.php exists on disk: ' . (file_exists(__DIR__ . '/src/FulfillFlow/Client.php') ? 'yes' : 'no'));
error_log('[FF-DEBUG] class_exists check: ' . (class_exists('MyVendor\\Plugin\\FulfillFlow\\Client') ? 'yes' : 'no'));
error_log('[FF-DEBUG] src/FulfillFlow contents: ' . implode(', ', glob(__DIR__ . '/src/FulfillFlow/*') ?: ['(empty or missing)']));

// 1. Database (SQLite file, db/ directory must be writable)
Connector::config(new Medoo([
    'database_type' => 'sqlite',
    'database_file' => Path::root()->down('db/database.db'),
]));

// 2. Default language
Translator::config('en_US');

// 3. Plugin info - SHIPPING (delivery) mode
Info::config(
    new PluginType(PluginType::LOGISTIC),
    fn() => 'FulfillFlow Courier',
    fn() => 'Integration between our CRM and the FulfillFlow courier API',
    [
        'class' => LogisticPluginClass::CLASS_DELIVERY,
        'entity' => PluginEntity::ENTITY_ORDER,
        'currency' => ['TZS'],
        'codename' => 'FULFILLFLOW_COURIER',
    ],
    new Developer(
        'Your Company',
        'you@example.com',
        'example.com'
    )
);

// 4. Settings form - holds the FulfillFlow API key
Settings::setForm(fn() => new SettingsForm());

// 5. Waybill form + handler - called when an order is approved and a
//    shipment needs to be created with FulfillFlow
WaybillContainer::config(
    fn(array $context = []) => new WaybillForm($context),
    new WaybillHandler()
);

// 6. Shipping cancel / remove actions (required for DELIVERY mode)
ShippingContainer::config(
    new CancelAction(),
    new RemoveOrdersAction()
);

// 7. Batch action - "Send to FulfillFlow" from the order list
BatchContainer::config(
    function (int $number) {
        switch ($number) {
            case 1: return new Batch_1();
            default: return null;
        }
    },
    new BatchShippingHandler()
);