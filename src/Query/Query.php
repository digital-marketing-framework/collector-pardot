<?php

namespace DigitalMarketingFramework\Collector\Pardot\Query;

use DigitalMarketingFramework\Collector\Pardot\Exception\PardotConnectorException;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

abstract class Query
{
    /**
     * @param array<string,string> $cookies
     *
     * @return array<string,string>
     */
    protected function buildCookies(array $cookies): array
    {
        return $cookies;
    }

    /**
     * @param array<string,string> $cookies
     */
    protected function buildCookieJar(array $cookies, string $uri): CookieJar
    {
        $cookies = $this->buildCookies($cookies);
        $requestCookies = [];
        if ($cookies !== []) {
            $host = parse_url($uri, PHP_URL_HOST);
            foreach ($cookies as $cKey => $cValue) {
                // Set up a cookie - name, value AND domain.
                $cookie = new SetCookie();
                $cookie->setName($cKey);
                $cookie->setValue(rawurlencode($cValue));
                if ($host !== false) {
                    $cookie->setDomain($host);
                }

                $requestCookies[] = $cookie;
            }
        }

        return new CookieJar(false, $requestCookies);
    }

    /**
     * @param array<string,string> $data
     */
    protected function parameterize(array $data): string
    {
        $params = [];
        foreach ($data as $key => $value) {
            $params[] = rawurlencode($key) . '=' . rawurlencode($value);
        }

        return implode('&', $params);
    }

    /**
     * @param array<string,string> $data
     */
    protected function buildBody(array $data): string
    {
        $data = $this->buildBodyData($data);

        return $this->parameterize($data);
    }

    /**
     * @param array<string,string> $parameters
     */
    protected function buildUri(string $host, string $path = '', array $parameters = []): string
    {
        $parameters = $this->buildUrlParameters($parameters);

        return $host . $path . ($parameters === [] ? '' : ('?' . $this->parameterize($parameters)));
    }

    /**
     * @param array<string,string> $data
     *
     * @return array<string,string>
     */
    protected function buildBodyData(array $data): array
    {
        return $data;
    }

    /**
     * @param array<string,string> $headers
     *
     * @return array<string,string>
     */
    protected function buildHeaders(array $headers): array
    {
        return $headers;
    }

    /**
     * @param array<string,string> $parameters
     *
     * @return array<string,string>
     */
    protected function buildUrlParameters(array $parameters): array
    {
        return $parameters;
    }

    protected function checkStatus(ResponseInterface $response): bool
    {
        $status_code = $response->getStatusCode();

        return $status_code < 500;
    }

    /**
     * @return array<mixed>|false
     */
    abstract protected function computeResponse(ResponseInterface $response): array|false;

    /**
     * @param array<string,string> $parameters
     * @param array<string,string> $data
     * @param array<string,string> $headers
     * @param array<string,string> $cookies
     *
     * @return array<mixed>|false
     */
    protected function send(
        string $host,
        string $path,
        array $parameters = [],
        array $data = [],
        string $method = 'GET',
        array $headers = [],
        array $cookies = []
    ): array|false {
        $uri = $this->buildUri($host, $path, $parameters);
        $requestOptions = [
            'body' => $this->buildBody($data),
            'cookies' => $this->buildCookieJar($cookies, $uri),
            'headers' => $this->buildHeaders($headers),
            'http_errors' => false,
        ];

        try {
            $client = new Client();
            $response = $client->request($method, $uri, $requestOptions);
            if (!$this->checkStatus($response)) {
                return false;
            }

            return $this->computeResponse($response);
        } catch (GuzzleException $e) {
            throw new PardotConnectorException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
