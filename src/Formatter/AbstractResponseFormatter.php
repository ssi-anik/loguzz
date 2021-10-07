<?php

namespace Loguzz\Formatter;

use GuzzleHttp\Cookie\CookieJar;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractResponseFormatter
{
    protected function parseData(RequestInterface $request, ResponseInterface $response, array $options): array
    {
        return [
            'protocol' => $this->getProtocol($response),
            'reason_phrase' => $this->getReasonPhrase($response),
            'status_code' => $this->getStatusCode($response),
            'cookies' => $this->getCookies($request, $response),
            'headers' => $this->getResponseHeaders($response),
            'size' => $this->getResponseBodySize($response),
            'body' => $this->getResponseBody($response),
        ];
    }

    final protected function getProtocol(ResponseInterface $response): string
    {
        return $response->getProtocolVersion();
    }

    final protected function getReasonPhrase(ResponseInterface $response): string
    {
        return $response->getReasonPhrase();
    }

    final protected function getStatusCode(ResponseInterface $response): int
    {
        return $response->getStatusCode();
    }

    final protected function getCookies(RequestInterface $request, ResponseInterface $response): array
    {
        $cookieJar = new CookieJar();
        $cookieJar->extractCookies(clone $request, clone $response);

        return array_map(function ($cookie) {
            return [
                'name' => $cookie['Name'] ?? null,
                'value' => $cookie['Value'] ?? null,
                'domain' => $cookie['Domain'] ?? null,
                'path' => $cookie['Path'] ?? '/',
                'max-age' => $cookie['Max-age'] ?? null,
                'expires' => $cookie['Expires'] ?? null,
                'secure' => $cookie['Secure'] ?? false,
                'discard' => $cookie['Discard'] ?? false,
                'httponly' => $cookie['Httponly'] ?? false,
            ];
        }, $cookieJar->toArray());
    }

    final protected function getResponseHeaders(ResponseInterface $response): array
    {
        return $response->getHeaders();
    }

    final protected function getResponseBodySize(ResponseInterface $response): ?int
    {
        return $response->getBody()->getSize();
    }

    final protected function getResponseBody(ResponseInterface $response): string
    {
        // response is cloned to avoid any accidental data damage
        $body = (clone $response)->getBody();
        if (!$body->isReadable()) {
            return '';
        }

        if ($body->isSeekable()) {
            $body->rewind();
        }

        return $body->getContents();
    }

    abstract public function format(RequestInterface $request, ResponseInterface $response, array $options = []);
}
