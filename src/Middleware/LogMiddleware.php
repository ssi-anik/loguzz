<?php

namespace Loguzz\Middleware;

use Closure;
use Exception;
use GuzzleHttp\Promise\Create;
use Loguzz\Formatter\AbstractExceptionFormatter;
use Loguzz\Formatter\AbstractRequestFormatter;
use Loguzz\Formatter\AbstractResponseFormatter;
use Loguzz\Formatter\ExceptionJsonFormatter;
use Loguzz\Formatter\RequestCurlFormatter;
use Loguzz\Formatter\ResponseJsonFormatter;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class LogMiddleware
{
    protected LoggerInterface $logger;
    protected array $options;

    public function __construct(LoggerInterface $logger, array $options = [])
    {
        $this->logger = $logger;
        $this->options = $options;
    }

    public function __invoke(callable $handler): Closure
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            if ($this->logRequest()) {
                $output = $this->formatWithTag($this->getRequestFormatter()->format($request, $options), 'request');
                $this->logger->{$this->getLogLevel()}($output);
            }

            if ($this->logResponse()) {
                return $handler($request, $options)->then(
                    $this->handleSuccess($request, $options),
                    $this->handleFailure($request, $options)
                );
            }

            return $handler($request, $options);
        };
    }

    private function logRequest(): bool
    {
        return (bool)($this->options['log_request'] ?? true);
    }

    private function formatWithTag($loggable, $type)
    {
        if ($tag = $this->getLogTag()) {
            if ($this->shouldSeparate()) {
                $tag = $tag . '.' . $type;
            }

            return $this->forceToJson() ? json_encode([$tag => $loggable]) : [$tag => $loggable];
        }

        return $loggable;
    }

    private function getLogTag(): string
    {
        return $this->options['tag'] ?? '';
    }

    private function shouldSeparate(): bool
    {
        return (bool)($this->options['separate'] ?? false);
    }

    private function forceToJson(): bool
    {
        return (bool)($this->options['force_json'] ?? true);
    }

    private function getRequestFormatter(): AbstractRequestFormatter
    {
        $formatter = $this->options['request_formatter'] ?? null;

        return $formatter instanceof AbstractRequestFormatter ? $formatter : $this->getDefaultRequestFormatter();
    }

    private function getDefaultRequestFormatter(): AbstractRequestFormatter
    {
        $length = (int)($this->options['length'] ?? 100);
        $length = $length < 10 ? 100 : $length;

        return new RequestCurlFormatter($length);
    }

    private function getLogLevel(): string
    {
        return $this->options['log_level'] ?? 'debug';
    }

    private function logResponse(): bool
    {
        return (bool)($this->options['log_response'] ?? true);
    }

    /**
     * Returns a function which is handled when a request was successful.
     *
     * @param RequestInterface $request
     * @param array $options
     *
     * @return callable
     */
    private function handleSuccess(RequestInterface $request, array $options): callable
    {
        return function (ResponseInterface $response) use ($request, $options) {
            if (!$this->logExceptionOnly()) {
                $output = $this->formatWithTag(
                    $this->getResponseFormatter()->format($request, $response, $options),
                    'success'
                );
                $this->logger->{$this->getLogLevel()}($output);
            }

            return $response;
        };
    }

    private function logExceptionOnly(): bool
    {
        return (bool)($this->options['exceptions_only'] ?? false);
    }

    private function getResponseFormatter(): AbstractResponseFormatter
    {
        $formatter = $this->options['response_formatter'] ?? null;

        return $formatter instanceof AbstractResponseFormatter ? $formatter : $this->getDefaultResponseFormatter();
    }

    private function getDefaultResponseFormatter(): ResponseJsonFormatter
    {
        return new ResponseJsonFormatter();
    }

    /**
     * Returns a function which is handled when a request was rejected.
     *
     * @param RequestInterface $request
     * @param array $options
     *
     * @return callable
     */
    private function handleFailure(RequestInterface $request, array $options): callable
    {
        return function (Exception $reason) use ($request, $options) {
            if (!$this->logSuccessOnly()) {
                $output = $this->formatWithTag(
                    $this->getExceptionFormatter()->format($request, $reason, $options),
                    'failure'
                );
                $this->logger->{$this->getLogLevel()}($output);
            }

            return Create::rejectionFor($reason);
        };
    }

    private function logSuccessOnly(): bool
    {
        return (bool)($this->options['success_only'] ?? false);
    }

    private function getExceptionFormatter(): AbstractExceptionFormatter
    {
        $formatter = $this->options['exception_formatter'] ?? null;

        return $formatter instanceof AbstractExceptionFormatter ? $formatter : $this->getDefaultExceptionFormatter();
    }

    private function getDefaultExceptionFormatter(): ExceptionJsonFormatter
    {
        return new ExceptionJsonFormatter();
    }
}
