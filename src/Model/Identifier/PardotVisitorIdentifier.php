<?php

namespace DigitalMarketingFramework\Collector\Pardot\Model\Identifier;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Model\Identifier\Identifier;

class PardotVisitorIdentifier extends Identifier
{
    public const MESSAGE_NO_VALID_IDENTIFIER = 'No valid Pardot identifier found for cache key';

    protected function getInternalCacheKey(): string
    {
        return $this->getCampaignId() . '-' . $this->getVisitorId();
    }

    public function getCampaignId(): string
    {
        foreach ($this->payload as $campaignId => $data) {
            if (isset($data['id'])) {
                return $campaignId;
            }
        }

        throw new DigitalMarketingFrameworkException(static::MESSAGE_NO_VALID_IDENTIFIER);
    }

    public function getVisitorId(): string
    {
        return $this->payload[$this->getCampaignId()]['id'];
    }
}
