<?php

namespace Loguzz\Formatter;

use GuzzleHttp\Cookie\CookieJarInterface;
use Psr\Http\Message\RequestInterface;

abstract class AbstractRequestFormatter
{
    abstract public function format(RequestInterface $request, array $options = []);

    protected function parseData(RequestInterface $request, array $options): array
    {
        return [
            'method' => $this->getHttpMethod($request),
            'body' => $this->getRequestBody($request),
            'cookies' => $this->getCookie($request, $options),
            'headers' => $this->getRequestHeaders($request),
            'user-agent' => $this->getUserAgent($request),
            'url' => $this->getUrl($request),
        ];
    }

    final protected function getHttpMethod(RequestInterface $request): string
    {
        return $request->getMethod();
    }

    final protected function getRequestBody(RequestInterface $request): string
    {
        // Cloned so that accidentally the request body is not changed
        $body = (clone $request)->getBody();

        if ($body->isSeekable()) {
            $previousPosition = $body->tell();
            $body->rewind();
        }

        $contents = $body->getContents();

        if ($body->isSeekable()) {
            $body->seek($previousPosition);
        }
        
        if ($contents) {
            // clean input of null bytes
            $contents = str_replace(chr(0), '', $contents);
        }

        return $contents;
    }

    final protected function getCookie(RequestInterface $request, array $options): array
    {
        $cookies = ($options['cookies'] ?? false) instanceof CookieJarInterface ? $options['cookies']->toArray() : [];

        return cookie_formatter($cookies);
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

            foreach ((array)$header as $headerValue) {
                $headers[$name][] = $headerValue;
            }
        }

        return $headers;
    }

    final protected function getUrl(RequestInterface $request): string
    {
        return (string)$request->getUri()->withFragment('');
    }

    final protected function getUserAgent(RequestInterface $request): string
    {
        return $request->getHeader('user-agent')[0] ?? '';
    }
}
