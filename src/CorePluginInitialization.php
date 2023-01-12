<?php

namespace DigitalMarketingFramework\Collector\Pardot;

use DigitalMarketingFramework\Collector\Pardot\IdentifierCollector\PardotIdentifierCollector;
use DigitalMarketingFramework\Core\IdentifierCollector\IdentifierCollectorInterface;
use DigitalMarketingFramework\Core\PluginInitialization;

class CorePluginInitialization extends PluginInitialization
{
    const PLUGINS = [
        IdentifierCollectorInterface::class => [
            PardotIdentifierCollector::class,
        ],
    ];
}