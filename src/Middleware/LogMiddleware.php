<?php

namespace Loguz\Middleware;

use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Loguz\Formatter\AbstractRequestFormatter;
use Loguz\Formatter\AbstractResponseFormatter;
use Loguz\Formatter\CurlCommandRequestFormatter;
use Loguz\Formatter\ResponseJsonFormatter;
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

        return new CurlCommandRequestFormatter($length);
    }

    private function getRequestFormatter () : AbstractRequestFormatter {
        $formatter = null;
        if (isset($this->options['log_formatter'])) {
            $formatter = $this->options['log_formatter'];
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
        if (isset($this->options['log_formatter'])) {
            $formatter = $this->options['log_formatter'];
        }

        return $formatter instanceof AbstractResponseFormatter ? $formatter : $this->getDefaultResponseFormatter();
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
                $this->logger->{$this->getLogLevel()}($this->getResponseFormatter()->format($response));
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
//                var_dump($reason instanceof RequestException);
                /*$this->logger->{$this->getLogLevel()}($this->getResponseFormatter()->format($reason));*/
            }

            return rejection_for($reason);
        };
    }
}