<?php

namespace Loguzz\Formatter;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractResponseFormatter
{
    protected $options = [];

    public function __construct()
    {
        $this->initializeOptions();
    }

    final protected function initializeOptions(array $options = [])
    {
        $this->options = empty($options) ? [] : $options;
    }

    protected function extractArguments(RequestInterface $request, ResponseInterface $response, array $options): void
    {
        $this->initializeOptions();
        $this->extractProtocol($response);
        $this->extractReasonPhrase($response);
        $this->extractStatusCode($response);
        $this->extractHeaders($response);
        $this->extractBodySize($response);
        $this->extractBody($response);
    }

    final protected function extractBodySize(ResponseInterface $response): void
    {
        $this->options['size'] = $response->getBody()->getSize();
    }

    final protected function extractBody(ResponseInterface $response): void
    {
        $body = $response->getBody();
        if (!$body->isReadable()) {
            $this->options['body'] = '';

            return;
        }

        if ($body->isSeekable()) {
            $previousPosition = $body->tell();
            $body->rewind();
        }

        $contents = $body->getContents();

        if ($body->isSeekable()) {
            $body->seek($previousPosition);
        }

        if ($contents) {
            $this->options['body'] = $contents;
        }
    }

    final protected function extractReasonPhrase(ResponseInterface $response): void
    {
        $this->options['reason_phrase'] = $response->getReasonPhrase();
    }

    final protected function extractStatusCode(ResponseInterface $response): void
    {
        $this->options['status_code'] = $response->getStatusCode();
    }

    final protected function extractProtocol(ResponseInterface $response): void
    {
        $this->options['protocol'] = $response->getProtocolVersion();
    }

    final protected function extractHeaders(ResponseInterface $response): void
    {
        $this->options['headers'] = $response->getHeaders();
    }

    abstract public function format(RequestInterface $request, ResponseInterface $response, array $options = []);
}
