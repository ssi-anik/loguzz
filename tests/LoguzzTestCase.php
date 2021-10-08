<?php

namespace Loguzz\Test;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use Loguzz\Middleware\LogMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;

class LoguzzTestCase extends TestCase
{
    public const USER_AGENT = 'anik/loguzz guzzle-log-middleware';
    public const BASE_URI = 'https://example.local';
    protected $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = $this->getLogger();
    }

    protected function getClient($responses = [], array $options = [], ?LogMiddleware $logMiddleware = null): Client
    {
        $responses = is_array($responses) ? $responses : [$responses];
        if ($responses) {
            $handler = new MockHandler($responses);
        } else {
            $handler = HandlerStack::create();
        }

        if ($logMiddleware) {
            $handler->push($logMiddleware, 'logger');
        }

        $options = $options + ['base_uri' => self::BASE_URI, 'handler' => $handler,];

        return new Client($options);
    }

    protected function getLogger(): TestLogger
    {
        return new TestLogger();
    }

    protected function getLoggerMiddleware(array $options = []): LogMiddleware
    {
        if (!$this->logger) {
            throw new Exception('logger variable must be initialized before calling getLoggerMiddleware().');
        }

        return new LogMiddleware($this->logger, $options);
    }

    protected function createRequest(
        $method = 'GET',
        $url = '/',
        $body = '',
        array $headers = []
    ): Request {
        $headers = $headers + ['user-agent' => self::USER_AGENT,];

        return new Request($method, $url, $headers, $body);
    }
}
