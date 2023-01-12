<?php

namespace DigitalMarketingFramework\Collector\Pardot\Query\Authentication;

interface PardotTokenInterface
{
    /**
     * @param array<string,string> $headers
     */
    public function addHeaders(array &$headers): void;

    /**
     * @param array<string,string> $cookies
     */
    public function addCookies(array &$cookies): void;

    /**
     * @param array<string,string> $parameters
     */
    public function addUrlParameters(array &$parameters): void;

    /**
     * @param array<string,string> $data
     */
    public function addBodyData(array &$data): void;
}
