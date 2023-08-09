<?php

namespace DigitalMarketingFramework\Collector\Pardot;

use DigitalMarketingFramework\Collector\Core\DataCollector\DataCollectorInterface;
use DigitalMarketingFramework\Collector\Pardot\DataCollector\PardotDataCollector;
use DigitalMarketingFramework\Collector\Pardot\IdentifierCollector\PardotIdentifierCollector;
use DigitalMarketingFramework\Core\IdentifierCollector\IdentifierCollectorInterface;
use DigitalMarketingFramework\Core\Initialization;
use DigitalMarketingFramework\Core\Registry\RegistryDomain;

class CollectorPardotInitialization extends Initialization
{
    protected const PLUGINS = [
        RegistryDomain::CORE => [
            IdentifierCollectorInterface::class => [
                PardotIdentifierCollector::class,
            ],
        ],
        RegistryDomain::COLLECTOR => [
            DataCollectorInterface::class => [
                PardotDataCollector::class,
            ],
        ],
    ];

    public function __construct()
    {
        parent::__construct('collector-pardot', '1.0.0');
    }
}
