<?php

namespace DigitalMarketingFramework\Collector\Pardot\Query\Authentication;

interface PardotAuthenticatorInterface
{
    public function __construct(string $environment);

    /**
     * @param array<mixed> $credentials
     */
    public function getAccessToken(array $credentials): ?PardotTokenInterface;
}
