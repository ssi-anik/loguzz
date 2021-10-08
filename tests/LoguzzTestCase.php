<?php

namespace Loguzz\Test;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Loguzz\Middleware\LogMiddleware;
use PHPUnit\Framework\TestCase;

class LoguzzTestCase extends TestCase
{
    public const USER_AGENT = 'anik/loguzz guzzle-log-middleware';
    public const BASE_URI = 'https://example.local';

    protected function getClient($responses = [], array $options = [], ?LogMiddleware $logMiddleware = null): Client
    {
        $responses = is_array($responses) ? $responses : [$responses];
        if ($responses) {
            $handler = MockHandler::createWithMiddleware($responses);
        } else {
            $handler = HandlerStack::create();
        }

        if ($logMiddleware) {
            $handler->push($logMiddleware, 'logger');
        }

        $options = $options + ['base_uri' => self::BASE_URI, 'handler' => $handler,];

        return new Client($options);
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

    protected function createResponse(
        array $headers = [],
        $status = 200,
        $body = null
    ): Response {
        return new Response($status, $headers, $body);
    }
}
