<?php

namespace DigitalMarketingFramework\Collector\Pardot\IdentifierCollector;

use DigitalMarketingFramework\Collector\Pardot\Model\Identifier\PardotVisitorIdentifier;
use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\Context\WriteableContextInterface;
use DigitalMarketingFramework\Core\IdentifierCollector\IdentifierCollector;
use DigitalMarketingFramework\Core\Model\Identifier\IdentifierInterface;

class PardotIdentifierCollector extends IdentifierCollector
{
    protected const REGEXP_COOKIE_VISITOR_ID = '/^visitor_id(\d+)$/';

    protected const REGEXP_COOKIE_VISITOR_HASH = '/^visitor_id(\d+)-hash$/';

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
        $idFound = false;
        $payload = [];
        foreach ($context->getCookies() as $name => $value) {
            if (preg_match(static::REGEXP_COOKIE_VISITOR_ID, $name, $matches)) {
                $payload[$matches[1]]['id'] = $value;
                $idFound = true;
            } elseif (preg_match(static::REGEXP_COOKIE_VISITOR_HASH, $name, $matches)) {
                $payload[$matches[1]]['hash'] = $value;
            }
        }

        if ($idFound) {
            return new PardotVisitorIdentifier($payload);
        }

        return null;
    }
}
