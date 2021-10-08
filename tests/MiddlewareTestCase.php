<?php

namespace Loguzz\Test;

use Loguzz\Middleware\LogMiddleware;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;

class ObjectFactory
{
    /** @var LoggerInterface */
    public $logger;
    /** @var \GuzzleHttp\Client */
    public $client;
    /** @var \Psr\Http\Message\RequestInterface */
    public $request;
}

class MiddlewareTestCase extends LoguzzTestCase
{
    protected function getLogger(): LoggerInterface
    {
        return new TestLogger();
    }

    protected function getLoggerMiddleware(LoggerInterface $logger, array $options = []): LogMiddleware
    {
        return new LogMiddleware($logger, $options);
    }

    protected function getLoggerMiddlewareWithOptions(
        ?LoggerInterface $logger = null,
        $options = []
    ): LogMiddleware {
        return $this->getLoggerMiddleware($logger ?? $this->getLogger(), $options);
    }

    protected function objectFactory(
        $middlewareOptions = [],
        $response = [],
        $requestOptions = [],
        $clientOptions = []
    ): ObjectFactory {

        $dto = new ObjectFactory();

        $dto->logger = $this->getLogger();

        $dto->client = $this->getClient(
            $response ?: $this->createResponse(),
            $clientOptions,
            $this->getLoggerMiddlewareWithOptions($dto->logger, $middlewareOptions)
        );

        $dto->request = $this->createRequest(...$requestOptions);

        return $dto;
    }
}
