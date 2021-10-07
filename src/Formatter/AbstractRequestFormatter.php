<?php

namespace Loguzz\Formatter;

use GuzzleHttp\Cookie\CookieJarInterface;
use Psr\Http\Message\RequestInterface;

abstract class AbstractRequestFormatter
{
    protected function parseData(RequestInterface $request, array $options): array
    {
        return [
            'method' => $this->getHttpMethod($request),
            'data' => $this->getRequestBody($request),
            'cookies' => $this->getCookie($request, $options),
            'headers' => $this->getRequestHeaders($request),
            'user-agent' => $this->getUserAgent($request),
            'url' => $this->getUrl($request),
        ];
    }

    final protected function getHttpMethod(RequestInterface $request): string
    {
        //if get request has data Add G otherwise curl will make a post request
        /*if (!empty($this->getRequestBody($request)) && 'GET' === ($method = $request->getMethod())) {
            return 'GET';
        }*/

        return $request->getMethod();
    }

    final protected function getRequestBody(RequestInterface $request): string
    {
        // Cloned so that accidentally the request body is not changed
        $body = (clone $request)->getBody();

        if ($body->isSeekable()) {
            $body->rewind();
        }

        $contents = $body->getContents();

        if ($contents) {
            // clean input of null bytes
            $contents = str_replace(chr(0), '', $contents);
        }

        return $contents;
    }

    final protected function getCookie(RequestInterface $request, array $options): array
    {
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
        }, ($options['cookies'] ?? false) instanceof CookieJarInterface ? $options['cookies']->toArray() : []);
    }

    final protected function getRequestHeaders(RequestInterface $request): array
    {
        $headers = [];
        foreach ($request->getHeaders() as $name => $header) {
            if ('host' === strtolower($name) && $header[0] === $request->getUri()->getHost()) {
                continue;
            }

            if (in_array(strtolower($name), ['cookie', 'user-agent'])) {
                continue;
            }

            foreach ((array) $header as $headerValue) {
                $headers[$name][] = $headerValue;
            }
        }

        return $headers;
    }

    final protected function getUrl(RequestInterface $request): string
    {
        return (string) $request->getUri()->withFragment('');
    }

    final protected function getUserAgent(RequestInterface $request): string
    {
        return $request->getHeader('user-agent')[0] ?? '';
    }

    abstract public function format(RequestInterface $request, array $options = []);
}
