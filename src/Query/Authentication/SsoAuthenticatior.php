<?php

namespace DigitalMarketingFramework\Collector\Pardot\Query\Authentication;

use DigitalMarketingFramework\Collector\Pardot\Connector\PardotConnectorInterface;
use DigitalMarketingFramework\Collector\Pardot\Query\Query;
use Psr\Http\Message\ResponseInterface;

class SsoAuthenticatior extends Query implements PardotAuthenticatorInterface
{
    public const HOSTS = [
        PardotConnectorInterface::ENVIRONMENT_PRODUCTION => 'https://login.salesforce.com',
        PardotConnectorInterface::ENVIRONMENT_SANDBOX => 'https://test.salesforce.com',
        PardotConnectorInterface::ENVIRONMENT_DEVELOP_ORG => 'https://login.salesforce.com',
    ];

    protected string $environment = PardotConnectorInterface::ENVIRONMENT_PRODUCTION;

    public function __construct(string $environment)
    {
        $this->environment = $environment;
    }

    protected function getHost(): string
    {
        return static::HOSTS[$this->environment] ?? '';
    }

    /**
     * @param ResponseInterface $response
     * @return array<mixed>|bool
     */
    protected function computeResponse(ResponseInterface $response): array|bool
    {
        $result = $response->getBody()->getContents();
        if ($result) {
            return json_decode($result, true);
        }
        return false;
    }

    public function getAccessToken(array $credentials): ?PardotTokenInterface
    {
        $host = $this->getHost();
        $path = '/services/oauth2/token';

        $parameters = [];

        $password = $credentials['password'];
        if ($credentials['securityToken'] ?? false) {
            $password .= $credentials['securityToken'];
        }

        $data = [
            'grant_type' => 'password',
            'client_id' => $credentials['clientId'],
            'client_secret' => $credentials['clientSecret'],
            'username' => $credentials['username'],
            'password' => $password,
        ];

        $headers = [
            'Content-type' => 'application/x-www-form-urlencoded',
        ];

        if ($host) {
            $response = $this->send(
                $host,
                $path,
                $parameters,
                $data,
                'POST',
                $headers
            );

            if (
                $response
                && !isset($response['error'])
                && isset($response['access_token'])
                && $response['access_token']
            ) {
                return new SsoPardotToken($response['access_token'], $credentials['businessUnitId']);
            }
        }

        return null;
    }
}
