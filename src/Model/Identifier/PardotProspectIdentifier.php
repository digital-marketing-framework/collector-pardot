<?php

namespace DigitalMarketingFramework\Collector\Pardot\Model\Identifier;

use DigitalMarketingFramework\Core\Model\Identifier\Identifier as Identifier;

class PardotProspectIdentifier extends Identifier
{
    public function __construct(string $prospectId)
    {
        return parent::__construct(['id' => $prospectId]);
    }

    protected function getInternalCacheKey(): string
    {
        return $this->getProspectId();
    }

    public function getProspectId(): string
    {
        return $this->payload['id'];
    }
}
