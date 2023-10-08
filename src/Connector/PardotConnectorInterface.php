<?php

namespace DigitalMarketingFramework\Collector\Pardot\Connector;

use DigitalMarketingFramework\Collector\Pardot\Query\QueryObject\Prospect;
use DigitalMarketingFramework\Collector\Pardot\Query\QueryObject\Visitor;

interface PardotConnectorInterface
{
    public const ENVIRONMENT_PRODUCTION = 'production';

    public const ENVIRONMENT_SANDBOX = 'sandbox';

    public const ENVIRONMENT_DEVELOP_ORG = 'develop_org';

    public function environment(string $environment): PardotConnectorInterface;

    public function version(string $version): PardotConnectorInterface;

    /**
     * @param array<string,mixed> $credentials
     */
    public function authenticate(array $credentials): bool;

    public function isAuthenticated(): bool;

    public function visitor(): Visitor;

    public function prospect(): Prospect;
}
