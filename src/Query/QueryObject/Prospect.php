<?php

namespace DigitalMarketingFramework\Collector\Pardot\Query\QueryObject;

class Prospect extends PardotQueryObject
{
    protected function getObjectName(): string
    {
        return 'prospect';
    }
}
