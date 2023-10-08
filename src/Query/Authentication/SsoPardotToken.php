<?php

namespace DigitalMarketingFramework\Collector\Pardot\Query\Authentication;

class SsoPardotToken extends PardotToken
{
    public function __construct(
        protected string $accessToken,
        protected string $businessUnitId
    ) {
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
