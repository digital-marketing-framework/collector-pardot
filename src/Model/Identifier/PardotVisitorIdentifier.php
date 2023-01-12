<?php

namespace DigitalMarketingFramework\Collector\Pardot\Model\Identifier;

use DigitalMarketingFramework\Core\Model\Identifier\Identifier;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;

class PardotVisitorIdentifier extends Identifier
{
    public const MESSAGE_NO_VALID_IDENTIFIER = 'No valid Pardot identifier found for cache key';

    protected function getInternalCacheKey(): string
    {
        return $this->getCampaignId() . ':' . $this->getVisitorId();
    }

    public function getCampaignId(): string
    {
        if (empty($this->payload)) {
            throw new DigitalMarketingFrameworkException(static::MESSAGE_NO_VALID_IDENTIFIER);
        }
        return substr(reset(array_keys($this->payload)), 10);
    }

    public function getVisitorId(): string
    {
        if (empty($this->payload)) {
            throw new DigitalMarketingFrameworkException(static::MESSAGE_NO_VALID_IDENTIFIER);
        }
        return reset($this->payload)['id'];
    }
}
