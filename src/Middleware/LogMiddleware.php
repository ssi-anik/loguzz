<?php

namespace Loguz\Middleware;

use Loguz\Formatter\AbstractRequestFormatter;
use Loguz\Formatter\CurlCommandRequestFormatter;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

class LogMiddleware
{
    protected $logger, $options;

    public function __construct (LoggerInterface $logger, array $options = []) {
        $this->logger = $logger;
        $this->options = $options;
    }

    private function logRequest () : bool {
        return isset($this->options['log_request']) ? (bool) $this->options['log_request'] : true;
    }

    private function getDefaultRequestFormatter () {
        $length = isset($this->options['length']) ? $this->options['length'] : 100;

        return new CurlCommandRequestFormatter($length);
    }

    private function getRequestFormatter () : AbstractRequestFormatter {
        $formatter = null;
        if (isset($this->options['log_formatter'])) {
            $formatter = $this->options['log_formatter'];
        }

        return $formatter instanceof AbstractRequestFormatter ? $formatter : $this->getDefaultRequestFormatter();
    }

    private function getLogLevel () {
        return isset($this->options['log_level']) ? $this->options['log_level'] : 'info';
    }

    public function __invoke (callable $handler) {
        return function (RequestInterface $request, array $options) use ($handler) {
            if ($this->logRequest()) {
                $output = $this->getRequestFormatter()->format($request, $options);
                $this->logger->{$this->getLogLevel()}($output);
            }

            return $handler($request, $options);
        };
    }
}