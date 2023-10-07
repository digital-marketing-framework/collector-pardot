<?php

namespace DigitalMarketingFramework\Collector\Pardot\Query\QueryObject;

use DigitalMarketingFramework\Collector\Pardot\Connector\PardotConnectorInterface;
use DigitalMarketingFramework\Collector\Pardot\Exception\PardotConnectorException;
use DigitalMarketingFramework\Collector\Pardot\Query\Authentication\PardotTokenInterface;
use DigitalMarketingFramework\Collector\Pardot\Query\Query;
use Psr\Http\Message\ResponseInterface;

abstract class PardotQueryObject extends Query
{
    public const HOSTS = [
        PardotConnectorInterface::ENVIRONMENT_PRODUCTION => 'https://pi.pardot.com',
        PardotConnectorInterface::ENVIRONMENT_SANDBOX => 'https://pi.demo.pardot.com',
        PardotConnectorInterface::ENVIRONMENT_DEVELOP_ORG => 'https://pi.demo.pardot.com',
    ];

    protected string $outputMode = 'simple';

    protected string $format = 'json';

    public function __construct(
        protected PardotTokenInterface $token,
        protected string $environment = PardotConnectorInterface::ENVIRONMENT_PRODUCTION,
        protected string $version = '4'
    ) {
    }

    public function outputMode(string $outputMode): PardotQueryObject
    {
        $this->outputMode = $outputMode;

        return $this;
    }

    public function format(string $format): PardotQueryObject
    {
        $this->format = $format;

        return $this;
    }

    /**
     * @param array<string,string> $headers
     *
     * @return array<string,string>
     */
    protected function buildHeaders(array $headers): array
    {
        $headers = parent::buildHeaders($headers);
        $this->token->addHeaders($headers);

        return $headers;
    }

    /**
     * @param array<string,string> $parameters
     *
     * @return array<string,string>
     */
    protected function buildUrlParameters(array $parameters): array
    {
        $parameters = parent::buildUrlParameters($parameters);
        $this->token->addUrlParameters($parameters);

        return $parameters;
    }

    /**
     * @param array<string,string> $cookies
     *
     * @return array<string,string>
     */
    protected function buildCookies(array $cookies): array
    {
        $cookies = parent::buildCookies($cookies);
        $this->token->addCookies($cookies);

        return $cookies;
    }

    /**
     * @param array<string,string> $data
     *
     * @return array<string,string>
     */
    protected function buildBodyData(array $data): array
    {
        $data = parent::buildBodyData($data);
        $this->token->addBodyData($data);

        return $data;
    }

    abstract protected function getObjectName(): string;

    protected function getHost(): string
    {
        return static::HOSTS[$this->environment];
    }

    /**
     * @param array<string,string> $pathParams
     * @param array<string,string> $params
     *
     * @return array<mixed>|bool
     */
    public function doAction(string $action, array $pathParams = [], array $params = []): array|bool
    {
        $path = '/api/' . $this->getObjectName() . '/version/' . $this->version . '/do/' . $action;
        foreach ($pathParams as $name => $value) {
            $path .= '/' . $name . '/' . $value;
        }

        if (!isset($params['format'])) {
            $params['format'] = $this->format;
        }

        if (!isset($params['output'])) {
            $params['output'] = $this->outputMode;
        }

        return $this->send($this->getHost(), $path, $params);
    }

    /**
     * @param ResponseInterface $response
     *
     * @return array<mixed>|bool
     */
    protected function computeResponse(ResponseInterface $response): array|bool
    {
        $result = $response->getBody()->getContents();
        if ($result) {
            if ($this->format === 'json') {
                $result = json_decode((string)$result, true, 512, JSON_THROW_ON_ERROR);
            }

            if (isset($result['err'])) {
                throw new PardotConnectorException($result['err'], $result['@attributes']['err_code'] ?? -1);
            }

            return $result;
        }

        return false;
    }

    /**
     * @param array<string,string> $query
     *
     * @return array<mixed>|bool
     */
    public function read(array $query): array|bool
    {
        $result = $this->doAction('read', $query);
        $objectName = $this->getObjectName();
        if ($result && is_array($result) && isset($result[$objectName])) {
            return $result[$objectName];
        }

        return $result;
    }
}
