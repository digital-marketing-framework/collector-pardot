<?php

namespace DigitalMarketingFramework\Collector\Pardot;

use DigitalMarketingFramework\Collector\Core\DataCollector\DataCollectorInterface;
use DigitalMarketingFramework\Collector\Pardot\DataCollector\PardotDataCollector;
use DigitalMarketingFramework\Core\PluginInitialization;

class CollectorPluginInitialization extends PluginInitialization
{
    const PLUGINS = [
        DataCollectorInterface::class => [
            PardotDataCollector::class,
        ],
    ];
}
