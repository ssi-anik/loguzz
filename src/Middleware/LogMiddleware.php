<?php

namespace Loguzz\Middleware;

use Exception;
use Loguzz\Formatter\AbstractExceptionFormatter;
use Loguzz\Formatter\AbstractRequestFormatter;
use Loguzz\Formatter\AbstractResponseFormatter;
use Loguzz\Formatter\ExceptionJsonFormatter;
use Loguzz\Formatter\RequestCurlFormatter;
use Loguzz\Formatter\ResponseJsonFormatter;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use function GuzzleHttp\Promise\rejection_for;

class LogMiddleware
{
    protected $logger, $options;

    public function __construct (LoggerInterface $logger, array $options = []) {
        $this->logger = $logger;
        $this->options = $options;
    }

    private function logExceptionOnly () : bool {
        return isset($this->options['exceptions_only']) ? (bool) $this->options['exceptions_only'] : false;
    }

    private function logSuccessOnly () : bool {
        return isset($this->options['success_only']) ? (bool) $this->options['success_only'] : false;
    }

    private function logRequest () : bool {
        return isset($this->options['log_request']) ? (bool) $this->options['log_request'] : true;
    }

    private function getDefaultRequestFormatter () : AbstractRequestFormatter {
        $length = isset($this->options['length']) ? $this->options['length'] : 100;

        return new RequestCurlFormatter($length);
    }

    private function getRequestFormatter () : AbstractRequestFormatter {
        $formatter = null;
        if (isset($this->options['request_formatter'])) {
            $formatter = $this->options['request_formatter'];
        }

        return $formatter instanceof AbstractRequestFormatter ? $formatter : $this->getDefaultRequestFormatter();
    }

    private function logResponse () : bool {
        return isset($this->options['log_response']) ? (bool) $this->options['log_response'] : true;
    }

    private function getDefaultResponseFormatter () {
        return new ResponseJsonFormatter();
    }

    private function getResponseFormatter () : AbstractResponseFormatter {
        $formatter = null;
        if (isset($this->options['response_formatter'])) {
            $formatter = $this->options['response_formatter'];
        }

        return $formatter instanceof AbstractResponseFormatter ? $formatter : $this->getDefaultResponseFormatter();
    }

    private function getDefaultExceptionFormatter () {
        return new ExceptionJsonFormatter();
    }

    private function getExceptionFormatter () : AbstractExceptionFormatter {
        return $this->getDefaultExceptionFormatter();
    }

    private function getLogLevel () {
        return isset($this->options['log_level']) ? $this->options['log_level'] : 'info';
    }

    private function getLogTag () : string {
        return isset($this->options['tag']) ? $this->options['tag'] : '';
    }

    private function forceToJson () : bool {
        return isset($this->options['force_json']) ? (bool) $this->options['force_json'] : true;
    }

    private function shouldSeparate () : bool {
        return isset($this->options['separate']) ? (bool) $this->options['separate'] : false;
    }

    private function formatWithTag ($loggable, $type) {
        if ($tag = $this->getLogTag()) {
            if ($this->shouldSeparate()) {
                $tag = $tag . '.' . $type;
            }
            return $this->forceToJson() ? json_encode([ $tag => $loggable ]) : [ $tag => $loggable ];
        }

        return $loggable;
    }

    public function __invoke (callable $handler) {
        return function (RequestInterface $request, array $options) use ($handler) {
            if ($this->logRequest()) {
                $output = $this->formatWithTag($this->getRequestFormatter()->format($request, $options), 'request');
                $this->logger->{$this->getLogLevel()}($output);
            }

            if ($this->logResponse()) {
                return $handler($request, $options)->then($this->handleSuccess($request, $options),
                    $this->handleFailure($request, $options));
            }

            return $handler($request, $options);
        };
    }

    /**
     * Returns a function which is handled when a request was successful.
     *
     * @param RequestInterface $request
     * @param array            $options
     *
     * @return callable
     */
    private function handleSuccess (RequestInterface $request, array $options) : callable {
        return function (ResponseInterface $response) use ($request, $options) {
            if (!$this->logExceptionOnly()) {
                $output = $this->formatWithTag($this->getResponseFormatter()->format($response), 'success');
                $this->logger->{$this->getLogLevel()}($output);
            }

            return $response;
        };
    }

    /**
     * Returns a function which is handled when a request was rejected.
     *
     * @param RequestInterface $request
     * @param array            $options
     *
     * @return callable
     */
    private function handleFailure (RequestInterface $request, array $options) : callable {
        return function (Exception $reason) use ($request, $options) {
            if (!$this->logSuccessOnly()) {
                $output = $this->formatWithTag($this->getExceptionFormatter()->format($reason), 'failure');
                $this->logger->{$this->getLogLevel()}($output);
            }

            return rejection_for($reason);
        };
    }
}