<?php

namespace DigitalMarketingFramework\Collector\Pardot\Query\Authentication;

class SsoPardotToken extends PardotToken
{
    protected string $accessToken;
    protected string $businessUnitId;

    public function __construct(string $accessToken, string $businessUnitId)
    {
        $this->accessToken = $accessToken;
        $this->businessUnitId = $businessUnitId;
    }

    /**
     * @param array<string,string> $headers
     */
    public function addHeaders(array &$headers): void
    {
        parent::addHeaders($headers);
        $headers['Authorization'] = 'Bearer ' . $this->accessToken;
        $headers['Pardot-Business-Unit-Id'] = $this->businessUnitId;
    }
}
