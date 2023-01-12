<?php

namespace DigitalMarketingFramework\Collector\Pardot\Query\QueryObject;

class Visitor extends PardotQueryObject
{
    protected function getObjectName(): string
    {
        return 'visitor';
    }
}
