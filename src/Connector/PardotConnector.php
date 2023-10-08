<?php

namespace DigitalMarketingFramework\Collector\Pardot\Connector;

use DigitalMarketingFramework\Collector\Pardot\Exception\PardotConnectorException;
use DigitalMarketingFramework\Collector\Pardot\Query\Authentication\PardotAuthenticatorInterface;
use DigitalMarketingFramework\Collector\Pardot\Query\Authentication\PardotTokenInterface;
use DigitalMarketingFramework\Collector\Pardot\Query\Authentication\SsoAuthenticatior;
use DigitalMarketingFramework\Collector\Pardot\Query\QueryObject\Prospect;
use DigitalMarketingFramework\Collector\Pardot\Query\QueryObject\Visitor;

class PardotConnector implements PardotConnectorInterface
{
    protected PardotTokenInterface $token;

    public function __construct(
        protected string $environment = PardotConnectorInterface::ENVIRONMENT_PRODUCTION,
        protected string $version = '3',
    ) {
    }

    protected function getAuthenticator(): PardotAuthenticatorInterface
    {
        return new SsoAuthenticatior($this->environment);
    }

    public function environment(string $environment): PardotConnectorInterface
    {
        $this->environment = $environment;

        return $this;
    }

    public function version(string $version): PardotConnectorInterface
    {
        $this->version = $version;

        return $this;
    }

    public function authenticate(array $credentials): bool
    {
        $authenticator = $this->getAuthenticator();
        try {
            $token = $authenticator->getAccessToken($credentials);
            if ($token instanceof PardotTokenInterface) {
                $this->token = $token;

                return true;
            }

            return false;
        } catch (PardotConnectorException) {
            return false;
        }
    }

    public function isAuthenticated(): bool
    {
        return isset($this->token);
    }

    public function visitor(): Visitor
    {
        return new Visitor($this->token, $this->environment, $this->version);
    }

    public function prospect(): Prospect
    {
        return new Prospect($this->token, $this->environment, $this->version);
    }
}
