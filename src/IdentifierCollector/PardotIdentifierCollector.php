<?php

namespace DigitalMarketingFramework\Collector\Pardot\IdentifierCollector;

use DigitalMarketingFramework\Collector\Pardot\Model\Identifier\PardotVisitorIdentifier;
use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\Context\WriteableContextInterface;
use DigitalMarketingFramework\Core\IdentifierCollector\IdentifierCollector;
use DigitalMarketingFramework\Core\Model\Identifier\IdentifierInterface;

class PardotIdentifierCollector extends IdentifierCollector
{
    protected const REGEXP_COOKIE_VISITOR_ID = '/^visitor_id[0-9]+$/';
    protected const REGEXP_COOKIE_VISITOR_HASH = '/^visitor_id[0-9]+-hash$/';

    protected function prepareContext(ContextInterface $source, WriteableContextInterface $target): void
    {
        foreach ($source->getCookies() as $name => $value) {
            if (
                preg_match(static::REGEXP_COOKIE_VISITOR_ID, $name)
                || preg_match(static::REGEXP_COOKIE_VISITOR_HASH, $name)
            ) {
                $target->setCookie($name, $value);
            }
        }
    }

    protected function collect(ContextInterface $context): ?IdentifierInterface
    {
        $payload = [];
        foreach ($context->getCookies() as $name => $value) {
            if (preg_match(static::REGEXP_COOKIE_VISITOR_ID, $name)) {
                $payload[$name]['id'] = $value;
            } elseif (preg_match(static::REGEXP_COOKIE_VISITOR_HASH, $name)) {
                $payload[$name]['hash'] = $value;
            }
        }
        if (!empty($payload)) {
            return new PardotVisitorIdentifier($payload);
        }
        return null;
    }
}
