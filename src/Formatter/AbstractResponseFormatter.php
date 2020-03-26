<?php

namespace Loguz\Formatter;

use Psr\Http\Message\ResponseInterface;

abstract class AbstractResponseFormatter
{
    protected $options = [];

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array                               $options
     */
    protected function extractArguments (ResponseInterface $response, array $options) {
        $this->extractProtocol($response);
        $this->extractReasonPhrase($response);
        $this->extractStatusCode($response);
        $this->extractHeaders($response);
        $this->extractBodySize($response);
        $this->extractBody($response);
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    private function extractBodySize (ResponseInterface $response) {
        $this->options['size'] = $response->getBody()->getSize();
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    private function extractBody (ResponseInterface $response) {
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

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    private function extractReasonPhrase (ResponseInterface $response) {
        $this->options['reason_phrase'] = $response->getReasonPhrase();
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    private function extractStatusCode (ResponseInterface $response) {
        $this->options['status_code'] = $response->getStatusCode();
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    private function extractProtocol (ResponseInterface $response) {
        $this->options['protocol'] = $response->getProtocolVersion();
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    private function extractHeaders (ResponseInterface $response) {
        $this->options['headers'] = $response->getHeaders();
    }

    /**
     * @param ResponseInterface $response
     * @param array             $options
     *
     * @return string | array
     */
    abstract public function format (ResponseInterface $response, array $options = []);
}